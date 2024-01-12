<?php

namespace Areaseb\Core\Models;

use Carbon\Carbon;
use Areaseb\Core\Models\{Contact, Availability, Ora};

class Master extends Primitive
{

//ELOQUENT
    
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function availability() {
        return $this->hasMany(Availability::class, 'contact_id', 'contact_id');
    }

    public function hours() {
        return $this->hasMany(Ora::class, 'id_maestro', 'id');
    }
}