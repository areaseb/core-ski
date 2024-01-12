<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class ContactBranches extends Model
{
    protected $guarded = array();
    protected $table='contact_branch';
    public $timestamps = true;
    protected $fillable = ['*'];
}
