<?php

namespace Areaseb\Core\Models;

use \Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class InvoicePayment extends Primitive
{
    protected $dates = ['date'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
