<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use \Carbon\Carbon;

class Primitive extends Model
{
    protected $guarded = array();

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notificationable');
    }

    //get class name
    public function getClassAttribute()
    {
        $arr = explode("\\", get_class($this));
        return end($arr);
    }

    //get class name
    public function getFullClassAttribute()
    {
        return get_class($this);
    }

    //autogenerate slug and storage folder name from class
    public function getDirectoryAttribute()
    {
        return str_plural(strtolower($this->class));
    }

    public static function getClassFromDirectory($directory, $path)
    {
        return $path.'\\'.str_singular(ucfirst($directory));
    }


    //get url of element
    public function getUrlAttribute()
    {
        return config('app.url') . $this->directory . '/' . $this->id;
    }

    //check if model has column in table
    public function scopeHasColumn($query, $column_name)
    {
        return Schema::connection('mysql')->hasColumn($query->getQuery()->from, $column_name);
    }

    //currency formatter
    public function fmt($number)
    {
		$fmt = "€ " . number_format($number, 2, ',', '.');
		return $fmt;
    }


    //color creator
    public function color($value)
    {

        // colors:
        // success = #28a745
        // danger = #dc3545
        // warning = #ffc107

        if($value < 10)
        {
            return '#dc3545';
        }
        elseif($value < 100)
        {
            return '#ffc107';
        }
        return '#28a745';

        /*
        $brightness = 255; $max = 100; $min = 0; $thirdColorHex = '00';

        // Calculate first and second color (Inverse relationship)
        $first = (1-($value/$max))*$brightness;
        $second = ($value/$max)*$brightness;

        // Find the influence of the middle color (yellow if 1st and 2nd are red and green)
        $diff = abs($first-$second);
        $influence = ($brightness-$diff)/2;
        $first = intval($first + $influence);
        $second = intval($second + $influence);

        // Convert to HEX, format and return
        $firstHex = str_pad(dechex($first),2,0,STR_PAD_LEFT);
        $secondHex = str_pad(dechex($second),2,0,STR_PAD_LEFT);

        return $firstHex . $secondHex . $thirdColorHex;
        */
    }

    //decimal
    public function decimal($number)
    {
        return number_format($number, 2, ',','.');
    }

    public static function NF($number)
    {
        return '€ '.number_format($number, 2, ',', '.');
    }

    public function scopeNation($query, $field)
    {
        if($query->hasColumn('nazione'))
        {
            return $query->where('nazione', $field);
        }

        if($query->hasColumn('nation'))
        {
            return $query->where('nation', $field);
        }

        return $query;
    }


    public function scopeRegion($query, $search)
    {
        if($query->hasColumn('city_id'))
        {
            if(is_array($search))
            {
                return $query->whereHas('city',
                    function($q) use($search){
                        $q->whereIn('regione',$search);
                    });
            }

            if($search == 'Estero')
            {
                $query->where('nation', '!=', 'IT');
            }
            else
            {
                return $query->whereHas('city',
                    function($q) use($search){
                        $q->where('regione',$search);
                    });
            }
        }
        return $query;
    }

    public function scopeProvince($query, $search)
    {
        if($query->hasColumn('city_id'))
        {
            if(is_array($search))
            {
                return $query->whereHas('city',
                    function($q) use($search){
                        $q->whereIn('provincia',$search);
                    });
            }
            return $query->whereHas('city',
                function($q) use($search){
                    $q->where('provincia',$search);
                });
        }
        return $query;
    }

    public function scopeUpdated($query, $days)
    {
        return $query->whereDate('updated_at', '>=', Carbon::today()->subDays( $days ) );
    }

    public function scopeCreated($query, $days)
    {
        return $query->whereDate('created_at', '>=', Carbon::today()->subDays( $days ) );
    }

}
