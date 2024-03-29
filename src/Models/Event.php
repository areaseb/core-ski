<?php

namespace Areaseb\Core\Models;

use \Carbon\Carbon;
use \Storage;
use App\User;
use Areaseb\Core\Models\Notification;
use Areaseb\Core\Models\Company;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;


class Event extends Primitive
{
    protected $casts = [
        'starts_at' => 'datetime:Y-m-d H:i:s',
        'ends_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function eventable()
    {
        return $this->morphTo();
    }

    //il creatore dell'evento (proprietario del calendario)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //il calendario associato all'evento
    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    //altri utenti associati all'evento
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    //contatti associati all'evento
    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'event_contact', 'event_id', 'contact_id');
    }

    //aziende associati all'evento
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'event_company', 'event_id', 'company_id');
    }

    public function scopeBetweenDates($query, $dates)
    {
        $start = Carbon::parse($dates['start'])->format('Y-m-d H:i:s');
        $end = Carbon::parse($dates['end'])->format('Y-m-d H:i:s');
        return $query = $query->whereDate('starts_at', '>=', $start)->whereDate('ends_at', '<=', $end);
    }

    /*
     * swap default color to new (max 3 extra calendars)
     * @param  [collection] $events [with original colors]
     * @param  [int] $key    [loop key]
     * @return [collection] [with new colors]
     */
    public static function mutateColors($events, $key)
    {
        $newColor = [
            0 => '#3788d8',
            1 => '#ff1a1a',
            2 => '#009933',
            3 => '#ffa31a'
        ];

        $events = $events->map(function ($event) use($key, $newColor) {
            $event->backgroundColor = $newColor[$key];
            $event->borderColor = $newColor[$key];
            return $event;
        });

        return $events;
    }


    public static function createICS($event, $ics_name)
    {
        foreach (self::all() as $calendar)
        {
            $contents = "BEGIN:VCALENDAR\r
PRODID:-//".config('app.name')."//IT\r
VERSION:2.0\r
CALSCALE:GREGORIAN\r
X-WR-CALNAME:calendario_".$ics_name."\r
X-WR-TIMEZONE:Europe/Rome\r
BEGIN:VTIMEZONE\r
TZID:Europe/Rome\r
X-LIC-LOCATION:Europe/Rome\r
BEGIN:DAYLIGHT\r
TZOFFSETFROM:+0100\r
TZOFFSETTO:+0200\r
TZNAME:CEST\r
DTSTART:19700329T020000\r
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\r
END:DAYLIGHT\r
BEGIN:STANDARD\r
TZOFFSETFROM:+0200\r
TZOFFSETTO:+0100\r
TZNAME:CET\r
DTSTART:19701025T030000\r
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\r
END:STANDARD\r
END:VTIMEZONE\r";

				//$description = (strstr($event->summary, PHP_EOL)) ? '' : $event->summary;

                $singleEvent =
"\r\nBEGIN:VEVENT\r
DTSTART:".$event->starts_at->format('Ymd\THis')."\r
DTEND:".$event->ends_at->format('Ymd\THis')."\r
DTSTAMP:".Carbon::now()->format('Ymd\THis')."\r
UID:".uniqid()."\r
CREATED:".$event->created_at->format('Ymd\THis')."\r
DESCRIPTION:".$event->summary."\r
LAST-MODIFIED:".$event->updated_at->format('Ymd\THis')."\r
LOCATION:".$event->location."\r
SEQUENCE:1\r
STATUS:CONFIRMED\r
SUMMARY:".$event->title."\r
TRANSP:OPAQUE\r
BEGIN:VALARM\r
ACTION:DISPLAY\r
TRIGGER:-PT10M\r
END:VALARM\r
END:VEVENT\r";

			$contents .= $singleEvent;

            $contents .= "\r\nEND:VCALENDAR";
            
            $filename = 'public/calendars/'.$ics_name;

            if (Storage::exists($filename))
            {
                Storage::delete($filename);
            }

            Storage::put($filename, $contents);
        }

    }


    /*
     * attach many to many relation
     * @param  [model] $event
     * @param  [request] $request
     * @return void
     */
    public static function attachModels($event, $request)
    {
        if($request->contact_id)
        {
            $event->contacts()->attach($request->contact_id);
                        
            $settingSMTP = Setting::smtp(0);
            
            config()->set('mail.host', $settingSMTP['MAIL_HOST']);
            config()->set('mail.port', $settingSMTP['MAIL_PORT']);
            config()->set('mail.encryption', $settingSMTP['MAIL_ENCRYPTION']);
            config()->set('mail.username', $settingSMTP['MAIL_USERNAME']);
            config()->set('mail.password', $settingSMTP['MAIL_PASSWORD']);


            $datetime_evento = explode(" ",$event->starts_at);
            $date_evento = explode("-",$datetime_evento[0]);
            $data_evento = $date_evento[2].'/'.$date_evento[1].'/'.$date_evento[0];
            $ora_evento = substr($datetime_evento[1],0,5);


            $contatto_mitt = User::where('id', auth()->user()->id)->with('contact')->get()[0]->contact;
            $mittente = User::where('id', auth()->user()->id)->first()->getFullnameAttribute();
            //->with('contact')->get()[0]->contact;
            $companyName = Company::where('id', $contatto_mitt->company_id)->first()->rag_soc;
            //dd($companyName);
                
            if(isset($request->invioEmail)){
            	
            	$ics_name = str_replace(" ","_",strtolower($mittente)).'_global.ics';

            	self::createICS($event, $ics_name);
            	
                    $destinatario = Contact::where('id', $request->contact_id)->first();
                    $destinatario_fullname = $destinatario->fullname;
                    $data = array(
                        'mittente' => $mittente,
                        'descrizione' => $event->title,
                        "data" => $data_evento,
                        'ora' => $ora_evento,
                        'luogo' => $event->location,
                        'destinatario' => $destinatario_fullname,
                        'email' => $destinatario->email,
                    	'email_from' => $contatto_mitt->email,
                        'azienda' => $companyName,
                        "file" => storage_path('app/public/calendars/'.$ics_name),
                        "name" => $ics_name         
                    );


                    //DEFINISCO IL MAILER IN BASE ALLA CONFIGURAZIONE SMTP SCELTA
                    Mail::send('areaseb::emails.events.new-event',$data, function ($message) use ($data)
                    {
                        $message->to($data['email'])
                            ->subject('Nuovo evento '.config('app.name'))
                            ->from($data['email_from'])
                            ->attach($data['file'], [
                                'as' => $data['name'],
                                'mime' => 'text/calendar',
                            ]);
                    });
            }
            
        }
        
        if(isset($request->user_id))
        {
            $event->users()->attach($request->user_id);

            // vecchia gestione 
            //$dsn = 'smtp://'.Setting::smtp(0)['MAIL_USERNAME'].':'.Setting::smtp(0)['MAIL_PASSWORD'].'@'.Setting::smtp(0)['MAIL_HOST'].':'.Setting::smtp(0)['MAIL_PORT'];
            //Mail::mailer($dsn);

            $settingSMTP = Setting::smtp(0);
            
            config()->set('mail.host', $settingSMTP['MAIL_HOST']);
            config()->set('mail.port', $settingSMTP['MAIL_PORT']);
            config()->set('mail.encryption', $settingSMTP['MAIL_ENCRYPTION']);
            config()->set('mail.username', $settingSMTP['MAIL_USERNAME']);
            config()->set('mail.password', $settingSMTP['MAIL_PASSWORD']);


            $datetime_evento = explode(" ",$event->starts_at);
            $date_evento = explode("-",$datetime_evento[0]);
            $data_evento = $date_evento[2].'/'.$date_evento[1].'/'.$date_evento[0];
            $ora_evento = substr($datetime_evento[1],0,5);


            $contatto_mitt = User::where('id', auth()->user()->id)->with('contact')->get()[0]->contact;
            $mittente = User::where('id', auth()->user()->id)->first()->getFullnameAttribute();
            //->with('contact')->get()[0]->contact;
            $companyName = Company::where('id', $contatto_mitt->company_id)->first()->rag_soc;
            //dd($companyName);

            foreach($request->user_id as $uid)
            {
                $new_event = $event->replicate();
                $new_event->user_id = $uid;
                $new_event->parent_event_id = $event->id;
                $new_event->calendar_id = User::find($uid)->calendars()->first()->id;
                $new_event->backgroundColor = '#FC8516';
                $new_event->save();

                Notification::create([
                    'name' => $event->title,
                    'body' => $event->summary,
                    'notificationable_id' => $event->id,
                    'notificationable_type' => get_class($event),
                    'created_at' => $event->starts_at,
                    'user_id' => $uid
                ]);


                
                if(isset($request->invioEmail)){
                	
                	$ics_name = str_replace(" ","_",strtolower($mittente)).'_global.ics';

                	self::createICS($event, $ics_name);
                	
                        $destinatario = User::where('id', $uid)->first();
                        $destinatario_fullname = $destinatario->getFullnameAttribute();
                        $data = array(
                            'mittente' => $mittente,
                            'descrizione' => $event->title,
                            "data" => $data_evento,
                            'ora' => $ora_evento,
                            'luogo' => $event->location,
                            'destinatario' => $destinatario_fullname,
                            'email' => $destinatario->email,
                        	'email_from' => $contatto_mitt->email,
                            'azienda' => $companyName,
	                        "file" => storage_path('app/public/calendars/'.$ics_name),
	                        "name" => $ics_name         
                        );


                        //DEFINISCO IL MAILER IN BASE ALLA CONFIGURAZIONE SMTP SCELTA
                        Mail::send('areaseb::emails.events.new-event',$data, function ($message) use ($data)
                        {
                            $message->to($data['email'])
                                ->subject('Nuovo evento '.config('app.name'))
                                ->from($data['email_from'])
                                ->attach($data['file'], [
	                                'as' => $data['name'],
	                                'mime' => 'text/calendar',
	                            ]);
                        });
                }
            }            
        }
        
        if(isset($request->emails) && isset($request->invioEmail))
        {
        	$settingSMTP = Setting::smtp(0);
            
            config()->set('mail.host', $settingSMTP['MAIL_HOST']);
            config()->set('mail.port', $settingSMTP['MAIL_PORT']);
            config()->set('mail.encryption', $settingSMTP['MAIL_ENCRYPTION']);
            config()->set('mail.username', $settingSMTP['MAIL_USERNAME']);
            config()->set('mail.password', $settingSMTP['MAIL_PASSWORD']);


            $datetime_evento = explode(" ",$event->starts_at);
            $date_evento = explode("-",$datetime_evento[0]);
            $data_evento = $date_evento[2].'/'.$date_evento[1].'/'.$date_evento[0];
            $ora_evento = substr($datetime_evento[1],0,5);


            $contatto_mitt = User::where('id', auth()->user()->id)->with('contact')->get()[0]->contact;
            $mittente = User::where('id', auth()->user()->id)->first()->getFullnameAttribute();
            //->with('contact')->get()[0]->contact;
            $companyName = Company::where('id', $contatto_mitt->company_id)->first()->rag_soc;
            //dd($companyName);


            if(isset($request->emails) && $request->emails != null){
                $ics_name = str_replace(" ","_",strtolower($mittente)).'_global.ics';

                self::createICS($event, $ics_name);

                //dd($filepath);
                

                
                $emails = explode(",",$request->emails);
                foreach($emails as $email){

                    $data = array(
                        'descrizione' => $event->title,
                        "data" => $data_evento,
                        'ora' => $ora_evento,
                        'luogo' => $event->location,
                        'mittente' => $mittente,
                        'email' =>  $email,
                        'email_from' => $contatto_mitt->email,
                        'azienda' => $companyName,
                        "file" => storage_path('app/public/calendars/'.$ics_name),
                        "name" => $ics_name                    
                    );

                    Mail::send('areaseb::emails.events.new-event-from-ext',$data, function ($message) use ($data)
                    {
                        $message->to($data['email'])
                            ->subject('Nuovo evento '.config('app.name'))
                            ->from($data['email_from'])
                            ->attach($data['file'], [
                                'as' => $data['name'],
                                'mime' => 'text/calendar',
                            ]);
                    });
                }

            }
		}
		
        $company_id = implode(" ", $request->company_id);
        if($company_id != "")
        {
            $event->companies()->attach($request->company_id);
        }
    }

    /**
     * Add expiring events in notification, and clean up when older than one day
     * @return [type] [description]
     */
    public static function expiringToday()
    {
        $eventsToNotify = Cache::remember('eventsToNotify', 60*23, function () {
            $from = Carbon::today();
            $to = Carbon::today()->addDays(1)->endOfDay();
            $events = self::whereBetween('starts_at', [$from, $to])->get();
            if(!$events->isEmpty())
            {
                foreach($events as $event)
                {
                    if(!Notification::where('notificationable_id', $event->id)->where('notificationable_type', get_class($event))->exists())
                    {
                        Notification::create([
                            'name' => $event->title,
                            'body' => $event->summary,
                            'notificationable_id' => $event->id,
                            'notificationable_type' => get_class($event),
                            'created_at' => $event->starts_at,
                            'user_id' => $event->calendar->user_id
                        ]);
                    }
                    else
                    {

                    }
                }
            }
            foreach(Notification::where('created_at', '<', Carbon::yesterday())->get() as $noty)
            {
                $noty->delete();
            }
            return $events;
        });
        return $eventsToNotify;
    }


}
