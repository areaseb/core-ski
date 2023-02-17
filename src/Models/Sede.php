<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $guarded = array();
    protected $table='sedi';
    public $timestamps = false;
    protected $fillable = ['*'];
}
