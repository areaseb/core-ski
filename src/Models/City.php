<?php

namespace Areaseb\Core\Models;

use Illuminate\Support\Facades\Cache;

class City extends Primitive
{

    public function scopeItalian($query)
    {
        $query = $query->where('italia', true);
    }


    //list all provinces
    static public function uniqueProvinces($region = null)
    {
        if(is_null($region))
        {
            $provinces = Cache::remember('provinces', 60*24*7, function () {
                $arr[''] = '';
                foreach (self::select('provincia')->where('italia', 1)->distinct()->orderBy('provincia', 'ASC')->get() as $value)
                {
                    $arr[$value->provincia] = $value->provincia;
                }
                return $arr;
            });
        }
        else
        {
            $provinces[''] = '';

            foreach (self::select('provincia')->where('regione', $region)->distinct()->orderBy('provincia', 'ASC')->get() as $value)
            {
                $provinces[$value->provincia] = $value->provincia;
            }

        }
        return $provinces;
    }


    public static function select2Mutate($arr)
    {
        $obj = []; $count=0;
        foreach($arr as $id => $text)
        {
            $obj[$count]['id'] = $id;
            $obj[$count]['text'] = $text;
            $count++;
        }

        return $obj;
    }

    //list all provinces
    static public function uniqueRegions()
    {
        $regions = Cache::remember('regions', 60*24*7, function () {
            $arr[''] = '';
            foreach (self::select('regione')->where('italia', 1)->distinct()->orderBy('regione', 'ASC')->get() as $value)
            {
                $arr[$value->regione] = $value->regione;
            }
            $arr['Estero'] = 'Estero';
            return $arr;
        });

        return $regions;
    }

    public static function getCityIdFromData($provincia, $nazione, $comune = null)
    {

        $city = self::getCityFromData($provincia, $nazione, $comune);
        if($city)
        {
            return $city->id;
        }
        return null;
    }


    public static function getCityFromData($provincia, $nazione, $comune = null)
    {
        if(!is_null($comune))
        {
            $city = self::where('comune', $comune)->first();
            if($city)
            {
                return $city;
            }
        }

        $city = self::where('comune', $provincia)->first();
        if($city)
        {
            return $city;
        }

        $city = self::where('provincia', $provincia)->first();
        if($city)
        {
            return $city;
        }

        $city = self::where('sigla_provincia', $provincia)->first();
        if($city)
        {
            return $city;
        }

        $city = self::where('sigla_provincia', $nazione)->where('italia',0)->first();
        if($city)
        {
            return $city;
        }

        return null;
    }

    public static function cityFromCountry($sigla_nazione)
    {
        $city = self::where('sigla_provincia', $sigla_nazione)->where('italia',0)->first();
        if($city)
        {
            return $city;
        }
        return null;
    }

    public static function siglaFromProvincia($provincia)
    {
        if($provincia != '' || !is_null($provincia))
        {
            return self::where('provincia', $provincia)->first()->sigla_provincia;
        }
        return null;
    }

    public static function provinciaFromSigla($sigla, $cap = null)
    {
        if(!is_null($cap))
        {
            $city = self::where('cap', $cap)->first();
            if($city)
            {
                return $city->provincia;
            }
        }

        $city = self::where('sigla_provincia', $sigla)->first();
        if($city)
        {
            return $city->provincia;
        }

        $city = self::where('provincia', $sigla)->first();
        if($city)
        {
            return $city->provincia;
        }

        $city = self::where('comune', $sigla)->first();
        if($city)
        {
            return $city->provincia;
        }

    }

}
