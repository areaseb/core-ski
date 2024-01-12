<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class DisabileSedute extends Model
{
    protected $guarded = array();
    protected $table='disabile_sedute';
    public $timestamps = true;
    protected $fillable = ['*'];
}
