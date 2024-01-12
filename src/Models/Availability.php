<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $guarded = array();
    protected $table='availabilities';
    public $timestamps = true;
    protected $fillable = ['*'];
}
