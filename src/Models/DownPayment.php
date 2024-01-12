<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;

class DownPayment extends Model
{
    protected $guarded = array();
    protected $table='down_payments';
    public $timestamps = true;
    protected $fillable = ['*'];


    public static function contact($id)
    {
        $contact = Contact::where('id',$id )->first();
        return $contact->nome.' '.$contact->cognome;
    }


}
