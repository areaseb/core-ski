<?php

namespace Areaseb\Core\Models;
use Areaseb\Core\Models\Company;
use Areaseb\Core\Models\Contact;
use Illuminate\Database\Eloquent\Model;

class CollettivoAcconti extends Model
{
    protected $guarded = array();
    protected $table='collettivo_acconti';
    public $timestamps = true;
    protected $fillable = ['*'];

}
