<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = array();

    public function notificationable()
    {
        return $this->morphTo();
    }

    public function getDirectoryAttribute()
    {
        return str_plural(strtolower($this->class));
    }

    //get url of element
    public function getUrlAttribute()
    {
        if(strpos($this->notificationable_type, 'Fe') !== false)
        {
            return null;
        }
        elseif(strpos($this->notificationable_type, 'Event') !== false)
        {
            $event = Event::find($this->notificationable_id);
            if($event)
            {
                return route('calendars.show', $event->calendar_id);
            }
        }
        else
        {
            return config('app.url') . $this->notificationable->directory . '/' . $this->notificationable->id;
        }
        return null;
    }

    public function getModalAttribute()
    {
        if(is_null($this->url))
        {
            $arr = explode("\\", $this->notificationable_type);
            return (object) ['class' => end($arr)];
        }
        return null;
    }

    public function scopeUnread($query)
    {
        $query = $query->where('read', 0);
    }

}
