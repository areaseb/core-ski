<?php

namespace Areaseb\Core\Models;
use Areaseb\Core\Models\Company;
use Areaseb\Core\Models\Contact;
use Areaseb\Core\Models\CollettivoAcconti;

use Illuminate\Database\Eloquent\Model;

class CollettivoAllievi extends Model
{
    protected $guarded = array();
    protected $table='collettivo_allievi';
    public $timestamps = true;
    protected $fillable = ['*'];


    public function customerName($customer_collective_id)
    {
        $customer = Company::where('id', $customer_collective_id)->first();

        return $customer != null ? $customer->rag_soc : '-';
    }

    public function studentName($customer_student_id)
    {
        $student = Contact::select('contacts.nome','contacts.cognome','contact_type.descrizione')
                            ->join('contact_type','contact_type.id','=','contacts.contact_type_id')
                            ->where('contacts.id', $customer_student_id)
                            ->first();

        return $student != null ? $student->nome.' '.$student->cognome . ' ('.$student->descrizione.')' : '-';
    }

    public function age($customer_student_id)
    {
        $student = Contact::where('id', $customer_student_id)->first();

        if(isset($student->data_nascita) && strtotime($student->data_nascita) > 0){
        	$now = new \DateTime();
	        $date = new \DateTime($student->data_nascita);
	        return $date->diff($now)->format("%y");
        } else {
        	return 'N.D.';
        }
        
    }

    public function getImportoData($customer_student_id, $collective_id)
    {

        return CollettivoAcconti::where('id_cliente', $customer_student_id)
                                ->where('id_collettivo', $collective_id)
                                ->first();

    }

    public function selectedByMasterAndDate($coll_id, $date, $master_id, $partecipant_id)
    {
        $student = $this::where('giorno', $date)
                                    ->where('id_maestro', $master_id)
                                    ->where('partecipante', $partecipant_id)
                                    ->where('id_collettivo', $coll_id)
                                    ->first();
                        
        
       
        return $student != null;
    }


}
