<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Areaseb\Core\Models\Ora;

class Collettivo extends Model
{
    protected $guarded = array();
    protected $table='collettivo';
    public $timestamps = true;
    protected $fillable = ['*'];

	public static function query() {
        $query = parent::query();
      
        if(!auth()->user()->hasRole('super')){
        	$user_branch = auth()->user()->contact->branchContact()->branch_id;        	
        	$query = $query->where('centro_costo', 'like', '%'.$user_branch.'%');
        	return $query;
        }		
        
		return $query;
    }


    public function listDaysAndMasters()
    {

        $list = Ora::select('ora.id','ora.id_maestro','masters.id','contacts.nome','contacts.cognome','ora.data')
                            ->join('masters','masters.id','=','ora.id_maestro')
                            ->join('contacts','contacts.id','=','masters.contact_id')
                            ->where('id_cliente', 'C_'.$this->id)	//str_pad($this->id, 5, '0', STR_PAD_LEFT)
                            ->orderBy('ora.data')
                            ->get();

        $dict = [];
        foreach ($list as $value) {



            if($this->existOra($dict,$value->data) != -1){

                $index = $this->existOra($dict,$value->data); 
                           
                $arr = $dict[$index][$value->data];

                array_push($arr,$value);   
                unset($dict[$index]);
                $obj = array();
                $obj[$value->data] = $arr;
                array_push($dict,$obj);
            }
            else{
                $obj = array();
                $items = [];
                array_push($items,$value);
                $obj[$value->data] = $items;
                array_push($dict,$obj);
            }

        }
        //dd($dict);
        return $dict;
    }


    public function listMastersInTable()
    {

        $list = Ora::select('masters.id','contacts.nome','contacts.cognome','ora.data')
                            ->join('masters','masters.id','=','ora.id_maestro')
                            ->join('contacts','contacts.id','=','masters.contact_id')
                            ->where('id_cliente', 'C_'.$this->id)	//str_pad($this->id, 5, '0', STR_PAD_LEFT)
                            ->groupby('masters.id')
                            ->get();
        $masters = "";
        foreach ($list as $value) {
            $masters = $masters == "" ? $value->cognome.' '.$value->nome : $masters.'<br>'.$value->cognome.' '.$value->nome; 
        }
       return $masters;
    }
    
    public function getDate()
    {
    	$date = Ora::where('id_cliente', 'C_'.$this->id)->distinct('data')->pluck('data')->toArray();
    
    	return $date;
    }


    private function existOra($list, $data){
        foreach ($list as $key => $value) {
            if(isset($value[$data]))
               return $key;
        }
        return -1;
    }
}
