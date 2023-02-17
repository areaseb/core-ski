<?php

namespace Areaseb\Core\Models;


class CostPayment extends Primitive
{
    protected $dates = ['date'];

    public function cost()
    {
        return $this->belongsTo(Cost::class);
    }
}
