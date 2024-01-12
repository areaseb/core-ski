<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Category, Contact, Company, Primitive, Product, Invoice, Item, Stat, Ora, Master};
use Areaseb\Core\Exports\ClientAnnualRevenueExport as Export;

class StatController extends Controller
{
//stats/aziende
    public function companies()
    {
        $year = date('Y')-3;
        $query = Company::has('invoices')
                        ->with('invoices')
                        ->whereHas('invoices', function($query) use($year) {
                            $query->whereYear('data', '>=', $year);
                        })
                         ->orderBy('rag_soc', 'ASC');
        $companiesId = (clone $query)->pluck('rag_soc', 'id')->toArray();

        if(request()->get('ids'))
        {
            $arr = explode('-', request('ids'));
            $companies = Company::whereIn('id', $arr)->get();
            $companiesIdSelected = $arr;
            $annualStats = Stat::annualStatInvoicesQuery(Company::whereIn('id', $arr));
        }
        else
        {
            $companies = $query->get();
            $companiesIdSelected = [];
            $annualStats = Stat::annualStatInvoices();
        }
// dd($annualStats, intval($annualStats[(date('Y')-3)]), $annualStats[(date('Y')-3)]);


        return view('areaseb::core.accounting.stats.companies', compact('companies', 'annualStats','companiesId', 'companiesIdSelected'));
    }

//stats/categorie
    public function categories()
    {
        $data = []; $graphData = []; $labels = ''; $totali = ''; $fatturato = ''; $tot_fatt = 0;
        
        if(request()->get('year') && request()->get('year') != 'tutti')
        {
        	$year = intval(request()->get('year'));
        }
        else
        {
        	$year = intval(date('Y'));
        }
        
        $invoiceIds = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->where('aperta', 0)->whereIn('tipo', ['F','R']);
        $accreditoIds = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->where('aperta', 0)->where('tipo', 'A');  
        
        if(request()->get('data_in')){
        	if(request()->get('data_out')){
        		$month_out = request()->get('data_out');
        	} else {
        		$month_out = request()->get('data_in');
        	}
        	$invoiceIds = $invoiceIds->where('data', '>=', request()->get('data_in'))->where('data', '<=', $month_out);
        	$accreditoIds = $accreditoIds->where('data', '>=', request()->get('data_in'))->where('data', '<=', $month_out);
        }  
        
        $invoiceIds = $invoiceIds->pluck('id')->toArray();
        $accreditoIds = $accreditoIds->pluck('id')->toArray();
       
       	$productIds = Item::whereIn('invoice_id', $invoiceIds)->pluck('product_id')->toArray();
        $productIds = array_unique($productIds);
        
        
        foreach(Stat::groupProductsByCategoryFromProducts($productIds) as $cat => $products)
        {
        	$tot = 0; $fatt = 0;
	        $category = Category::find($cat);
	        
        	foreach($products as $product)
        	{        
                $items = Item::whereIn('invoice_id', $invoiceIds)->where('product_id', $product)->get();
                
                foreach($items as $item){
                	$tot += intval($item->qta);
                	$perc_ritenuta = Invoice::where('id', $item->invoice_id)->first()->perc_ritenuta;
                	if($perc_ritenuta > 0){
                		$fatt += ((($item->iva + $item->importo) * ((100 - $perc_ritenuta) / 100)) * $item->qta) * ((100 - $item->sconto) / 100);
                	} else {
                		$fatt += ((($item->iva + $item->importo)) * $item->qta) * ((100 - $item->sconto) / 100);
                	}
	                
                }
                       
                $items = Item::whereIn('invoice_id', $accreditoIds)->where('product_id', $product)->get();
                
                foreach($items as $item){
                	$tot -= intval($item->qta);
	                $perc_ritenuta = Invoice::where('id', $item->invoice_id)->first()->perc_ritenuta;
                	if($perc_ritenuta > 0){
                		$fatt -= ((($item->iva + $item->importo) * ((100 - $perc_ritenuta) / 100)) * $item->qta) * ((100 - $item->sconto) / 100);
                	} else {
                		$fatt -= ((($item->iva + $item->importo)) * $item->qta) * ((100 - $item->sconto) / 100);
                	}
                }
	        }
		    
            if($tot < 0){
            	$fatt = -1 * $fatt;
            }
            
		    $tot_fatt += $fatt;
            
            $totali .= '"'.$tot.'",';
            $fatturato .= '"'.$fatt.'",';
            if($category){
            	$labels .= '"'.$category->name_it.'",';
            }           
            $data[$cat]['totali'] = $tot;
            $data[$cat]['fatturato'] = Primitive::NF($fatt);
        	
        	
        }
		
		ksort($data);

        $graphData['labels'] = substr($labels, 0, -1);
        $graphData['fatturato'] = substr($fatturato, 0, -1);
        $graphData['totali'] = substr($totali, 0, -1);
        $tot_fatt = Primitive::NF($tot_fatt);

        $graphData['labels'] = substr($labels, 0, -1);
        $graphData['fatturato'] = substr($fatturato, 0, -1);
        $graphData['totali'] = substr($totali, 0, -1);

        return view('areaseb::core.accounting.stats.categories', compact('graphData', 'data', 'tot_fatt'));
    }


//stats/categorie/{id}
    public function category($id)
    {
        $category = Category::find($id);

        $data = []; $graphData = []; $labels = ''; $totali = ''; $fatturato = ''; $tot_fatt = 0;
        
        if(request()->get('year') && request()->get('year') != 'tutti')
        {
        	$year = intval(request()->get('year'));
        }
        else
        {
        	$year = intval(date('Y'));
        }
        
        $invoiceIds = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->where('aperta', 0)->whereIn('tipo', ['F','R']);
        $accreditoIds = Invoice::whereBetween('data', [$year.'-06-01', ($year+1).'-05-31'])->where('aperta', 0)->where('tipo', 'A');   
        
        if(request()->get('data_in')){
        	if(request()->get('data_out')){
        		$month_out = request()->get('data_out');
        	} else {
        		$month_out = request()->get('data_in');
        	}
        	$invoiceIds = $invoiceIds->where('data', '>=', request()->get('data_in'))->where('data', '<=', $month_out);
        	$accreditoIds = $accreditoIds->where('data', '>=', request()->get('data_in'))->where('data', '<=', $month_out);
        }  
        
        $invoiceIds = $invoiceIds->pluck('id')->toArray();
        $accreditoIds = $accreditoIds->pluck('id')->toArray();
       
        if($category){
        	$prodIds = \DB::table('categorizables')->where('category_id', $id)->where('categorizable_type', 'Areaseb\Core\Models\Product')->pluck('categorizable_id')->toArray();
        }
        else {
        	$pIds = \DB::table('categorizables')->distinct('category_id')->where('categorizable_type', 'Areaseb\Core\Models\Product')->pluck('categorizable_id')->toArray();
        	$prodIds = Item::whereIn('invoice_id', $invoiceIds)->whereNotIn('product_id', $pIds)->distinct()->pluck('product_id')->toArray();
        }
        
        for($i = 1; $i <= 12; $i++){
        	$trend_tot[$i] = 0;
        	$trend_fatt[$i] = 0;
        }
                  
        foreach($prodIds as $product)
    	{       
    		$tot = 0; $fatt = 0;
    		
            $items = Item::whereIn('invoice_id', $invoiceIds)->where('product_id', $product)->get();
            
            foreach($items as $item){
            	$trend_tot[intval($item->invoice->data->format('m'))] += intval($item->qta);
            	$trend_fatt[intval($item->invoice->data->format('m'))] += (($item->iva + $item->importo) * $item->qta) * ((100 - $item->sconto) / 100);
            	
            	$tot += intval($item->qta);
            	$perc_ritenuta = Invoice::where('id', $item->invoice_id)->first()->perc_ritenuta;
            	if($perc_ritenuta > 0){
            		$fatt += ((($item->iva + $item->importo) * ((100 - $perc_ritenuta) / 100)) * $item->qta) * ((100 - $item->sconto) / 100);
            	} else {
            		$fatt += ((($item->iva + $item->importo)) * $item->qta) * ((100 - $item->sconto) / 100);
            	}
            }
               
            $items = Item::whereIn('invoice_id', $accreditoIds)->where('product_id', $product)->get();
            
            foreach($items as $item){
            	$trend_tot[intval($item->invoice->data->format('m'))] -= intval($item->qta);
            	$trend_fatt[intval($item->invoice->data->format('m'))] -= (($item->iva + $item->importo) * $item->qta) * ((100 - $item->sconto) / 100);
            	
            	$tot -= intval($item->qta);
            	$perc_ritenuta = Invoice::where('id', $item->invoice_id)->first()->perc_ritenuta;
            	if($perc_ritenuta > 0){
            		$fatt -= ((($item->iva + $item->importo) * ((100 - $perc_ritenuta) / 100)) * $item->qta) * ((100 - $item->sconto) / 100);
            	} else {
            		$fatt -= ((($item->iva + $item->importo)) * $item->qta) * ((100 - $item->sconto) / 100);
            	}
            }
            		    
            if($tot < 0){
            	$fatt = -1 * $fatt;
            }
            
            $tot_fatt += $fatt;
            $totali .= '"'.$tot.'",';
	        $fatturato .= '"'.$fatt.'",';
	        $labels .= '"'.str_replace('"', '\'\'', Product::find($product)->nome_it).'",';
	        $data[$product]['totali'] = $tot;
	        $data[$product]['fatturato'] = Primitive::NF($fatt); 
        }
	    
	    ksort($data);
	    
        $graphData['labels'] = substr($labels, 0, -1);
        $graphData['fatturato'] = substr($fatturato, 0, -1);
        $graphData['totali'] = substr($totali, 0, -1);
        $tot_fatt = Primitive::NF($tot_fatt);
        
        

        $filteredInvoiceIds = Item::whereIn('product_id', $prodIds)->whereIn('invoice_id', $invoiceIds)->pluck('invoice_id');
        $companyIds = Invoice::whereIn('id', $filteredInvoiceIds)->pluck('company_id');
        $sectorIds = Company::whereIn('id', $companyIds)->pluck('sector_id');
        $sectors = [];
        foreach($sectorIds as $sector_id)
        {
            if(intval($sector_id))
            {
                if(!isset($sectors[$sector_id]))
                {
                    $sectors[$sector_id] = 1;
                }
                else
                {
                    $sectors[$sector_id] += 1;
                }
            }
        }

        $totalAmountSectors = [];
        foreach($sectors as $sector_id => $count)
        {
            $companyIds = Company::where('sector_id', $sector_id)->pluck('id');
            $inv = Invoice::whereIn('id', $filteredInvoiceIds)->whereIn('company_id', $companyIds)->get();
            foreach($inv as $i){
            	$perc_ritenuta = $i->perc_ritenuta;
            	if($i->tipo == 'F' || $i->tipo == 'R'){
	            	if($perc_ritenuta > 0){
	            		$totalAmountSectors[$sector_id] += $i->imponibile - $i->ritenuta + $i->iva;
	            	} else {
	            		$totalAmountSectors[$sector_id] += $i->imponibile + $i->iva;
	            	}
	            }
	            if($i->tipo == 'A'){
	            	if($perc_ritenuta > 0){
	            		$totalAmountSectors[$sector_id] -= $i->imponibile - $i->ritenuta + $i->iva;
	            	} else {
	            		$totalAmountSectors[$sector_id] -= $i->imponibile + $i->iva;
	            	}
	            }
            }
            
        }

        $graphData['labels'] = substr($labels, 0, -1);
        $graphData['fatturato'] = substr($fatturato, 0, -1);
        $graphData['totali'] = substr($totali, 0, -1);

        return view('areaseb::core.accounting.stats.category', compact('category', 'graphData', 'data','invoiceIds', 'sectors', 'totalAmountSectors'));
    }
    
//stats/maestri
    public function masters()
    {
        $ore_maestro = []; $fatturato = 0;
        
        if(request()->get('year') && request()->get('year') != 'tutti')
        {
        	$year = intval(request()->get('year'));
        }
        else
        {
        	if(date('m') <= 4){
	    		$year = date('Y') - 1;
	    	} else {
	    		$year = date('Y');
	    	}
        }
           
        $masters = Master::all();	//\DB::table('masters')->select('*')->get();
        if(!auth()->user()->hasRole('super')){
        	$branch_id = auth()->user()->contact->branchContact()->branch_id;
        	$contact_masters = Contact::where('contact_type_id', 3)->pluck('id')->toArray();
        	$masters_id = \DB::table('contact_branch')->whereIn('contact_id', $contact_masters)->where('branch_id', $branch_id)->pluck('contact_id')->toArray();
        	
        	$masters = $masters->whereIn('contact_id', $masters_id);
        }
        
   
        foreach($masters as $master){
        	
        	if(request()->get('data_in')){
	        	if(request()->get('data_out')){
	        		$month_out = request()->get('data_out');
	        	} else {
	        		$month_out = request()->get('data_in');
	        	}
	        	$ore = Ora::where('id_maestro', $master->id)->whereBetween('data', [$year.'-05-01', ($year+1).'-04-30'])->whereBetween('data', [request()->get('data_in'), $month_out])->where('id_cliente', 'not like', 'L_%')->get();
	        } else {
	        	$ore = Ora::where('id_maestro', $master->id)->whereBetween('data', [$year.'-05-01', ($year+1).'-04-30'])->where('id_cliente', 'not like', 'L_%')->get();
	        }
        	
        	
        	foreach($ore as $ora){
        		list($h_in, $m_in) = explode(":", $ora->ora_in);
	            list($h_out, $m_out) = explode(":", $ora->ora_out);
	            $dif_ore = $h_out - $h_in;
	            $dif_minuti = $m_out - $m_in;
	            $dif_minuti_ok = ($dif_minuti * 100) / 60;
	            if($dif_minuti_ok < 0){
	                $dif_ore--;
	            }
	            $dif_minuti_ok = abs($dif_minuti_ok);
	            $diff = "$dif_ore.$dif_minuti_ok";
	            
	            if(isset($ore_maestro[$master->id])){
	            	$ore_maestro[$master->id] += $diff;
	            } else {
	            	$ore_maestro[$master->id] = $diff;
	            }
	            
        	}
        	
        	
        }
        
        if(request()->get('data_in')){
        	if(request()->get('data_out')){
        		$month_out = request()->get('data_out');
        	} else {
        		$month_out = request()->get('data_in');
        	}
        	$inv = Invoice::where('aperta', 0)->whereBetween('data', [$year.'-05-01', ($year+1).'-04-30'])->whereBetween('data', [request()->get('data_in'), $month_out])->get();
        } else {
        	$inv = Invoice::where('aperta', 0)->whereBetween('data', [$year.'-05-01', ($year+1).'-04-30'])->get();
        }
        
        foreach($inv as $i){
        	$perc_ritenuta = $i->perc_ritenuta;
        	if($i->tipo == 'F' || $i->tipo == 'R'){
            	if($perc_ritenuta > 0){
            		$fatturato += $i->imponibile - $i->ritenuta + $i->iva;
            	} else {
            		$fatturato += $i->imponibile + $i->iva;
            	}
            }
            if($i->tipo == 'A'){
            	if($perc_ritenuta > 0){
            		$fatturato -= $i->imponibile - $i->ritenuta + $i->iva;
            	} else {
            		$fatturato -= $i->imponibile + $i->iva;
            	}
            }
        }
        
        return view('areaseb::core.accounting.stats.masters', compact('masters', 'ore_maestro', 'fatturato'));
    }  
    
//stats/maestro
    public function master($id)
    {
    	$maestro = Master::find($id);
    	
    	if(request()->get('year') && request()->get('year') != 'tutti')
        {
        	$year = intval(request()->get('year'));
        	$to = ($year+1).'-04-30';
        }
        else
        {
        	if(date('m') <= 4){
	    		$year = date('Y') - 1;
	    	} else {
	    		$year = date('Y');
	    	}
	    	$to = date('Y-m-d');
        }
    	
    	$from = $year.'-05-01';
    	
    	
    	if(request()->get('data_in')){
        	if(request()->get('data_out')){
        		$month_out = request()->get('data_out');
        	} else {
        		$month_out = request()->get('data_in');
        	}
        	$ore = Ora::where('id_maestro', $id)->whereBetween('data', [$from, $to])->whereBetween('data', [request()->get('data_in'), $month_out])->where('id_cliente', 'not like', 'L_%')->orderBy('data')->get();
        } else {
        	$ore = Ora::where('id_maestro', $id)->whereBetween('data', [$from, $to])->where('id_cliente', 'not like', 'L_%')->orderBy('data')->get();
        }
    	
    	//$ore = Ora::where('data', '>=', $from)->where('data', '<=', $to)->where('id_maestro', $id)->where('id_cliente', 'not like', 'L_%')->orderBy('data')->get();
    	
    	return view('areaseb::core.accounting.stats.master', compact('ore', 'maestro'));
    }      

//stats/balance
    public function balance()
    {
        $graphData = Stat::monthlyAnnualGraph();
        return view('areaseb::core.accounting.stats.balance', compact('graphData'));
    }

//stats/exports
    public function export()
    {
        $year = date('Y')-3;
        $query = Company::has('invoices')
                        ->with('invoices')
                        ->whereHas('invoices', function($query) use($year) {
                            $query->whereYear('data', '>=', $year);
                        })->orderBy('rag_soc', 'ASC');

        if(request()->get('ids'))
        {
            $arr = explode('-', request('ids'));
            $companies = Company::whereIn('id', $arr)->get();
        }
        else
        {
            $companies = $query->get();
        }
        return \Excel::download(new Export($companies), 'stats-clienti.xlsx');
    }


//stats/expenses/{category}
    public function expense(Category $category)
    {

        $query = Cost::query();

        $query = $query->whereIn('expense_id', $category->expenses()->pluck('id'));

        $companies = ( clone $query )->distinct('company_id')->pluck('company_id')->toArray();
        $groupedCosts = [];
        foreach ($companies as $company_id) {
            $groupedCosts[$company_id] = ( clone $query )->where('company_id', $company_id)->get();
        }

        return view('areaseb::core.accounting.stats.expenses', compact('groupedCosts', 'category'));
    }



}
