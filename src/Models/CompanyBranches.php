<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBranches extends Model
{
    protected $guarded = array();
    protected $table='company_branch';
    public $timestamps = true;
    protected $fillable = ['*'];

}
