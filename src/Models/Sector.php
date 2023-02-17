<?php

namespace Areaseb\Core\Models;

class Sector extends Primitive
{
    public $timestamps = false;

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

}
