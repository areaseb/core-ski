<?php

namespace Areaseb\Core\Models;

use \Carbon\Carbon;
use \Storage;
use App\User;
use Illuminate\Support\Facades\Cache;

class Calendar extends Primitive
{
    public $timestamps = false;

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public static function Scadenze()
    {
        $cal = self::where('nome', 'scadenze')->first();
        if($cal)
        {
            return $cal->id;
        }

        $cal = self::create([
            'user_id' => 1,
            'nome' => 'scadenze'
        ]);

        return $cal->id;
    }

    /**
     * well formatted calendars
     * @return [collections]
     */
    public static function formatted()
    {
        $cals = Cache::remember('cals', 60*10, function () {
            $calendars = [];
            foreach(self::with('user')->get() as $cal)
            {
                if($cal->nome == 'global')
                {
                	$calendars[$cal->id] = $cal->user->contact->fullname;                    
                }
                else
                {
                    $calendars[$cal->id] = ucfirst($cal->nome);
                }
            }
            return $calendars;
        });
        return $cals;
    }

    /**
     * all calendars without those associated to the current user
     * @return [collections]
     */
    public static function allNoCurrentUser()
    {
        return self::where('user_id', '!=', auth()->user()->id)->get();
    }

    /**
     * loop each calendar AND create a .ics AND save it in storage
     * @return [void]
     */
    public static function createICS()
    {
        foreach (self::all() as $calendar)
        {
            $contents = $calendar->header;
            
            foreach($calendar->events as $event)
            {
            	
				$description = (strstr($event->summary, PHP_EOL)) ? '' : $event->summary;
				
                $singleEvent = "BEGIN:VEVENT\r
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
END:VEVENT\r\n";
$contents .= $singleEvent;
            }
            $contents .= "END:VCALENDAR";

            $filename = 'public/calendars/'.$calendar->ics_name.'.ics';

            if (Storage::exists($filename))
            {
                Storage::delete($filename);
            }

            Storage::put($filename, $contents);
        }
    }

    /**
     * make header of ics using user name and calendar name
     * @return [string] ics header
     */
    public function getHeaderAttribute()
    {
        return "BEGIN:VCALENDAR\r
PRODID:-//".config('app.name')."//IT\r
VERSION:2.0\r
CALSCALE:GREGORIAN\r
X-WR-CALNAME:calendario_".$this->ics_name."\r
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
END:VTIMEZONE\r\n";
    }

    public function getIcsNameAttribute()
    {
        if( ($this->user->id == 1) && ($this->nome == 'global') )
        {
            return str_slug("admin ".$this->nome, "_");
        }
        else
        {
            return str_slug($this->user->full_name." ".$this->nome, "_");
        }
    }

}
