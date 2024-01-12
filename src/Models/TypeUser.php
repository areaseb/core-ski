<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class TypeUser extends Model
{
    protected $guarded = array();
    protected $table='contact_type';
    public $timestamps = true;
    protected $fillable = ['*'];
}
