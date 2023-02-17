<?php

namespace Areaseb\Core\Models;

use \Carbon\Carbon;
use Areaseb\Core\Models\{Calendar, Category, Cost, Event, Expense};
use Illuminate\Support\Facades\Cache;
//use App\Classes\Fe\Actions\UploadIn;

class Cost extends Primitive
{
    protected $dates = ['data', 'data_scadenza', 'data_ricezione'];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payments()
    {
        return $this->hasMany(CostPayment::class);
    }


//SETTER
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = is_null($value) ? null : Carbon::createFromFormat('d/m/Y', $value);
    }

    public function setDataScadenzaAttribute($value)
    {
        $this->attributes['data_scadenza'] = is_null($value) ? null : Carbon::createFromFormat('d/m/Y', $value);
    }

    public function setDataRicezioneAttribute($value)
    {
        $this->attributes['data_ricezione'] = is_null($value) ? null : Carbon::createFromFormat('d/m/Y', $value);
    }

    public function setDataSaldoAttribute($value)
    {
        $this->attributes['data_saldo'] = is_null($value) ? null : Carbon::createFromFormat('d/m/Y', $value);
    }

//GETTER

    public function getPaymentStatusAttribute()
    {
        if($this->saldato)
        {
            return 0;
        }
        if(!intval($this->totale))
        {
            return 0;
        }
        if( !$this->payments()->sum('amount'))
        {
            return 0;
        }
        return round($this->payments()->sum('amount')/$this->totale*100) ;
    }

    public function getPaymentColorAttribute()
    {
        return $this->color( $this->payment_status );
    }

    public function getNomeAttribute()
    {
        if($this->numero)
        {
            return $this->numero;
        }
        return $this->expense->nome . ' ' . $this->data->format('m');
    }

    public function getImponibileFormattedAttribute()
    {
        return $this->fmt($this->imponibile);
    }

    public function getTotaleFormattedAttribute()
    {
        return $this->fmt($this->totale);
    }

    public function getXmlAttribute()
    {
        if($this->media()->xml()->exists())
        {
            return asset('storage/fe/ricevute/'.$this->media()->xml()->first()->filename);
        }
        return false;
    }

    public function getRealXmlAttribute()
    {
        if($this->media()->xml()->exists())
        {
            return asset('storage/fe/ricevute/'.$this->media()->xml()->first()->filename);
        }
        return false;
    }

    public function getPdfAttribute()
    {
        if($this->media()->pdf()->exists())
        {
            return asset('storage/fe/pdf/ricevute/'.$this->media()->pdf()->first()->filename);
        }
        return false;
    }

    public function getIsMyCompanyAttribute()
    {
        if( strtolower($this->company->rag_soc) ==  strtolower(Setting::base()->rag_soc) )
        {
            return true;
        }
        return false;
    }


//SCOPES
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


    public static function firstYear()
    {

        $firstYear = Cache::remember('firstYear', 60*24*7*56, function () {
            $oldest = self::orderBy('anno', 'ASC')->first();
            if(is_null($oldest))
            {
                return date('Y');
            }

            return $oldest->anno;
        });
        return $firstYear;
    }

    public static function yearsArray()
    {
        $arr = [];
        foreach(range(date('Y'), self::firstYear()) as $r)
        {
            $arr[$r] = $r;
        }
        return $arr;
    }



    public static function inScadenzaPrev($days)
    {
        return self::where('data_scadenza', '>=', Carbon::today()->subDays($days))
            ->where('saldato', false)
            ->orderBy('data_scadenza', 'DESC')
            ->get();
    }

    public static function filter($data)
    {
        $query = Cost::query();

        if($data->get('company_id'))
        {
            $query = $query->where('company_id', $data->get('company_id'));
        }

        if($data->get('category_id'))
        {
            $query = $query->whereIn('expense_id', Category::find($data->get('category_id'))->expenses()->pluck('id')->toArray());
        }

        if($data->get('anno'))
        {
            $query = $query->whereYear('data', $data->get('anno'));
            if($data->get('mese'))
            {
                $query = $query->whereMonth('data', '=', $data->get('mese'));
            }
        }

        if($data->get('id'))
        {
            $query = Cost::where('id', $data->get('id'));
        }

        if($data->get('q'))
        {
            $query = Cost::where('numero', 'like', '%'.$data['q'].'%');
        }


        if($data->get('sort'))
        {
            $arr = explode('|', $data->get('sort'));
            $query = Cost::orderBy($arr[0], $arr[1]);
        }
        else
        {
            $query = $query->orderBy('data', 'DESC');
        }

        return $query;
    }

    public static function storeCostInCalendar($cost)
    {

        $link = '<br><a href="'.$cost->url.'/edit" class="btn btn-primary btn-sm"><i class="fas fa-link"></i></a>';

        Event::create([
            'user_id' => 1,
            'calendar_id' => Calendar::Scadenze(),
            'title' => 'Pagare Acquisto €'. number_format($cost->totale, 2, ',', '.'),
            'summary' => 'Pagare fattura N. ' .$cost->numero . ' ricevuta il '. $cost->data_ricezione->format('d/m/Y') . ' a conto di '.$cost->company->rag_soc.$link,
            'starts_at' => $cost->data_scadenza->format('Y-m-d') . ' 09:00:00',
            'ends_at' => $cost->data_scadenza->format('Y-m-d') . ' 10:00:00',
            'backgroundColor' => ($cost->saldato) ? '#28a745' : '#dc3545',
            'eventable_id' => $cost->id,
            'eventable_type' => get_class($cost),
            'done' => ($cost->saldato) ? 1 : 0
        ]);
        return true;
    }

    public static function updateCostInCalendar($cost)
    {
        $link = '<br><a href="'.$cost->url.'/edit" class="btn btn-primary btn-sm"><i class="fas fa-link"></i></a>';
        $event = Event::where('eventable_id', $cost->id)->where('eventable_type', get_class($cost))->first();
        if($event)
        {
            $event->starts_at = $cost->data_scadenza->format('Y-m-d') . ' 09:00:00';
            $event->ends_at = $cost->data_scadenza->format('Y-m-d') . ' 10:00:00';
            $event->backgroundColor = ($cost->saldato) ? '#28a745' : '#dc3545';
            $event->done = ($cost->saldato) ? 1 : 0;
            $event->summary = 'Pagare fattura N. ' .$cost->numero . ' ricevuta il '. $cost->data_ricezione->format('d/m/Y') . ' a conto di '.$cost->company->rag_soc.$link;
            $event->save();
        }

        return true;
    }

    public static function deleteCostFromCalendar($cost)
    {
        $event = Event::where('eventable_id', $cost->id)->where('eventable_type', get_class($cost))->first();
        if($event)
        {
            $event->delete();
        }

        return true;
    }


}
