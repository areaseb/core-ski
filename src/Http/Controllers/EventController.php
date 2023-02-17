<?php

namespace Areaseb\Core\Http\Controllers;

use App\User;
use Areaseb\Core\Models\{Calendar, Contact, Company, Event};
use Illuminate\Http\Request;
use \Carbon\Carbon;
use \Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;


class EventController extends Controller
{

//expiring-events - POST
    public function expiring()
    {
        $now = Carbon::now();
        //$now = Carbon::parse('test time here');
        $from = (clone $now)->addSeconds(30);
        $to = (clone $now)->subSeconds(30);

        // $from = Carbon::parse('2021-07-14 13:00:00');
        // $to = Carbon::parse('2021-07-14 16:00:00');

        $user = auth()->user();

        if($user->hasPermissionTo('costs.read'))
        {
            $scadenze = Calendar::firstOrCreate(['nome' => 'scadenze', 'user_id' => 1, 'privato' => 0]);
            $calendars[] = $scadenze->id;
        }
        if(Schema::hasTable('killer_quotes'))
        {
            if($user->hasPermissionTo('killerquotes.read'))
            {
                $preventivi = Calendar::firstOrCreate(['nome' => 'preventivi', 'user_id' => 1, 'privato' => 0]);
                $calendars[] = $preventivi->id;
            }
        }

        $calendars[] = Calendar::where('user_id', $user->id)->where('nome', 'global')->first()->id;

        $events = Event::whereBetween('starts_at', [$from, $to])->whereIn('calendar_id', $calendars)->get();
        return $events;
    }

//api/events/
    public function defaultUserEvent()
    {
        return auth()->user()->default_calendar->events()
                ->betweenDates($request->input())
                ->select('id', 'title', 'starts_at as start', 'ends_at as end' ,'allday', 'backgroundColor', 'backgroundColor as borderColor')
                ->get();
    }

//api/events/{userId}
    public function userEvent($id, Request $request)
    {
        return User::find($id)->default_calendar->events()
                ->betweenDates($request->input())
                ->select('id', 'title', 'starts_at as start', 'ends_at as end' ,'allday', 'backgroundColor', 'backgroundColor as borderColor')
                ->get();
    }

//api/events/{event}/done
    public function markAsDone(Event $event)
    {
        if($event->done)
        {
            $event->update(['done' => 0, 'backgroundColor' => '#3788d8']);
        }
        else
        {
            $event->update(['done' => 1, 'backgroundColor' => '#019222']);
        }

        return 'done';
    }


//api/calendars/{calendar_id}/events
    public function calendarEvent($id, Request $request)
    {
        if(is_numeric($id))
        {
            return Calendar::find($id)->events()
                    ->betweenDates($request->input())
                    ->select('id', 'title', 'starts_at as start', 'ends_at as end' ,'allday', 'backgroundColor', 'backgroundColor as borderColor')
                    ->get();
        }

        $arrIds = explode('-', $id);
        $collection = collect();

        foreach ($arrIds as $key => $id_calendar)
        {
            $events = Calendar::find($id_calendar)->events()
                    ->betweenDates($request->input())
                    ->select('id', 'calendar_id' ,'title', 'starts_at as start', 'ends_at as end' , 'allday', 'backgroundColor', 'backgroundColor as borderColor')
                    ->get();

            Event::mutateColors($events, $key);

            $collection = $collection->merge($events);
        }

        return $collection->all();
    }

//events - POST
    public function store(Request $request)
    {

        if($request->type == 'ricursivo')
        {
            $idsRecursive = [];
            for($x=0; $x < $request->x_times; $x++)
            {
                $starts_at = Carbon::createFromFormat('d/m/Y H:i', $request->from_date . ' ' . ($request->da_ora) .':'.$request->da_minuto);
                $ends_at = Carbon::createFromFormat('d/m/Y H:i', $request->from_date . ' ' . ($request->a_ora) .':'.$request->a_minuto);

                if(strpos($request->every, 'd') !== false)
                {
                    $days = intval($request->every);
                    $start = $starts_at->addDays($x*$days)->format('Y-m-d H:i:s');
                    $end = $ends_at->addDays($x*$days)->format('Y-m-d H:i:s');
                }
                else
                {
                    $start = $starts_at->addMonths($x*$request->every)->format('Y-m-d H:i:s');
                    $end = $ends_at->addMonths($x*$request->every)->format('Y-m-d H:i:s');
                }

                $data = [
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'summary' => $request->summary,
                    'title' => $request->titolo,
                    'location' => $request->location,
                    'user_id' => auth()->user()->id,
                    'calendar_id' => $request->calendar_id,
                    'backgroundColor' => '#ffc107'
                ];

                $event = Event::create($data);

                Event::attachModels($event, $request);

                $idsRecursive[] = $event->id;
            }

            return Event::whereIn('id', $idsRecursive)->get();

        }
        elseif($request->type == 'singolo')
        {
            $event = Event::create([
                'starts_at' => Carbon::createFromFormat('d/m/Y H:i', $request->date_singolo . ' ' . ($request->da_ora) .':'.$request->da_minuto)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::createFromFormat('d/m/Y H:i', $request->date_singolo . ' ' . ($request->a_ora) .':'.$request->a_minuto)->format('Y-m-d H:i:s'),
                'summary' => $request->summary,
                'title' => $request->titolo,
                'location' => $request->location,
                'user_id' => auth()->user()->id,
                'calendar_id' => $request->calendar_id
            ]);
        }
        elseif($request->type == 'allday')
        {
            $arr = explode(' - ', $request->range);
            $event = Event::create([
                'starts_at' => Carbon::createFromFormat('d/m/Y H:i:s', trim($arr[0]).' 08:00:00')->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::createFromFormat('d/m/Y H:i:s', trim($arr[1]).' 23:59:59')->format('Y-m-d H:i:s'),
                'summary' => $request->summary,
                'title' => $request->titolo,
                'location' => $request->location,
                'allday' => true,
                'backgroundColor' => '#28a745',
                'user_id' => auth()->user()->id,
                'calendar_id' => $request->calendar_id
            ]);
        }

        Event::attachModels($event, $request);

        return $event;
    }

    //events/{id} - GET
    public function show(Event $event)
    {
       
        if($event->eventable_type == 'KillerQuote\App\Models\KillerQuote')
        {
            
            if(\KillerQuote\App\Models\KillerQuoteNote::where('killer_quote_id', $event->eventable_id)->exists())
            {
                return [
                    'event' => $event->where('id', $event->id)->with('companies', 'users', 'contacts')->get(),
                    'notes' => \KillerQuote\App\Models\KillerQuoteNote::where('killer_quote_id', $event->eventable_id)->get(),
                    'killer_id' => $event->eventable_id
                ];
            }
            else
            {
                return [
                    'event' => $event->where('id', $event->id)->with('companies', 'users', 'contacts')->get(),
                    'notes' => 'empty',
                    'killer_id' => $event->eventable_id
                ];
            }
        }
        elseif(strpos($event->eventable_type, 'Deals') !== false)
        {
            if($event->eventable_type == 'Deals\App\Models\DealGenericNote')
            {
                $e = \Deals\App\Models\DealEvent::where('dealable_type', 'Deals\App\Models\DealGenericNote')->where('dealable_id', $event->eventable_id)->first();
                if($e)
                {
                    return [
                        'event' => $event->where('id', $event->id)->with('companies', 'users', 'contacts')->get(),
                        'deal_id' => $e->deal_id
                    ];
                }

            }


        }

        if($event->parent_event_id == null)
            $event_record = $event->where('id', $event->id)->with('user','companies', 'users', 'contacts')->get();
        else
            $event_record = $event->where('id', $event->parent_event_id)->with('user','companies', 'users', 'contacts')->get();

           
        $autore = Contact::where('id', $event_record[0]->user_id)->first();
        $event_record[0]->autore =  $autore->nome.' '. $autore->cognome;
        return ['event' => $event_record];
    }

//events/{id}/edit - GET
    public function edit(Event $event)
    {
        $contacts= Contact::all()->pluck('fullname' ,'id')->toArray();
        $companies = Company::pluck('rag_soc', 'id')->toArray();
        $users = User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        $selectedCompany = $event->companies()->pluck('id');
        $selectedUsers = $event->users()->pluck('id');
        $selectedContacts = $event->contacts()->pluck('id');
        $calendars = [];
        foreach(Calendar::all() as $cal)
        {
            $calendars[$cal->id] = ($cal->nome == 'global') ? $cal->user->contact->fullname : $cal->nome;
        }
        return view('areaseb::core.events.edit', compact('event', 'contacts', 'selectedContacts', 'companies', 'selectedCompany', 'users', 'selectedUsers', 'calendars'));
    }

//events/{id}/edit - GET
    public function update(Event $event, Request $request)
    {

        $event->calendar_id = $request->calendar_id;

        if($request->range)
        {

        }
        else
        {
            $event->starts_at = Carbon::createFromFormat('d/m/Y H:i', $request->from_date . ' ' . ($request->da_ora) .':'.$request->da_minuto)->format('Y-m-d H:i:s');
            $event->ends_at = Carbon::createFromFormat('d/m/Y H:i', $request->from_date . ' ' . ($request->a_ora) .':'.$request->a_minuto)->format('Y-m-d H:i:s');
            $event->title = $request->title;
            $event->summary = $request->summary;
            $event->location = $request->location;
        }

        $event->save();

        if($request->contact_id)
        {
            $event->contacts()->sync($request->contact_id);
        }
        if($request->user_id)
        {
            $event->users()->sync($request->user_id);
        }
        if($request->company_id)
        {
            $event->companies()->sync($request->company_id);
        }

        return redirect('calendars/'.$event->calendar_id)->with('message', 'Evento modificato');
    }

//events/{id} - DELETE ALL
    public function destroy(Event $event, Request $request)
    {
        
        $calendar_id = $event->calendar_id;
        if($event->eventable_type == 'Deals\App\Models\DealGenericNote')
        {
            $dGN = \DB::table('deal_generic_notes')->where('id', $event->eventable_id)->first();
            $dE = \DB::table('deal_events')->where('dealable_id', $dGN->id)->where('dealable_type', 'Deals\App\Models\DealGenericNote')->first();
            if($dE)
            {
                \DB::table('deal_events')->where('dealable_id', $dGN->id)->where('dealable_type', 'Deals\App\Models\DealGenericNote')->orderBy('id')->limit(1)->delete();
            }
            if($dGN)
            {
                \DB::table('deal_generic_notes')->where('id', $event->eventable_id)->orderBy('id')->limit(1)->delete();
            }
        }

        $count_events = \DB::table('event_user')->where('event_id', $event->id)->count();
        if($count_events > 0){ // se ho relazioni
            \DB::table('event_user')->where('event_id', $event->id)->delete();
        }
        $event->delete();
        Event::where('parent_event_id',  $event->id)->delete();
        return redirect('calendars/'.$calendar_id)->with('message', 'Evento eliminato');
    }


    public function deleteEventForMe(Event $event, Request $request)
    {
        $calendar_id = $event->calendar_id;
        if($event->eventable_type == 'Deals\App\Models\DealGenericNote')
        {
            $dGN = \DB::table('deal_generic_notes')->where('id', $event->eventable_id)->first();
            $dE = \DB::table('deal_events')->where('dealable_id', $dGN->id)->where('dealable_type', 'Deals\App\Models\DealGenericNote')->first();
            if($dE)
            {
                \DB::table('deal_events')->where('dealable_id', $dGN->id)->where('dealable_type', 'Deals\App\Models\DealGenericNote')->orderBy('id')->limit(1)->delete();
            }
            if($dGN)
            {
                \DB::table('deal_generic_notes')->where('id', $event->eventable_id)->orderBy('id')->limit(1)->delete();
            }
        }

        $event_id = $event->parent_event_id != null ? $event->parent_event_id : $event->id;
        \DB::table('event_user')->where('event_id', $event_id)->where('user_id', auth()->user()->id)->delete();
        if($event->parent_event_id != null)
            $event->delete();

        return redirect('calendars/'.$calendar_id)->with('message', 'Evento eliminato');
    }

    //api-events/{event} - GET
    public function apiEvent(Event $event)
    {
        return $event;
    }


//addevent/{token}
    public function createFromToken($token)
    {
        $calendar = Calendar::where('token', $token)->first();

        if(is_null($calendar))
        {
            abort(404);
        }

        $utente = User::find($calendar->user_id);

        $companies = [''=>'']+Company::pluck('rag_soc', 'id')->toArray();

        $calendars = Calendar::where('user_id', $utente->id)->pluck('nome', 'id');

        $users = [];
        foreach( User::where('id', '!=', $utente->id)->get() as $u)
        {
            $users[$u->id] = $u->fullname;
        }


        return view('areaseb::core.events.createToken', compact('calendar', 'utente', 'token', 'companies', 'calendars', 'users'));
    }



    public static function createICS($event, $ics_name)
    {
        foreach (self::all() as $calendar)
        {
            $contents = $calendar->header;
$description = (strstr($event->summary, PHP_EOL)) ? '' : $event->summary;
                $singleEvent =
"\r\nBEGIN:VEVENT\r
DTSTART:".$event->starts_at->format('Ymd\THis')."\r
DTEND:".$event->ends_at->format('Ymd\THis')."\r
DTSTAMP:".Carbon::now()->format('Ymd\THis')."\r
UID:".uniqid()."\r
CREATED:".$event->created_at->format('Ymd\THis')."\r
DESCRIPTION:".$description."\r
LAST-MODIFIED:".$event->updated_at->format('Ymd\THis')."\r
LOCATION:".$event->location."\r
SEQUENCE:1\r
STATUS:CONFIRMED\r
SUMMARY:".$event->title."\r
TRANSP:OPAQUE\r
BEGIN:VALARM\r
ACTION:DISPLAY\r
DESCRIPTION:This is an event reminder\r
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



//addevent/{token} - POST
    public function storeFromToken(Request $request, $token)
    {
        $event = new Event;
            $event->starts_at = Carbon::createFromFormat('d/m/Y H:i', $request->from_date . ' ' . ($request->da_ora) .':'.$request->da_minuto)->format('Y-m-d H:i:s');
            $event->ends_at = Carbon::createFromFormat('d/m/Y H:i', $request->from_date . ' ' . ($request->a_ora) .':'.$request->a_minuto)->format('Y-m-d H:i:s');
            $event->title = $request->title;
            $event->summary = $request->summary;
            $event->location = $request->location;
            $event->user_id = $request->user_id;
            $event->calendar_id = $request->calendar_id;
        $event->save();

        if($request->company_id)
        {
            $event->companies()->attach($request->company_id);
        }


        if(isset($request->emails) && $request->emails != null){
            $dsn = 'smtp://'.Setting::smtp(0)['MAIL_USERNAME'].':'.Setting::smtp(0)['MAIL_PASSWORD'].'@'.Setting::smtp(0)['MAIL_HOST'].':'.Setting::smtp(0)['MAIL_PORT'];
            Mail::mailer($dsn);

            $datetime_evento = explode(" ",$event->starts_at);
            $date_evento = explode("-",$datetime_evento[0]);
            $data_evento = $date_evento[2].'/'.$date_evento[1].'/'.$date_evento[0];
            $ora_evento = substr($datetime_evento[1],0,5);


            $contatto_mitt = User::where('id', auth()->user()->id)->with('contact')->get()[0]->contact;
            $mittente = User::where('id', auth()->user()->id)->first()->getFullnameAttribute();
            //->with('contact')->get()[0]->contact;
            $companyName = Company::where('id', $contatto_mitt->id)->first()->rag_soc;

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
                    'azienda' => $companyName,
                    "file" => storage_path('app/public/calendars/'.$ics_name),
                    "name" => $ics_name                    
                );

                Mail::send('areaseb::emails.events.new-event-from-ext',$data, function ($message) use ($data)
                {
                    $message->to($data['email'])
                        ->subject('Nuovo evento '.config('app.name'))
                        ->from(Setting::smtp(0)['MAIL_FROM_ADDRESS'])
                        ->attach($data['file'], [
                            'as' => $data['name'],
                            'mime' => 'text/calendar',
                        ]);
                });
            }

        }

        if($request->users)
        {
            foreach($request->users as $user_id)
            {
                $calendar_id = User::find($user_id)->calendars()->first()->id;
                $event = new Event;
                    $event->starts_at = Carbon::createFromFormat('d/m/Y H:i', $request->from_date . ' ' . ($request->da_ora) .':'.$request->da_minuto)->format('Y-m-d H:i:s');
                    $event->ends_at = Carbon::createFromFormat('d/m/Y H:i', $request->from_date . ' ' . ($request->a_ora) .':'.$request->a_minuto)->format('Y-m-d H:i:s');
                    $event->title = $request->title;
                    $event->summary = $request->summary;
                    $event->location = $request->location;
                    $event->user_id = $user_id;
                    $event->calendar_id = $calendar_id;
                $event->save();

                if($request->company_id)
                {
                    $event->companies()->attach($request->company_id);
                }
            }
        }

        return back()->with('message', 'Evento Aggiunto');
    }



}
