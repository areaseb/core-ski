<?php

namespace Areaseb\Core\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InvoicesExport implements FromView, ShouldAutoSize
{

    public $invoices;

    public function __construct($invoices)
    {
        $this->invoices = $invoices;
    }

    public function view(): View
    {
    	$daSaldare = (clone $this->invoices)->where('saldato', 0)->count();
        $invoices = (clone $this->invoices)->paginate(50);
        
        $tot_invoices = (clone $this->invoices)->get();
        $esenzioni = array();
        $e = array();
        $ritenuta = 0;
        foreach($tot_invoices as $inv){
        	$ritenuta += $inv->ritenuta;
        	foreach($inv->items as $item){
        		if(!in_array($item->exemption_id, $e)){
        			$e[] = $item->exemption_id;
        			$esenzioni[$item->exemption->nome]['imponibile'] = $item->qta * $item->importo * ((100 - $item->sconto) / 100);
        			$esenzioni[$item->exemption->nome]['iva'] = $item->iva;
        		} else{
        			$esenzioni[$item->exemption->nome]['imponibile'] += $item->qta * $item->importo * ((100 - $item->sconto) / 100);
        			$esenzioni[$item->exemption->nome]['iva'] += $item->iva;
        		}
        	}
        }
        
        request()->merge([
		    'tipo' => 'U',
		]);
        $autofatture = \Areaseb\Core\Models\Invoice::filter(request())->where('numero', '!=', '')->where('aperta', 0)->orderBy('data', 'DESC')->orderBy('numero', 'DESC')->get();
        $esenzioni_autofatture = array();
        $e = array();
        foreach($autofatture as $auto){
        	foreach($auto->items as $item){
        		if(!in_array($item->exemption_id, $e)){
        			$e[] = $item->exemption_id;
        			$esenzioni_autofatture[$item->exemption->nome]['imponibile'] = $item->qta * $item->importo * ((100 - $item->sconto) / 100);
        			$esenzioni_autofatture[$item->exemption->nome]['iva'] = $item->iva;
        		} else{
        			$esenzioni_autofatture[$item->exemption->nome]['imponibile'] += $item->qta * $item->importo * ((100 - $item->sconto) / 100);
        			$esenzioni_autofatture[$item->exemption->nome]['iva'] += $item->iva;
        		}
        	}
        }
        
        return view('areaseb::core.accounting.invoices.excel', compact('invoices', 'esenzioni', 'esenzioni_autofatture', 'ritenuta', 'daSaldare'));
    }

}
