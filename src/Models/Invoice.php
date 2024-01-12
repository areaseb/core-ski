<?php

namespace Areaseb\Core\Models;

use \Carbon\Carbon;
use Deals\App\Models\DealEvent;
use Illuminate\Support\Facades\Cache;

class Invoice extends Primitive
{
    protected $dates = ['data', 'data_registrazione', 'ddt_data_doc', 'pa_data_doc', 'data_saldo', 'data_scadenza'];

    protected $casts = [
        'rates' => 'array',
    ];

	public static function query() {
        $query = parent::query();
        
        if(!auth()->user()->hasRole('super')){
        	$user_branch = auth()->user()->contact->branchContact()->branch_id;
        	$query = $query->where('branch_id', $user_branch);
        	return $query;
        }		
        
		return $query;
    }
    
    public function dealEvent() {
        if(class_exists('Deals\App\Models\DealEvent'))
            return $this->morphOne(DealEvent::class, 'dealable');
        return null;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function exemption()
    {
        return $this->belongsTo(Exemption::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function notices()
    {
        return $this->hasMany(InvoiceNotice::class);
    }

    public function contact($contact_id)
    {
        return Contact::find($this->contact_id);
    }


// setter
    public function setDataAttribute($value)
    {
    	if(strstr($value, '/')){
    		$data = explode(' ', $value);
    		list($g, $m, $a) = explode('/', $data[0]);
    		$value = "$a-$m-$g";
    	}
        $this->attributes['data'] = is_null($value) ? null : date('Y-m-d', strtotime($value));	//Carbon::createFromFormat('d/m/Y', Carbon::parse($value)->format('d-m-Y'));
    }

    public function setDataRegistrazioneAttribute($value)
    {
    	if(strstr($value, '/')){
    		$data = explode(' ', $value);
    		list($g, $m, $a) = explode('/', $data[0]);
    		$value = "$a-$m-$g";
    	}
        $this->attributes['data_registrazione'] = is_null($value) ? null : date('Y-m-d', strtotime($value));	//Carbon::createFromFormat('d/m/Y', Carbon::parse($value)->format('d-m-Y'));
    }

    public function setDdtDataDocAttribute($value)
    {
    	if(strstr($value, '/')){
    		$data = explode(' ', $value);
    		list($g, $m, $a) = explode('/', $data[0]);
    		$value = "$a-$m-$g";
    	}
        $this->attributes['ddt_data_doc'] = is_null($value) ? null : date('Y-m-d', strtotime($value));	//Carbon::createFromFormat('d/m/Y', Carbon::parse($value)->format('d-m-Y'));
    }

    public function setPaDataDocAttribute($value)
    {
    	if(strstr($value, '/')){
    		$data = explode(' ', $value);
    		list($g, $m, $a) = explode('/', $data[0]);
    		$value = "$a-$m-$g";
    	}
        $this->attributes['pa_data_doc'] = is_null($value) ? null : date('Y-m-d', strtotime($value));	//Carbon::createFromFormat('d/m/Y', Carbon::parse($value)->format('d-m-Y'));
    }

    public function setDataSaldoAttribute($value)
    {
    	if(strstr($value, '/')){
    		$data = explode(' ', $value);
    		list($g, $m, $a) = explode('/', $data[0]);
    		$value = "$a-$m-$g";
    	}
        $this->attributes['data_saldo'] = is_null($value) ? null : date('Y-m-d', strtotime($value));	//Carbon::createFromFormat('d/m/Y', Carbon::parse($value)->format('d-m-Y'));
    }

    public function setPagamentoAttribute($value)
    {
        if($value == 'RB**')
        {
            $this->attributes['pagamento'] = 'RBFM';
        }
        $this->attributes['pagamento'] = $value;
    }



//getter

    public function getMaxNumber($type, $branch_id = null)
    {
    	if(!is_null($branch_id)){
    		return self::where('tipo', $type)->where('branch_id', $branch_id)->where('aperta', 0)->whereYear('data', date("Y"))->orderBy('numero', 'DESC')->first()->numero + 1;
    	} else {
    		return self::where('tipo', $type)->where('branch_id', auth()->user()->contact->branchContact()->branch_id)->where('aperta', 0)->whereYear('data', date("Y"))->orderBy('numero', 'DESC')->first()->numero + 1;
    	}
        
    }

    public function getPaymentStatusAttribute()
    {
        if($this->saldato)
        {
            return 0;
        }
        if($this->total > 0){
        	return 100-round(($this->total - $this->payments()->sum('amount'))/$this->total*100);
        } else {
        	return 0;
        }
        
    }

    public function getPaymentColorAttribute()
    {
        return $this->color( $this->payment_status );
    }

    public function getHasBolloAttribute()
    {
        if($this->bollo > 0)
        {
            return true;
        }
        return false;
    }

    public function getHasBolloInItemsAttribute()
    {
        foreach($this->items as $item)
        {
            if($item->is_bollo)
            {
                return true;
            }
        }
        return false;
    }

    public function getTitoloAttribute()
    {
        return $this->tipo_formatted . " N." . $this->numero . " / " . $this->branch_id. " / " .$this->data->format('Y');
    }

    public function getTotalAttribute()
    {
    	if($this->bollo && $this->bollo_a == 'cliente'){
			$imponibile = $this->imponibile;	// - $this->bollo;
		} else {
			$imponibile = $this->imponibile;
		}
		
        return $imponibile + $this->iva - $this->ritenuta;	// + $this->bollo;
    }

    public function getTotalFormattedAttribute()
    {
        return $this->fmt($this->total + $this->rounding);
    }

    public function getTotalDecimalAttribute()
    {
        return $this->decimal($this->total + $this->rounding);
    }

    public function getImponibileFormattedAttribute()
    {	
    	if($this->bollo && $this->bollo_a == 'cliente'){
    		return $this->fmt($this->imponibile);	// - $this->bollo
    	} else {
    		return $this->fmt($this->imponibile);
    	}	
        
    }

    public function getImponibileDecimalAttribute()
    {
        return $this->decimal($this->imponibile);	// - $this->bollo
    }

    public function getSpeseFormattedAttribute()
    {
        return $this->fmt($this->spese);
    }

    public function getSpeseDecimalAttribute()
    {
        return $this->decimal($this->spese);
    }

    public function getIvaFormattedAttribute()
    {
        return $this->fmt($this->iva);
    }

    public function getIvaDecimalAttribute()
    {
        return $this->decimal($this->iva);
    }

    public function getPercentIvaAttribute()
    {
        if($this->imponibile)
        {
            return ($this->items()->sum('perc_iva')) / $this->items()->count() . '%';
            //return round(($this->total/$this->imponibile)*100)-100 . "%";
        }
        return 0;
    }
    public function getPercentIvaIntAttribute()
    {
        if($this->imponibile)
        {
            return ($this->items()->sum('perc_iva')) / $this->items()->count();
            //return round(($this->total/$this->imponibile)*100)-100;
        }
        return 0;
    }

    public function getTipoPagamentoAttribute()
    {
        if($this->pagamento == 'RB**')
        {
            return config('invoice.payment_types')['RBFM'];
        }
        if(isset(config('invoice.payment_types')[$this->pagamento])){
        	return config('invoice.payment_types')[$this->pagamento];
        } else {
        	return null;
        }
        
    }

    public function getTipoFormattedAttribute()
    {
        return config('invoice.types')[$this->tipo];
    }

    public function getSaldatoFormattedAttribute()
    {
        if($this->saldato)
        {
            return '<i class="fa fa-check text-success"></i>';
        }
        return '<i class="fa fa-times text-danger"></i>';
    }

    public function getStatusFormattedAttribute()
    {
        $html = '<small class="label p-1 ';

        if($this->data->format('Y') <= 2019)
        {
            return '';
        }

        if($this->status == 0)
        {
            $html .= 'bg-info';
        }
        elseif($this->status == 7 || $this->status == 8 || $this->status == 3 )
        {
            $html .= 'bg-success';
        }
        elseif($this->status == 1 || $this->status == 10)
        {
            $html .= 'bg-warning';
        }
        else{
            $html .= 'bg-danger';
        }
        $html .= '">';
        $html .= ($this->status == 0) ? 'Da inviare' : config('fe.status')[$this->status];
        $html .= '</small>';
        return $html;
    }

    public function getIsConsegnataAttribute()
    {
        if(config('core.modules')['fe'])
        {
            if($this->status == 0)
            {
                return false;
            }
            if(config('fe.status')[$this->status] == 'Non consegnata')
            {
                return true;
            }
            if(config('fe.status')[$this->status] == 'Consegnata')
            {
                return true;
            }
            if(config('fe.status')[$this->status] == 'Inviata')
            {
                return true;
            }
            if(config('fe.status')[$this->status] == 'Presa in carico')
            {
                return true;
            }
        }
        return false;
    }

    public function getXmlAttribute()
    {
        if(config('core.modules')['fe'])
        {
            if($this->media()->xml()->exists())
            {
                return asset('storage/fe/inviate/'.$this->media()->xml()->first()->filename);
            }
        }
        return false;
    }

    public function getRealXmlAttribute()
    {
        if($this->media()->xml()->exists())
        {
            return storage_path('app/public/fe/inviate/'.$this->media()->xml()->first()->filename);
        }
        return false;
    }

    public function getPdfAttribute()
    {
        if($this->media()->pdf()->exists())
        {
            return asset('storage/fe/pdf/inviate/'.$this->media()->pdf()->first()->filename);
        }
        return false;
    }

    public function getHasItemDefaultAttribute()
    {
        if($this->items()->exists())
        {
            if($this->items()->where('product_id',  Product::default())->exists())
            {
                return true;
            }
            return false;
        }
        return false;
    }

    public function getOfficialNameAttribute()
    {
        $first = 'FPR';
        if($this->tipo_doc != 'Pr')
        {
            $first = 'FPA';
        }
        return $first.' '.$this->numero.'/'.$this->data->format('y');

    }

    public function getCompanyOfficialNameAttribute()
    {
        $first = 'FPR';
        if($this->tipo_doc != 'Pr')
        {
            $first = 'FPA';
        }
        return $this->company != null ? $this->company->rag_soc.' -|- '.$first.' '.$this->numero.'/'.$this->data->format('y') : '';

    }

    public function getBranchNameAttribute()
    {
        
        return \Areaseb\Core\Models\Branch::where('id', $this->branch_id)->first()->nome;

    }


//SCOPES & FILTERS

    public function scopeEntrate($query)
    {
        $query = $query->where('numero', '!=', '')->where('numero', '!=', 0)->whereNotNull('numero')->where('tipo', '!=', 'A')->where('aperta', 0);
    }

    public function scopeFatture($query)
    {
        $query = $query->where('numero', '!=', '')->where('numero', '!=', 0)->whereNotNull('numero')->where('tipo', 'F')->where('aperta', 0);
    }

    public function scopeRicevute($query)
    {
        $query = $query->where('numero', '!=', '')->where('numero', '!=', 0)->whereNotNull('numero')->where('tipo', 'R')->where('aperta', 0);
    }

    public function scopeAutofatture($query)
    {
        $query = $query->where('numero', '!=', '')->where('numero', '!=', 0)->whereNotNull('numero')->where('tipo', 'U')->where('aperta', 0);
    }

    public function scopeNotediaccredito($query)
    {
        $query = $query->where('numero', '!=', '')->where('numero', '!=', 0)->whereNotNull('numero')->where('tipo', 'A')->where('aperta', 0);
    }

    public function scopeTipo($query, $value)
    {
        $query = $query->where('tipo', $value)->where('aperta', 0);
    }

    public function scopeAnno($query, $value)
    {
        $query = $query->whereYear('data', $value)->where('aperta', 0);
    }

    public function scopeMese($query, $value)
    {
        $anno = $value['year'];
        $mese = $value['month'];
        $query = $query->whereYear('data', $anno)->whereMonth('data', $mese)->where('aperta', 0);
    }

    public function scopeSaldate($query)
    {
        $query = $query->where('saldato', true)->where('aperta', 0);
    }

    public function scopeUnpaid($query)
    {
        $query = $query->where('saldato', false)->where('aperta', 0);
    }

    public function scopeConsegnate($query)
    {
        if(config('core.modules')['fe'])
        {
            $query = $query->where('status', '!=', 2)->where('aperta', 0);
        }
        $query = $query;
    }


    public static function filter($data)
    {
        $query = self::with('company');

		if($data->get('numero'))
        {

            if(!is_null($data->numero))
            {
                $query = $query->where('numero', $data->numero);
            } else 
            {
            	$query = $query;
            }

        }
        
        if($data->get('tipo_pag'))
        {
            if(!is_null($data->tipo_pag))
            {
                $query = $query->where('tipo_saldo', $data->tipo_pag);
            } else 
            {
            	$query = $query;
            }

        }
        
        if($data->get('tipo'))
        {

            if($data['tipo'] != 'F-A')
            {
                $query = $query->tipo( $data['tipo'] );
            } else {
            	$query = $query->whereIn('tipo', ['F', 'A']);
            }

        }

        if($data->has('saldato'))
        {
            if(!is_null($data->saldato))
            {
                $query = $query->where('saldato', $data->saldato);
            }
            else
            {
                $query = $query;
            }
        }

        if($data->has('anno') && !is_null($data->anno))
        {
        	$query = $query->whereYear('data', $data->anno);            
        } 
        elseif($data->has('anno') && is_null($data->anno)) 
        {        	
        	if(!is_null($data->range)) {
        	
	        	$range = explode(' - ', $data->range);
	        	list($g, $m, $a) = explode("/", $range[0]);
	        	
	        	if($a == date('Y')){
	        		$query = $query->whereYear('data', date('Y'));
	        	} 
	        }
        } else {
        	$query = $query->whereYear('data', date('Y'));
        }

        if($data->has('mese'))
        {
            if(!is_null($data->mese))
            {
                $query = $query->whereMonth('data', $data->mese);
            }
            else
            {
                $query = $query;
            }
        }

		if($data->has('contact'))
        {
            if(!is_null($data->contact))
            {
                $query = $query->where('contact_id', $data->contact);
            }
            else
            {
                $query = $query;
            }
        }

        if($data->has('company'))
        {
            if(!is_null($data->company))
            {
                $query = $query->where('company_id', $data->company);
            }
            else
            {
                $query = $query;
            }
        }
        
        if($data->has('cc'))
        {
            if(!is_null($data->cc))
            {
                $query = $query->where('branch_id', $data->cc);
            }
            else
            {
                $query = $query;
            }
        }

        if($data->get('range') && is_null($data->anno) && is_null($data->mese))
        {
            $range = explode(' - ', $data->range);
            $da = Carbon::createFromFormat('d/m/Y', $range[0])->format('Y-m-d');
            $a =  Carbon::createFromFormat('d/m/Y', $range[1])->format('Y-m-d');

            $query = $query->whereBetween( 'data', [$da, $a] );
        }


        if(!is_null($data->get('exemption_id')))
        {
            if(intval($data->get('exemption_id')) === 1)
            {
                $query = $query->whereHas('items', function($q) {
                    $q->whereNotNull('exemption_id');
                });
            }
            elseif(intval($data->get('exemption_id')) === 2)
            {
            }
            else
            {
                $query = $query->whereHas('items', function($q) {
                    $q->whereNull('exemption_id');
                });
            }
        }

        $query = $query->where('aperta', 0);
        
        if($data->get('sort'))
        {
            $arr = explode('|', $data->sort);
            $field = $arr[0];
            $value = $arr[1];
            $query = $query->orderBy($field, $value);
        }

        return $query;
    }

    /**
     * per riepilogo FE, group all item by exemption, summation of imponibile e iva, plus adding natura e riferimento
     * @return [obj]
     */
    public function getItemsGroupedByExAttribute()
    {
        $results = $this->items()->groupBy('exemption_id')->get();
        $exIds = [];
        foreach($results as $result)
        {
            $exIds[] = $result->exemption_id;
        }

        $group = [];

        if(count($exIds) == 1 && is_null($exIds[0]))
        {
            $resultIva = $this->items()->groupBy('perc_iva')->get();
            $ivaIds = [];
            foreach($resultIva as $i)
            {
                $ivaIds[] = $i->perc_iva;
            }

            foreach($ivaIds as $key => $perc_iva)
            {
                $arr = [];$arr['imponibile'] = 0;$arr['iva'] = 0;
                foreach(Item::where('invoice_id', $this->id)->where('perc_iva', $perc_iva)->get() as $item)
                {
                    $arr['exemption_id'] = $item->exemption_id;
                    $arr['perc_iva'] = $item->perc_iva;
                    $arr['imponibile'] += $item->totale_riga;
                    $arr['iva'] += $item->iva;
                    $arr['esigibilita_iva'] = 'I';

                }
                $group[] = (object)$arr;
            }
            return (object)$group;
        }



        foreach($exIds as $key => $exemption_id)
        {
            $arr = [];$arr['imponibile'] = 0;$arr['iva'] = 0;
            foreach(Item::where('invoice_id', $this->id)->where('exemption_id', $exemption_id)->get() as $item)
            {
                $arr['exemption_id'] = $exemption_id;
                $arr['perc_iva'] = $item->perc_iva;
                $arr['imponibile'] += $item->totale_riga;
                $arr['iva'] += $item->iva;
                if(is_null($item->exemption_id))
                {
                    $arr['esigibilita_iva'] = 'I';
                }
                else
                {
                    $arr['natura'] = $item->exemption->codice;
                    $arr['riferimento_normativo'] = $item->exemption->nome;
                }
            }
            $group[] = (object)$arr;
        }
        return (object)$group;
    }

    /*
     * grupping items by iva for invoice
     * @return [$arr] [description]
     */
    public function getItemsGroupedByPercIvaAttribute()
    {
        $results = [];
        $perc_iva = $this->items()->pluck('perc_iva')->toArray();
        $arr = array_unique($perc_iva);
        sort($arr);
        foreach($arr as $p)
        {
            $imponibile = 0;
            $iva = 0;
            foreach($this->items()->where('perc_iva', $p)->get() as $item)
            {
                $imponibile += $item->totale_riga;
                $iva += $item->iva;
            }
            $results[$p]['imponibile'] = $this->fmt($imponibile);
            $results[$p]['iva'] = $this->fmt($iva);
        }
        return $results;
    }

    public function getItemsGroupedByEsenzioneAttribute()
    {
        $results = [];
        $perc_iva = $this->items()->pluck('exemption_id')->toArray();
        $arr = array_unique($perc_iva);
        sort($arr);
        foreach($arr as $p)
        {
            $imponibile = 0;
            $iva = 0;
            foreach($this->items()->where('exemption_id', $p)->get() as $item)
            {
                $imponibile += $item->totale_riga;
                $iva += $item->iva;
            }
            $results[$p]['imponibile'] = $this->fmt($imponibile);
            $results[$p]['iva'] = $this->fmt($iva);
            if(Exemption::where('id', $p)->exists())
            {
                $results[$p]['exemption'] = Exemption::find($p)->nome;
                $results[$p]['val'] = Exemption::find($p)->perc;
            }
            else
            {
                $results[$p]['exemption'] = '';
                $results[$p]['val'] = 22;
            }
        }
        return $results;
    }

    public static function inScadenzaPrev($days)
    {
        return self::where('data_scadenza', '>=', Carbon::today()->subDays($days))
            ->where('saldato', false)
            ->where('aperta', 0)
            ->orderBy('data_scadenza', 'DESC')
            ->get();
    }







}
