<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class DisabileTipo extends Model
{
    protected $guarded = array();
    protected $table='disabile_tipo';
    public $timestamps = true;
    protected $fillable = ['*'];
}
