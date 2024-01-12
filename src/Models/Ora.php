<?php

namespace Areaseb\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Areaseb\Core\Models\{Master, Invoice, InvoiceOra};

class Ora extends Model
{
    protected $guarded = array();
    protected $table='ora';
    public $timestamps = true;
    protected $fillable = ['*'];
    
    public static function query() {
        $query = parent::query();
        
        if(!auth()->user()->hasRole('super')){
        	$user_branch = auth()->user()->contact->branchContact()->branch_id;
        	$query = $query->where('id_cc', $user_branch);
        	return $query;
        }		
        
		return $query;
    }

	public function teacher() {
		return $this->belongsTo(Master::class, 'id_maestro', 'id');
	}

	public function invoice() {
		return $this->hasOneThrough(Invoice::class, InvoiceOra::class, 'ora_id', 'id', 'id', 'invoice_id');
	}
}
