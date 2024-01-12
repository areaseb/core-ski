<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    protected $guarded = array();
    protected $table='specializations';
    public $timestamps = true;
    protected $fillable = ['*'];
}
