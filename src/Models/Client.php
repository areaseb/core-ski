<?php

namespace Areaseb\Core\Models;

class Client extends Primitive
{

    public $timestamps = false;

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public static function Client()
    {
        return self::where('nome', 'Client')->first();
    }

    public static function Lead()
    {
        return self::where('nome', 'Lead')->first();
    }

    public static function Prospect()
    {
        return self::where('nome', 'Prospect')->first();
    }

    public static function Referente()
    {
        return self::where('nome', 'Referente')->first();
    }

    public function scopeCompany($query)
    {
        $query = $query->where('company', true);
    }

    public function scopeContact($query)
    {
        $query = $query->where('contact', true);
    }

}
