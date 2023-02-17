<?php

namespace Areaseb\Core\Models;

use \Carbon\Carbon;
use App\User;

class Note extends Primitive
{

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getIsNewAttribute()
    {
        if($this->company->client_id != '2')
        {
            return false;
        }
        return ($this->created_at == $this->updated_at);
    }

}
