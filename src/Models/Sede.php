<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $guarded = array();
    protected $table='branches';
    public $timestamps = true;
    protected $fillable = ['*'];

    public function contacts()
    {
        return \DB::table('contact_branch')->where('branch_id', $this->id);
    }
}
