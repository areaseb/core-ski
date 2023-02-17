<?php

namespace Areaseb\Core\Models;

use Illuminate\Support\Facades\Cache;

class Exemption extends Primitive
{
    public $timestamps = false;

    public static function getIdByCode($code)
    {
        return self::where('codice', $code)->first();
    }

    public static function esenzioneBollo()
    {
        return 1;
    }

    /**
     * array ordered by codice
     * @return [type] [description]
     */
    public static function esenzioni()
    {
        $esenzioni = Cache::remember('esenzioni', 60*24*7, function () {
            $arr = [];
            foreach(self::where('connettore', 'Aruba')->orderBy('codice', 'ASC')->get() as $ex)
            {
                $arr[$ex->id] = $ex->codice . ' - ' . $ex->nome;
            }
            return $arr;
        });
        return $esenzioni;
    }

}
