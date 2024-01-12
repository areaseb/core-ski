<?php

namespace Areaseb\Core\Models;
use Areaseb\Core\Models\Company;
use Areaseb\Core\Models\Contact;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $guarded = array();
    protected $table='labels';
    public $timestamps = true;
    protected $fillable = ['*'];


}
