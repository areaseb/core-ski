<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class ContactDisabled extends Model
{
    protected $guarded = array();
    protected $table='contact_disabled';
    public $timestamps = true;
    protected $fillable = ['*'];
}
