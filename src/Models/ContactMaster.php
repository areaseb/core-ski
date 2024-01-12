<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContactMaster extends Model
{
    protected $guarded = array();
    protected $table='masters';
    public $timestamps = true;
    protected $fillable = ['*'];


    public function isDisponibile($contact_id, $data_start, $data_end)
    {

        $query = "select count(*) as total from availabilities 
                                where contact_id = $contact_id and
                                (
                                    (data_start < \"$data_start\" and \"$data_start\" < data_end )
                                    or 
                                    (data_start < \"$data_end\" and \"$data_end\" < data_end )
                                    or 
                                    (data_start = \"$data_start\" or data_start = \"$data_end\")
                                    or
                                    (data_end = \"$data_start\" or data_end = \"$data_end\")
                                )";
                             
        $res = \DB::select($query);     
        //dd($res[0], $query);
        return $res[0]->total > 0 ? false : true;
    }
}
