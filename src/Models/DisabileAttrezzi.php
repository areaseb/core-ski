<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class DisabileAttrezzi extends Model
{
    protected $guarded = array();
    protected $table='disabile_attrezzi';
    public $timestamps = true;
    protected $fillable = ['*'];
}
