<?php

namespace Areaseb\Core\Models;

use \Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class Expense extends Primitive
{
    public $timestamps = false;

    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

    public static function default()
    {
        $default = Cache::remember('default', 60*24*7*56, function () {
            return self::where('nome', 'Da Categorizzare')->first();
        });
        return $default;
    }

    public function getIsDefaultAttribute()
    {
        if($this->id == self::default()->id)
        {
            return true;
        }
        return false;
    }

}
