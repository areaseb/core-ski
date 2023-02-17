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


// setter
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = is_null($value) ? null : Carbon::createFromFormat('d/m/Y', $value);
    }

    public function setDataRegistrazioneAttribute($value)
    {
        $this->attributes['data_registrazione'] = is_null($value) ? null : Carbon::createFromFormat('d/m/Y', $value);
    }

    public function setDdtDataDocAttribute($value)
    {
        $this->attributes['ddt_data_doc'] = is_null($value) ? null : Carbon::createFromFormat('d/m/Y', $value);
    }

    public function setPaDataDocAttribute($value)
    {
        $this->attributes['pa_data_doc'] = is_null($value) ? null : Carbon::createFromFormat('d/m/Y', $value);
    }

    public function setDataSaldoAttribute($value)
    {
        $this->attributes['data_saldo'] = is_null($value) ? null : Carbon::createFromFormat('d/m/Y', $value);
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

    public function getPaymentStatusAttribute()
    {
        if($this->saldato)
        {
            return 0;
        }
        return 100-round(($this->total - $this->payments()->sum('amount'))/$this->total*100) ;
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
        return $this->tipo_formatted . " N." . $this->numero . " / " . $this->data->format('Y');
    }

    public function getTotalAttribute()
    {
        return $this->imponibile + $this->iva;
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
        return $this->fmt($this->imponibile);
    }

    public function getImponibileDecimalAttribute()
    {
        return $this->decimal($this->imponibile);
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
            return round(($this->total/$this->imponibile)*100)-100 . "%";
        }
        return 0;
    }
    public function getPercentIvaIntAttribute()
    {
        if($this->imponibile)
        {
            return round(($this->total/$this->imponibile)*100)-100;
        }
        return 0;
    }

    public function getTipoPagamentoAttribute()
    {
        if($this->pagamento == 'RB**')
        {
            return config('invoice.payment_types')['RBFM'];
        }
        return config('invoice.payment_types')[$this->pagamento];
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
        return $this->company->rag_soc.' -|- '.$first.' '.$this->numero.'/'.$this->data->format('y');

    }


//SCOPES & FILTERS

    public function scopeEntrate($query)
    {
        $query = $query->where('tipo', '!=', 'A');
    }

    public function scopeFatture($query)
    {
        $query = $query->where('tipo', 'F');
    }

    public function scopeRicevute($query)
    {
        $query = $query->where('tipo', 'R');
    }

    public function scopeAutofatture($query)
    {
        $query = $query->where('tipo', 'U');
    }

    public function scopeNotediaccredito($query)
    {
        $query = $query->where('tipo', 'A');
    }

    public function scopeTipo($query, $value)
    {
        $query = $query->where('tipo', $value);
    }

    public function scopeAnno($query, $value)
    {
        $query = $query->whereYear('data', $value);
    }

    public function scopeMese($query, $value)
    {
        $anno = $value['year'];
        $mese = $value['month'];
        $query = $query->whereYear('data', $anno)->whereMonth('data', $mese);
    }

    public function scopeSaldate($query)
    {
        $query = $query->where('saldato', true);
    }

    public function scopeUnpaid($query)
    {
        $query = $query->where('saldato', false);
    }

    public function scopeConsegnate($query)
    {
        if(config('core.modules')['fe'])
        {
            $query = $query->where('status', '!=', 2);
        }
        $query = $query;
    }


    public static function filter($data)
    {
        $query = self::with('company');

        if($data->get('tipo'))
        {

            if($data['tipo'] != 'FA')
            {
                $query = $query->tipo( $data['tipo'] );
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

        if($data->has('anno'))
        {
            if(!is_null($data->anno))
            {
                $query = $query->whereYear('data', $data->anno);
            }
            else
            {
                $query = $query;
            }
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

        if($data->get('range'))
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
            ->orderBy('data_scadenza', 'DESC')
            ->get();
    }







}
