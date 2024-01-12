<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Deals\App\Models\{Deal, DealEvent, DealGenericQuote};
use App\Classes\Accounting\Requests\{EditInvoice, CreateInvoice};
use Areaseb\Core\Models\{Category, Company, Contact, Exemption, Master, Specialization, Invoice, InvoiceNotice, Item, Primitive, Product, Setting, Stat, Sede, Ora, Collettivo, CollettivoAcconti, CollettivoAllievi, Hangout, Branch};
use \Carbon\Carbon;
use Areaseb\Core\Models\Fe\InvoiceToXml;
use Areaseb\Core\Models\Fe\XmlToInvoice;
use Areaseb\Core\Models\Fe\FatturaCheck;
use Areaseb\Core\Mail\Notice;
//use App\Classes\Fe\Actions\UploadOut;

class InvoiceController extends Controller
{

    public function index()
    {
        if(request()->input())
        {
            $query = Invoice::filter(request())->where('numero', '!=', '')->where('aperta', 0)->orderBy('data', 'ASC')->orderBy('numero', 'ASC');
            $totQuery = Stat::TotaleQuery($query);
            $month_stats = null;
            $month_vat_stats = null;
            $graphData = null;
            $listBoxes = Stat::TotaleQueryBoxes($query);
        }
        else
        {
            $query = Invoice::where('data', date('Y-m-d'))->where('numero', '!=', '')->where('aperta', 0)->orderBy('data', 'ASC')->orderBy('numero', 'ASC')->with('company');
            $totQuery = Stat::TotaleQuery($query);
            $month_stats = Stat::TotaleMeseImponibile();
            $graphData = Stat::invoicePageGraph();
            $month_vat_stats = Stat::TotaleMeseVat();
            $listBoxes = Stat::TotaleQueryBoxes($query);
        }
        
        $daSaldare = (clone $query)->where('saldato', 0)->count();
        $invoices = (clone $query)->paginate(100);
        
        $tot_invoices = (clone $query)->get();
        $esenzioni = array();
        $e = array();
        $ritenuta = 0;
        foreach($tot_invoices as $inv){
        	$ritenuta += $inv->ritenuta;
        	foreach($inv->items as $item){
        		if($item->exemption_id){
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
        }
			/*
        request()->merge([
		    'tipo' => 'U',
		]);
		*/
        $autofatture = Invoice::filter(request())->where('numero', '!=', '')->where('aperta', 0)->orderBy('data', 'DESC')->orderBy('numero', 'DESC')->get();
        $esenzioni_autofatture = array();
        $e = array();
        foreach($autofatture as $auto){
        	foreach($auto->items as $item){
        		if($item->exemption_id){
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
        }
        $contacts_query = Contact::orderBy('cognome', 'ASC')->get();
        $contacts = array();
        foreach($contacts_query as $contact){
        	$contacts[$contact->id] = $contact->fullname;
        }
        //dd($esenzioni, $esenzioni_autofatture);
        return view('areaseb::core.accounting.invoices.index', compact('contacts', 'invoices', 'esenzioni', 'esenzioni_autofatture', 'ritenuta', 'month_stats', 'month_vat_stats', 'graphData', 'totQuery', 'daSaldare','listBoxes'));
    }

//insoluti - GET
    public function insoluti()
    {
        if(request()->input())
        {
            $query = Invoice::filter(request())->where('numero', '!=', '')->where('saldato', 0)->where('aperta', 0)->orderBy('data', 'DESC')->orderBy('numero', 'DESC');
            $totQuery = Stat::TotaleQueryInsoluti($query);
            $month_stats = null;
        }
        else
        {
            $query = Invoice::where('numero', '!=', '')->where('saldato', 0)->where('aperta', 0)->orderBy('data', 'DESC')->orderBy('numero', 'DESC')->with('company');
            $totQuery = Stat::TotaleQueryInsoluti($query);
            $month_stats = null;
        }
        $invoices = $query->paginate(50);
        return view('areaseb::core.accounting.invoices.insoluti', compact('invoices', 'totQuery', 'month_stats'));
    }


    public function create()
    {
        $deals = [];
        if(class_exists("Deals\App\Models\Deal"))
        {
            $deals = ['' => ''];
            $dealsC = Deal::whereNull('accepted')->orWhere('accepted', true)->orderBy('created_at', 'DESC')->where('created_at', '>',Carbon::today()->subMonth(4))->get();
            foreach($dealsC as $deal)
            {
                $deals[$deal->id] = $deal->company->rag_soc . " N." . sprintf('%03d', $deal->numero);
            }
        }

        $contacts = ['' => '']+\DB::table('contacts')->select('id', \DB::raw('CONCAT(contacts.nome, " ", contacts.cognome) AS full_name'))->where('contact_type_id', '<', 3)->get()
                            ->pluck('full_name', 'id')->toArray();


        $companies = ['' => '']+Company::where('client_id', '!=', 4)->whereNotNull('piva')->orderBy('rag_soc', 'ASC')->pluck('rag_soc', 'id')->toArray();
        $sedi = Branch::orderBy('nome', 'ASC')->pluck('nome', 'id')->toArray();
        //$sedi = Company::where('client_id', 4)->whereNotNull('piva')->orderBy('rag_soc', 'ASC')->pluck('rag_soc', 'id')->toArray();
        $products = ['' => '']+Product::groupedOpt();
        $exemption_list = Exemption::where('connettore', 'Aruba')->orderBy('codice', 'ASC')->get();
        $exemptions = ['' => ''];
        foreach($exemption_list as $exemp){
        	$exemptions += [$exemp->id => $exemp->codice . ' - ' . $exemp->nome];
        }
        
        $selectedCompany = [];

        if(request('deal') && class_exists('Deals\App\Models\Deal'))
            $selectedCompany = [Deal::findOrFail(request('deal'))->company_id];

        $items = [];
        $invoices = ['' => '']+Invoice::whereDate('data', '>', Carbon::today()->subMonths(1)->format('Y-m-d'))->get()->pluck('company_official_name', 'id')->toArray();
        return view('areaseb::core.accounting.invoices.create', compact('contacts','companies', 'selectedCompany', 'products', 'exemptions', 'items', 'invoices', 'deals', 'sedi'));
    }

    public function store(Request $request)
    {
        $branch_id = null;
        if(isset($request->company_id)){
            $element = \DB::table('company_branch')->where('company_id', $request->company_id)->first();
            if($element != null)
                $branch_id = $element->branch_id;
        }
        elseif(isset($request->contact_id)){
            $element = \DB::table('contact_branch')->where('contact_id', $request->contact_id)->first();
            if($element != null)
                $branch_id = $element->branch_id;
        }
        elseif(isset($request->branch_id)){
            $element = \DB::table('branches')->where('company_id', $request->branch_id)->first();
            if($element != null)
                $branch_id = $element->id;
        }
        else{
            $element = auth()->user()->contact->branchContact();
            if($element != null)
                $branch_id = $element->branch_id;
        }

        $invoice = new Invoice;
        $invoice->tipo_doc = request('tipo_doc');
        $invoice->tipo = request('tipo');
        $invoice->numero = request('numero');
        $invoice->numero_registrazione = request('numero');
        $invoice->data = request('data');
        $invoice->data_registrazione = request('data');
        $invoice->company_id = request('company_id');
        $invoice->contact_id = request('contact_id');
        $invoice->branch_id = $branch_id;

        $invoice->riferimento = request('riferimento');

        $invoice->pagamento = request('pagamento');
        $invoice->tipo_saldo = request('tipo_saldo');
        $invoice->data_saldo = request('data_saldo');
        $invoice->data_scadenza = $this->getDataScadenza($request);
        $invoice->aperta = 0;

        $invoice->spese = request('spese') ?? 0.00;
        $invoice->perc_ritenuta = request('perc_ritenuta') ?? 0.00;
        $invoice->rate = request('rate');
        $invoice->saldato = request('data_saldo') ? 1 : 0;
        if(request('bollo_a') != '' && !is_null(request('bollo_a'))){
        	$invoice->bollo = request('bollo') ?? 0.00;
        	$invoice->bollo_a = request('bollo_a');
        } else {
        	$invoice->bollo = null;
        	$invoice->bollo_a = null;
        }
        

        $invoice->pa_n_doc = request('pa_n_doc');
        $invoice->pa_data_doc = request('pa_data_doc');
        $invoice->pa_cup = request('pa_cup');
        $invoice->pa_cig = request('pa_cig');
        $invoice->ddt_n_doc = request('ddt_n_doc');
        $invoice->ddt_data_doc = request('ddt_data_doc');

        $invoice->split_payment = request('split_payment');

        $invoice->save();

        $this->addItemToInvoice($request->itemsToForm, $invoice);

        if(request('deal_id')) {
            $this->attachToDeal($invoice, request('deal_id'));
        }

        return redirect('invoices?tipo='.request('tipo'))->with('message', 'Fattura Creata');
    }

    public function attachToDeal($invoice, $dealId) {
        if(class_exists("Deals\App\Models\DealEvent") && class_exists("Deals\App\Models\Deal")) {
            DealEvent::where('dealable_id', $invoice->id)->where('dealable_type', $invoice->full_class)->delete();
            DealEvent::createEvent($dealId, DealEvent::EVENTS['invoice'], $invoice->id, $invoice->full_class, $invoice->created_at);
            Deal::where('id', $dealId)->update([
                'accepted' => Deal::STATUSES['completed']
            ]);
        }
    }

    public function edit(Invoice $invoice)
    {
        $deals = [];
        if(class_exists("Deals\App\Models\Deal"))
        {
            $deals = ['' => ''];
            $dealsC = Deal::whereNull('accepted')->orWhere('accepted', true)->orderBy('created_at', 'DESC')->where('created_at', '>', Carbon::today()->subMonth(4))->get();
            foreach($dealsC as $deal)
            {
                $deals[$deal->id] = $deal->company->rag_soc . " N." . sprintf('%03d', $deal->numero);
            }
        }

        $contacts = ['' => '']+\DB::table('contacts')->select('id', \DB::raw('CONCAT(contacts.nome, " ", contacts.cognome) AS full_name'))->get()
                            ->pluck('full_name', 'id')->toArray();

        $companies = ['' => '']+Company::where('client_id', '!=', 4)->whereNotNull('piva')->orderBy('rag_soc', 'ASC')->pluck('rag_soc', 'id')->toArray();
        $sedi = Branch::orderBy('nome', 'ASC')->pluck('nome', 'id')->toArray();
        //$sedi = Company::where('client_id', 4)->whereNotNull('piva')->orderBy('rag_soc', 'ASC')->pluck('rag_soc', 'id')->toArray();
        $products = ['' => '']+Product::groupedOpt();
        $exemption_list = Exemption::where('connettore', 'Aruba')->orderBy('codice', 'ASC')->get();
        $exemptions = ['' => ''];
        foreach($exemption_list as $exemp){
        	$exemptions += [$exemp->id => $exemp->codice . ' - ' . $exemp->nome];
        }
        $selectedCompany = [$invoice->company_id];
        $items = $invoice->items()->with('product')->get();
        $invoices = Invoice::whereDate('data', '>', Carbon::today()->subMonths(6)->format('Y-m-d'))->where('id', '!=', $invoice->id)->get()->pluck('company_official_name', 'id')->toArray();
        return view('areaseb::core.accounting.invoices.edit', compact('contacts','invoice', 'companies', 'selectedCompany', 'products', 'exemptions', 'items', 'invoices', 'deals', 'sedi'));
    }

    public function update(Request $request, Invoice $invoice)
    {
    	if(request('company_id') == '' && request('contact_id') == '')
    		return redirect()->back()->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')->with('error', 'Devi impostare il cliente');
    	
        $invoice->tipo_doc = request('tipo_doc');
        $invoice->tipo = request('tipo');
        $invoice->numero = request('numero');
        $invoice->numero_registrazione = request('numero');
        $invoice->data = request('data');
        $invoice->data_registrazione = request('data');
        $invoice->company_id = request('company_id');
        $invoice->contact_id = request('contact_id');
        $invoice->riferimento = request('riferimento');
        $invoice->branch_id = request('branch_id');

        $invoice->pagamento = request('pagamento');
        $invoice->tipo_saldo = request('tipo_saldo');
        $invoice->data_saldo = request('data_saldo');
        $invoice->data_scadenza = $this->getDataScadenza($request);
        if(request('bollo_a') != '' && !is_null(request('bollo_a'))){
        	$invoice->bollo = request('bollo') ?? 0.00;
        	$invoice->bollo_a = request('bollo_a');
        } else {
        	$invoice->bollo = null;
        	$invoice->bollo_a = null;
        }

        $invoice->spese = request('spese') ?? 0.00;
        $invoice->perc_ritenuta = request('perc_ritenuta') ?? 0.00;
        $invoice->rate = request('rate');
        $invoice->saldato = request('data_saldo') ? 1 : 0;

        $invoice->pa_n_doc = request('pa_n_doc');
        $invoice->pa_data_doc = request('pa_data_doc');
        $invoice->pa_cup = request('pa_cup');
        $invoice->pa_cig = request('pa_cig');
        $invoice->ddt_n_doc = request('ddt_n_doc');
        $invoice->ddt_data_doc = request('ddt_data_doc');

        $invoice->split_payment = request('split_payment');

        $invoice->save();

        $this->updateItemsToInvoice($request->itemsToForm, $invoice);

		return redirect($request->previous)->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')->with('message', 'Fattura Aggiornata');
        //return redirect('invoices')->with('message', 'Fattura Aggiornata');
    }

    public function show(Invoice $invoice)
    {
        $company = $invoice->company;
        $contact = $invoice->contact($invoice->contact_id);
        return view('areaseb::core.accounting.invoices.show', compact('invoice', 'company','contact'));
    }

    public function destroy(Invoice $invoice)
    {
		if($invoice->status == 0)
        {
        	\DB::table('invoice_ora')->where('invoice_id', $invoice->id)->delete();
            $invoice->items()->delete();
            $invoice->delete();
        }
        
        return 'done';
    }

    public function checkUnique(Request $request)
    {
        return intval(Invoice::where('tipo', $request->type)->whereYear('data', $request->year)->where('numero', $request->number)->exists());
    }


    //api/invoices/{company}/list
    public function getOfCompany(Company $company)
    {
        $count_year = 0;
        if(Invoice::where('company_id', $company->id)->exists())
        {
            $max = Carbon::parse(Invoice::where('company_id', $company->id)->max('data'))->format('Y');
            $min = Carbon::parse(Invoice::where('company_id', $company->id)->min('data'))->format('Y');

            foreach(range($max, $min) as $year)
            {
                $arr = [''=>''];$arr= [];$count = 0;
                foreach(Invoice::where('company_id', $company->id)->whereYear('data', $year)->latest()->get() as $invoice)
                {
                    $arr[$count]['id'] = $invoice->id;
                    $arr[$count]['text'] = $invoice->numero.'/'.$invoice->data->format('Y') . ' ' . $invoice->total_formatted;
                    $count++;
                }
                $data[$count_year]['text'] = $year;
                $data[$count_year]['children'] = $arr;
                $count_year++;
            }

            return $data;
        }
        return [];
    }

    public function getNumberFromType($type, $anno = null, $id = null)
    {
        $anno = is_null($anno) ? request('anno') : $anno;
        $id = is_null($id) ? request('id') : $id;                

        if($id != null)
        {
            $element = Invoice::findOrFail($id);
           
            if($element->aperta == 0){            	
	            if($anno == $element->data->format('Y') && $element->tipo == $type)
	            {
	                return $element->numero;
	            }
	            elseif($anno == $element->data->format('Y') && ($type == 'U' || $type == 'A'))
	            {
	                return $element->numero;
	            }
	        }
	        
	        if(!is_null($element->company_id)){
	        	$branch_id = Company::where('id', $element->company_id)->first()->contact->branchContact()->branch_id;
	        } elseif(!is_null($element->contact_id)){
	        	$branch_id = Contact::where('id', $element->contact_id)->first()->branchContact()->branch_id;
	        }

            if($type == 'R' || $type == 'D')
            {
                $maxR = Invoice::where('tipo', 'R')->whereYear('data', $anno)->where('branch_id', $branch_id)->where('aperta', 0)->max('numero');
                $maxD = Invoice::where('tipo', 'D')->whereYear('data', $anno)->where('branch_id', $branch_id)->where('aperta', 0)->max('numero');
                
                return max($maxR, $maxD)+1;
            }

            $maxF = Invoice::where('tipo', 'F')->whereYear('data', $anno)->where('branch_id', $branch_id)->where('aperta', 0)->max('numero');
            $maxU = Invoice::where('tipo', 'U')->whereYear('data', $anno)->where('branch_id', $branch_id)->where('aperta', 0)->max('numero');
            $maxA = Invoice::where('tipo', 'A')->whereYear('data', $anno)->where('branch_id', $branch_id)->where('aperta', 0)->max('numero');
            return max($maxF, $maxU, $maxA)+1;
        }
		
		
        $branch_id = array();
        
        if(auth()->user()->hasRole('super')){
        	$branch_id = Sede::pluck('id')->toArray();
        } else {
        	$branch_id[] = auth()->user()->contact->branchContact()->branch_id;
        }
        
        if($type == 'P')
        {
            return Invoice::where('tipo', 'P')->whereYear('data', $anno)->whereIn('branch_id', $branch_id)->where('aperta', 0)->max('numero') + 1;
        }

        if($type == 'R' || $type == 'D')
        {
            $maxR = Invoice::where('tipo', 'R')->whereYear('data', $anno)->whereIn('branch_id', $branch_id)->where('aperta', 0)->max('numero');
            $maxD = Invoice::where('tipo', 'D')->whereYear('data', $anno)->whereIn('branch_id', $branch_id)->where('aperta', 0)->max('numero');

            return max($maxR, $maxD)+1;
        }

        $maxF = Invoice::where('tipo', 'F')->whereYear('data', $anno)->whereIn('branch_id', $branch_id)->where('aperta', 0)->max('numero');
        $maxU = Invoice::where('tipo', 'U')->whereYear('data', $anno)->whereIn('branch_id', $branch_id)->where('aperta', 0)->max('numero');
        $maxA = Invoice::where('tipo', 'A')->whereYear('data', $anno)->whereIn('branch_id', $branch_id)->where('aperta', 0)->max('numero');

        return max($maxF, $maxU, $maxA)+1;
    }

    /**
     * @param [json] $items   [js obj with all items from form]
     * @param [model] $invoice [invoice where to add items]
     */
    public function addItemToInvoice($items, $invoice)
    {
        $imposte = 0;
        $imponibile = 0;

        //save new item
        foreach(json_decode($items) as $item)
        {
            $sconto = 0;
            $percSconto = 0;
            $percIva = 0;
            if(isset($item->perc_sconto))
            {
                if(!is_null($item->perc_sconto))
                {
                    $percSconto = $item->perc_sconto/100;
                    $sconto = $item->perc_sconto;
                }
            }


            if(!is_null($item->perc_iva) || ($item->perc_iva != 0))
            {
                $percIva = $item->perc_iva/100;
            }

            if($percIva > 0)
            {
                if(isset($item->prezzo))
                {
                    $iva = $item->prezzo * $percIva * $item->qta * (1-$percSconto);
                }
                else
                {
                    $iva = $item->importo * $percIva * $item->qta * (1-$percSconto);
                }
            }
            else
            {
                $iva = 0;
            }

            if($item->item_id)
            {
                $i = Item::find($item->item_id);
                if($i)
                {
                    $i->update([
                        'exemption_id' => isset($item->exemption_id) ? $item->exemption_id : null,
                        'descrizione' => $this->cleanDescription($item->descrizione),
                        'qta' => $item->qta,
                        'sconto'=> $sconto,
                        'perc_iva' => $item->perc_iva,
                        'iva' => $iva,
                        'importo' => isset($item->prezzo) ? $item->prezzo : $item->importo,
                    ]);
                }
                else
                {
                    $i = Item::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $item->product_id,
                        'exemption_id' => isset($item->exemption_id) ? $item->exemption_id : null,
                        'descrizione' => $this->cleanDescription($item->descrizione),
                        'qta' => $item->qta,
                        'sconto'=> $sconto,
                        'perc_iva' => $item->perc_iva,
                        'iva' => $iva,
                        'importo' => isset($item->prezzo) ? $item->prezzo : $item->importo,
                    ]);
                }

            }
            else
            {

                $i = Item::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'exemption_id' => isset($item->exemption_id) ? $item->exemption_id : null,
                    'descrizione' => $this->cleanDescription($item->descrizione),
                    'qta' => $item->qta,
                    'sconto'=> $sconto,
                    'perc_iva' => $item->perc_iva,
                    'iva' => $iva,
                    'importo' => isset($item->prezzo) ? $item->prezzo : $item->importo,
                ]);
            }

            $p = (isset($item->prezzo) ? $item->prezzo : $item->importo) * (1-$percSconto);

            $imposte += $i->iva;
            $imponibile += ($p*$i->qta);

        }
		
		if($invoice->perc_ritenuta > 0){
			if($invoice->has_bollo){
				$ritenuta = ($imponibile - $invoice->bollo) * ($invoice->perc_ritenuta / 100);
			} else {
				$ritenuta = $imponibile * ($invoice->perc_ritenuta / 100);
			}			
			$invoice->ritenuta = floatval($ritenuta);
		}
		
		if($invoice->has_bollo)
        {
            if($invoice->bollo_a == 'cliente')
            {
                if(!$invoice->has_bollo_in_items)
                {
                    Item::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => Product::bollo(),
                        'exemption_id' => Exemption::esenzioneBollo(),
                        'descrizione' => "Assolvimento virtuale dell'imposta ai sensi del DM 17.6.2014",
                        'qta' => 1,
                        'sconto'=> 0,
                        'perc_iva' => 0,
                        'iva' => 0,
                        'importo' => $invoice->bollo,
                    ]);
                    $imponibile += $invoice->bollo;
                }
            }
        }

        $imposte = round($imposte, 2);
        $invoice->imponibile = $imponibile;
        $invoice->iva = $imposte;
        $invoice->save();

        return true;
    }


    /**
     * @param [json] $items   [js obj with all items from form]
     * @param [model] $invoice [invoice where to add items]
     */
    public function updateItemsToInvoice($items, $invoice)
    {
        $imposte = 0;
        $imponibile = 0;

        //save new item
        foreach(json_decode($items) as $item)
        {

            $sconto = 0;
            $percSconto = 0;
            $percIva = 0;
            if(isset($item->perc_sconto))
            {
                if(!is_null($item->perc_sconto))
                {
                    $percSconto = $item->perc_sconto/100;
                    $sconto = $item->perc_sconto;
                }
            }


            if(!is_null($item->perc_iva) || ($item->perc_iva != 0))
            {
                $percIva = $item->perc_iva/100;
            }

            if($percIva > 0)
            {
                $iva = $item->prezzo * $percIva * $item->qta * (1-$percSconto);
            }
            else
            {
                $iva = 0;
            }


            $descrizione = $this->cleanDescription($item->descrizione);

            if($item->item_id)
            {
                $i = Item::find($item->item_id);
                if($i){
                	$i->update([
	                    'exemption_id' => isset($item->exemption_id) ? $item->exemption_id : null,
	                    'descrizione' => $descrizione,
	                    'qta' => $item->qta,
	                    'sconto'=> $sconto,
	                    'perc_iva' => $item->perc_iva,
	                    'iva' => $iva,
	                    'importo' => $item->prezzo
	                ]);
                }
                
            }
            else
            {

                $i = Item::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'exemption_id' => isset($item->exemption_id) ? $item->exemption_id : null,
                    'descrizione' => $descrizione,
                    'qta' => $item->qta,
                    'sconto'=> $sconto,
                    'perc_iva' => $item->perc_iva,
                    'iva' => $iva,
                    'importo' => $item->prezzo
                ]);
            }

			if($i){
				$imposte += $i->iva;
            	$imponibile += ($i->importo*$i->qta*(1-$percSconto));
			}            

        }
		
		if($invoice->perc_ritenuta > 0){
			if($invoice->has_bollo){
				$ritenuta = ($imponibile - $invoice->bollo) * ($invoice->perc_ritenuta / 100);
			} else {
				$ritenuta = $imponibile * ($invoice->perc_ritenuta / 100);
			}
			$invoice->ritenuta = floatval($ritenuta);
		} else {
			$invoice->ritenuta = 0;
		}
		
        if($invoice->has_bollo)
        {
            if($invoice->bollo_a == 'cliente')
            {
                if(!$invoice->has_bollo_in_items)
                {
                    Item::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => Product::bollo(),
                        'exemption_id' => Exemption::esenzioneBollo(),
                        'descrizione' => "Assolvimento virtuale dell'imposta ai sensi del DM 17.6.2014",
                        'qta' => 1,
                        'sconto'=> 0,
                        'perc_iva' => 0,
                        'iva' => 0,
                        'importo' => $invoice->bollo,
                    ]);
                    $imponibile += $invoice->bollo;
                }
            }
        }

        if($this->checkIfHasSpese($invoice))
        {
            if( (!is_null($invoice->spese)) && ($invoice->spese != 0.00))
            {
                Item::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => Product::spese(),
                    'exemption_id' => Exemption::esenzioneBollo(),
                    'descrizione' => "Spese di incasso",
                    'qta' => 1,
                    'sconto'=> 0,
                    'perc_iva' => 0,
                    'iva' => 0,
                    'importo' => $invoice->spese,
                ]);
                $imponibile += $invoice->spese;
            }
        }

        $imposte = round($imposte, 2);
        $invoice->imponibile = $imponibile;
        $invoice->iva = $imposte;
        $invoice->save();

        return true;
    }


    //invoices-item/{item} route('invoices.item.delete')
        public function deleteItem(Item $item)
        {
            $iva = 0;
            $imponibile = 0;
            $invoice = $item->invoice;
            $id_ora = $item->ora_id;
            $item->delete();

            foreach($invoice->items as $i)
            {
                $imponibile += $i->qta * $i->importo * (1-($i->sconto/100));
                $iva += ($i->qta * $i->importo * (1-($i->sconto/100))) * ($i->perc_iva/100);
            }

            $invoice->update([
                'imponibile' => $imponibile,
                'iva' => $iva
            ]);
            
            \DB::table('invoice_ora')->where('ora_id', $id_ora)->where('invoice_id', $invoice->id)->delete();

            return 'done';
        }


    /**
     * Terenziani sconto su prezzo ivato
     * @param [json] $items   [js obj with all items from form]
     * @param [model] $invoice [invoice where to add items]
     */
    public function addItemToInvoiceTerenziani($items, $invoice)
    {

        if($invoice->items()->exists())
        {
            Item::destroy($invoice->items()->pluck('id'));
        }

        $company = $invoice->company;

        $imposte = 0;
        $imponibile = 0;

        //save new item
        foreach(json_decode($items) as $item)
        {
            $product = Product::find($item->id);
            $percSconto = 0;
            $sconto = 0;
            if(isset($item->perc_sconto))
            {
                if(!is_null($item->perc_sconto))
                {
                    $percSconto = $item->perc_sconto/100;
                    $sconto = $item->perc_sconto;
                }
            }


            $importo = $product->prezzo * (1+(config('app.iva')/100)) * (1-$percSconto);
            $pNoiva = $importo / (1+(config('app.iva')/100));

            if($company->nation== "IT")
            {
                $iva = ($importo-$pNoiva)* $item->qta;
                if(isset($item->perc_sconto))
                {
                    if(isset($item->exemption_id))
                    {
                        $ex = $item->exemption_id;
                    }
                    else
                    {
                        $ex = null;
                    }

                }
                else
                {
                    $ex = null;
                }
            }
            else
            {
                $iva = 0;
                $ex = 3;
            }

            $i = Item::create([
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'exemption_id' => $ex,
                'descrizione' => $this->cleanDescription($item->descrizione),
                'qta' => $item->qta,
                'sconto'=> $sconto,
                'perc_iva' => $item->perc_iva,
                'iva' => $iva,
                'importo' => $product->prezzo * (1+(config('app.iva')/100)),
            ]);

            $imposte += $i->iva;
            $imponibile += ($product->prezzo * (1+(config('app.iva')/100)) * (1-$percSconto) * $item->qta);
        }

        if($invoice->has_bollo)
        {
            if($invoice->bollo_a == 'cliente')
            {
                if(!$invoice->has_bollo_in_items)
                {
                    Item::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => Product::bollo(),
                        'exemption_id' => Exemption::esenzioneBollo(),
                        'descrizione' => "Assolvimento virtuale dell'imposta ai sensi del DM 17.6.2014",
                        'qta' => 1,
                        'sconto'=> 0,
                        'perc_iva' => 0,
                        'iva' => 0,
                        'importo' => $invoice->bollo,
                    ]);
                    $imponibile += $invoice->bollo;
                }
            }
        }

        $imposte = round($imposte, 2);
        $invoice->imponibile = $imponibile;
        $invoice->iva = $imposte;
        $invoice->save();

    }

    /**
     * if rate not null split payment
     * @param  [type] $invoice [description]
     * @return [type]          [description]
     */
    public function manageRate($invoice)
    {
        return 'todo';
    }

    public function checkBeforeFe(Invoice $invoice)
    {
        $company = $invoice->company;
        if(is_null($company->cf))
        {
            return ['status' => false, 'id' => $company->id, 'field' => 'cf'];
        }

        if($company->is_italian)
        {
            if(!$company->private)
            {
                if(is_null($company->sdi) && is_null($company->pec))
                {
                    if(is_null($company->sdi))
                    {
                        return ['status' => false, 'id' => $company->id, 'field' => 'sdi'];
                    }
                }
            }
        }

        return ['status' => true];
    }


    public function sendFe(Invoice $invoice)
    {
        if(!config('core.modules')['fe'])
        {
            return back()->with('message', 'Non hai il modulo per la Fattura Elettronica');
        }

        $settings = Setting::fe();
        if($settings->connettore == 'Fatture in Cloud')
        {
            $api = new \App\FeiC\Actions\Send($invoice);
            $result = $api->send();

            if($result)
            {
                $invoice->update(['status' => 1]);
                return back()->with('message', 'Fattura inviata');
            }
            else
            {
                // TODO Check error
                $error = 'Errore';
                return back()->with('error', $error);
            }
        }
        elseif($settings->connettore == 'Aruba')
        {
            $sender = new \App\Fe\Actions\Send($invoice, Setting::fe());

            $response = $sender->init();

            if($response == 'done')
            {
                return back()->with('message', 'Fattura inviata');
            }
            return back()->with('error', $response);
        }
        return back()->with('message', 'Fattura NON inviata');
    }


    public function getDataScadenza($request)
    {
        $deadlines = config('invoice.payment_types_dead_lines');
        $q = $deadlines[$request->pagamento];
        
        if(strstr($request->data, " ")){
    		$data = explode(' ', $request->data);
    		$request->data = $data[0];
        }
        
        if($q > 0)
        {
            return Carbon::createFromFormat('d/m/Y', $request->data)->addDays($q)->lastOfMonth();
        }
        return Carbon::createFromFormat('d/m/Y', $request->data)->format('Y-m-d');
    }

//api/invoices/saldato - POST
    public function toggleSaldato(Request $request)
    {
        $invoice = Invoice::find($request->id);
            $invoice->saldato = intval($request->saldato);
        $invoice->save();

        if(intval($request->saldato) === 1)
        {
            return "Ora la fattura risulta pagata";
        }
        return 'Fattura non saldata';
    }

    public function duplicate(Invoice $invoice)
    {
    	if(\DB::table('invoice_ora')->where('invoice_id', $invoice->id)->exists()){
    		return redirect(route('invoices.index'))->with('error', 'Fattura non duplicabile perchè ci sono ore collegate');
    	}
    	
        
        if($invoice->tipo == 'P')
        {
            $numero = $this->getNumberFromType('F', date('Y'));
        }
        else
        {
            $numero = $this->getNumberFromType($invoice->tipo, date('Y'));
        }

        $deadlines = config('invoice.payment_types_dead_lines');
        $q = $deadlines[$invoice->pagamento];
        $data = date('d/m/Y');

        $new = $invoice->replicate();
        	$new->tipo = $invoice->tipo;
            $new->numero = $numero;
            $new->numero_registrazione = $numero;
            $new->data = $data;
            $new->data_scadenza = Carbon::createFromFormat('d/m/Y', $data)->addDays($q)->lastOfMonth()->format('Y-m-d');
            $new->data_saldo = null;
            $new->sendable = 0;
            $new->status = 0;
        $new->save();

        foreach($invoice->items as $item)
        {
            $new_item = $item->replicate();
            $new_item->invoice_id = $new->id;
            $new_item->save();
        }

        return redirect(route('invoices.edit', $new->id))->with('message', 'Fattura dupplicata');
    }

//invoices/{invoice}/edit-saldo - GET
    public function editSaldoForm($invoice)
    {
        $invoice = Invoice::find($invoice);
        return view('areaseb::core.accounting.invoices.form-edit-saldo', compact('invoice'));
    }
//invoices/{invoice}/update-saldo - PATCH
    public function updateSaldoForm($invoice)
    {
        $data_saldo = request('data_saldo');
        if(strpos(request('data_saldo'), ":") !== false)
        {
            $arr = explode(" ", request('data_saldo'));
            $data_saldo = $arr[0];
        }
        $invoice = Invoice::find($invoice);
            $invoice->tipo_saldo = request('tipo_saldo');
            $invoice->data_saldo = $data_saldo;
            $invoice->saldato = true;
        $invoice->save();

        return back()->with('message', 'Fattura Modificata');
    }

//invoices/{invoice}/mark-as-unpaid
    public function markAsUnpaid(Request $request, Invoice $invoice)
    {
        $invoice->update(['data_saldo' => null, 'saldato' => 0]);
        return back();
    }

//api/invoices/import - GET
    public function import()
    {
        return view('areaseb::core.accounting.invoices.import');
    }

//api/invoices/import - POST
    public function importProcess(Request $request)
    {
        $class = new XmlToInvoice($request->file);
        $class->init();
        return redirect(route('invoices.index'));
    }


//api/invoices/export?anno=2020&company=&mese=02&range=&saldato=&tipo= - GET
    public function exportXmlInZip()
    {
        if(request()->input())
        {
            $invoices = Invoice::filter(request())->get();
        }
        else
        {
            $invoices = Invoice::anno(date('Y'))->get();
        }

        $zip_file = 'invoices.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach($invoices as $invoice)
        {
            if($invoice->media()->xml()->exists())
            {
                $zip->addFile($invoice->real_xml, $invoice->media()->xml()->first()->filename);
            }
            else
            {
                $this->export($invoice);
                $zip->addFile($invoice->real_xml, $invoice->media()->xml()->first()->filename);
            }
        }
        $zip->close();

        return response()->download($zip_file);
    }

    public function export(Invoice $invoice)
    {
        (new InvoiceToXml($invoice, Setting::fe()))->init();
        $stringname = $invoice->media()->xml()->first()->filename;
        $arr = explode('/',$stringname);
        $filename = $arr[1];
        $file = storage_path('app/public/fe/inviate/'.$invoice->media()->xml()->first()->filename);

        return response()->download($file, $filename, ['Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0']);
    }

    public function exportPost(Request $request, Invoice $invoice)
    {
        (new InvoiceToXml($invoice, Setting::fe()))->init();

        $fcResponse = (new FatturaCheck(['filename' => $invoice->media()->xml()->first()->filename]))->init();

        if(!$fcResponse['is_empty'])
        {
            if(!$fcResponse['isValid'])
            {
                return $fcResponse['errors'][0];
            }
        }
        return 'done';
    }

    public function cleanDescription($str)
    {
        $str = str_replace('€', 'EUR', $str);
        $str = str_replace('£', 'GBP', $str);
        $str = str_replace('$', 'USD', $str);
        $str = str_replace('©',' Copyright', $str);
        $str = str_replace('®', ' Registered', $str);
        $str = str_replace('™',' Trademark', $str);
        return $str;
    }

    private function checkIfHasSpese($invoice)
    {
        foreach($invoice->items as $item)
        {
            if($item->product_id == 179)
            {
                return false;
            }
        }
        return true;
    }


    public function sendNotice(Request $request, Invoice $invoice)
    {
        if(Setting::validSmtp(0))
        {
            $mailer = app()->makeWith('custom.mailer', Setting::smtp(0));
            $name = $invoice->media()->pdf()->first()->filename;
            $mailer->send(new Notice($name, $invoice));

            InvoiceNotice::create([
                'invoice_id' => $invoice->id,
                'response' => "Inviato sollecito automatico",
                'type' => 'email',
                'date' => Carbon::today()
            ]);

            return back()->with('message', 'Sollectio inviato');
        }

        return back()->with('message', "Sollectio salvato nel database ma l'email non è stata spedita perché non hai impostato un server di posta");
    }


    //prima nota
    public function getPrimaNota(Request $request)
    {
        $branches = Sede::all();

/*        $anno_in =  intval(date("Y")) - 1;
        $anno_out =  intval(date("Y"));
        $data_dal = $anno_in.'-07-01';
        $data_al = $anno_out.'-06-30';*/
        $data_dal = date('Y-m-d');
        $data_al = date('Y-m-d');

        if(isset($request->data_in)){
            $data_dal = $request->data_in;
        }
        if(isset($request->data_out)){
            $data_al = $request->data_out;
        }

        $query_sede = "";
        if(isset($request->branch_id) && $request->branch_id != ""){
            $query_sede = " and branch_id = ".$request->branch_id;
        }
        
        $query = "select distinct data from invoices where \"$data_dal\" <= data and data <= \"$data_al\" and numero is not null $query_sede order by data;";        // dd($query);
        $date = \DB::select($query);
        $arr_finale = [];

        foreach ($date as $d) {
            $arr_data = [];
            list($a, $m, $g) = explode("-", $d->data);
            array_push($arr_data, $g.'/'.$m.'/'.$a );
            $totale_finale = 0;
            foreach (array_keys(config('invoice.payment_modes')) as $pt) {
                if($pt != ''){

                    $item = Invoice::select(\DB::raw('SUM(imponibile) as imponibile'), \DB::raw('SUM(ritenuta) as ritenuta'))
                    ->where("data", $d->data)
                    ->where("saldato", 1)
                    ->where("tipo_saldo", $pt)
                    ->first();
                    //dd($item);
                    $totale = $item->imponibile - $item->ritenuta;	
                    $totale_finale = $totale_finale + $totale;
                    array_push($arr_data, $totale );
                }
                
            }
            array_push($arr_data, $totale_finale );
            array_push($arr_finale, $arr_data );
        }

        //dd($arr_finale);
        /*if(request()->input())
        {
            $companies = Company::filter(request())->where('client_id','!=', 4)->orderBy('rag_soc', 'ASC')->paginate(30);
        }
        else
        {
            if(auth()->user()->hasRole('testimonial'))
            {
                $query = auth()->user()->testimonial->companies();
            }
            elseif(auth()->user()->hasRole('agent'))
            {
                if(auth()->user()->hasRole('super'))
                {
                    $query = Company::query();
                }
                else
                {
                    $query = auth()->user()->agent->companies();
                }
            }
            else
            {
                $query = Company::query();
            }
            $companies = $query->where('client_id','!=', 4)->with('client', 'sector')->orderBy('rag_soc', 'ASC')->paginate(30);
        }


        foreach ($companies as $key => $value) {
            if($value->contacts()->first() != null && $value->contacts()->first()->contact_type_id == 3)
                $companies->forget($key);
        }*/

        return view('areaseb::core.prima_nota.index',compact('branches','arr_finale'));
    }


    public function getOreAperte(Request $request)
    {
        $contact = auth()->user()->contact;
        $branch = $contact->branch($contact->id);
        
        $items = Item::select('items.id as item_id','items.importo','items.ora_id','invoices.*','ora.data as data_inv','ora.ora_in as ora_in_inv', 'ora.ora_out as ora_out_inv', 
                                'ora.id_cliente', 'ora.id_maestro', 'ora.id_cc as sede_id')
                    ->join("invoices","invoices.id","items.invoice_id")
                    ->join("ora","ora.id","items.ora_id")
                    ->where('invoices.aperta', 1);

		if(count($branch) == 1){
			$items = $items->where('invoices.branch_id', $branch[0]);
		}			

        if(isset(request()->client))
            $items = $items->where('ora.id_cliente', request()->client);
        if(isset(request()->ritrovo))
            $items = $items->where('ora.ritrovo', request()->ritrovo);
        if(isset(request()->sede))
            $items = $items->where('ora.id_cc', request()->sede);

        $year = date("Y");
        $from = date('Y-m-d');	//date(strval($year -1 ).'-07-01');
        $to = date('Y-m-d');

        if(isset(request()->from))
            $from = date(request()->from);
        if(isset(request()->to))
            $to = date(request()->to);
            
        $items = $items->whereBetween('ora.data', [$from, $to]);


        $items = $items->whereNot('ora.id_cliente', 'LIKE', 'L%')->whereNot('ora.id_cliente', 'LIKE', 'C%');
        /*if(!request()->input())
        {
            $companies = Company::filter(request())->where('client_id','!=', 4)->orderBy('rag_soc', 'ASC')->paginate(30);
        }
        */
        $items = $items->get();

        foreach ($items as $item) {
            $item->cliente = null;
            $item->cliente_id = null;

            list($tipo, $id_cliente) = explode('_', $item->id_cliente);
            $item->tipo = $tipo;
            $item->cliente_id = $id_cliente;
            if($tipo == 'Y'){
                $com = Company::find($id_cliente);
                if($com != null)
                    $item->cliente =$com->rag_soc;
            }
            if($tipo == 'T'){
                $cont = Contact::find($id_cliente);
                if($cont != null)
                    $item->cliente =$cont->nome.' '.$cont->cognome;
            }
                
        
            $maestro = Contact::find( $item->id_maestro);
            if($maestro != null)
                $item->maestro =$maestro->nome.' '.$maestro->cognome;
            
            $item->sede_lbl = '';
            $sede = Sede::find($item->sede_id);
            if($sede != null)
                $item->sede_lbl = $sede->nome;

            $item->disciplina_desc = "";

            switch ($item->disciplina) {
                case 1:
                    $item->disciplina_desc = "Discesa";
                    break;
                case 2:
                    $item->disciplina_desc = "Fondo - Classico";
                    break;
                case 3:
                    $item->disciplina_desc = "Fondo - Skating";
                    break;
                default:
                    $item->disciplina_desc = "Snowboard";
            }
        }

        $clienti = $this->generateListaClienti();
        $ritrovi =Hangout::orderby('luogo', 'ASC')->get();
        $sedi =Sede::orderby('nome', 'ASC')->get();
        $products = [''=>'']+Product::orderby('nome', 'ASC')->pluck('nome', 'id')->toArray();
        return view('areaseb::core.ore_aperte.index', compact('items','products','ritrovi','clienti','sedi'));
    }

    public function generateListaClienti(){
        $arrClienti =[];
        $com = Company::where('client_id', '!=', 4)->get();
        foreach ($com as $value) {
            array_push($arrClienti,(object)['id'=> 'Y_'.$value->id, 'nome' => $value->rag_soc]);
        }
        $cont = Contact::all();
        foreach ($cont as $value) {
            array_push($arrClienti,(object)['id'=> 'T_'.$value->id, 'nome' => $value->nome.' '.$value->cognome]);
        }

        return $arrClienti;
    }

    public function getCollettiviAperti(Request $request)
    {
		$contact = auth()->user()->contact;
        $branch = $contact->branch($contact->id);
        
        $items = Item::select('items.id as item_id','items.importo','items.ora_id','invoices.*','ora.data as data_inv','ora.ora_in as ora_in_inv', 'ora.ora_out as ora_out_inv', 
                    'ora.id_cliente', 'ora.id_maestro', 'ora.id_cc as sede_id')
            ->join("invoices","invoices.id","items.invoice_id")
            ->join("ora","ora.id","items.ora_id")
            ->where('invoices.aperta', 1);
		
		if(count($branch) == 1){
			$items = $items->where('invoices.branch_id', $branch[0]);
		}
		
            $year = date("Y");
            $from = date('Y-m-d');	//date(strval($year -1 ).'-07-01');
            $to = date('Y-m-d');

            if(isset(request()->from))
                $from = date(request()->from);
            if(isset(request()->to))
                $to = date(request()->to);
            if(isset(request()->sede))
                $items = $items->where('ora.id_cc', request()->sede);

            $items = $items->whereBetween('ora.data', [$from, $to]);


            $items = $items->where('ora.id_cliente', 'LIKE', 'C%');

            $items = $items->get();

            foreach ($items as $item) {
                $item->cliente = null;
                $item->cliente_id = null;

                list($tipo, $id_cliente) = explode('_', $item->id_cliente);
                $item->tipo = $tipo;
                $item->cliente_id = $id_cliente;

                $collett_id = null;
                if($tipo == 'C'){
                    $coll = Collettivo::find($id_cliente);
                    if($coll != null){
                        $item->collettivo =$coll->nome;
                        $collett_id=$coll->id;
                    }
                        
                }

                $item->collett_id = $collett_id;
                $item->sede_lbl = '';
                $sede = Sede::find($item->sede_id);
                if($sede != null)
                    $item->sede_lbl = $sede->nome;

                $item->disciplina_desc = "";

                switch ($item->disciplina) {
                    case 1:
                        $item->disciplina_desc = "Discesa";
                        break;
                    case 2:
                        $item->disciplina_desc = "Fondo - Classico";
                        break;
                    case 3:
                        $item->disciplina_desc = "Fondo - Skating";
                        break;
                    default:
                        $item->disciplina_desc = "Snowboard";
                }


                $item->maestri = $coll->listMastersInTable();
            }

            $sedi =Sede::orderby('nome', 'ASC')->get();
            $products = [''=>'']+Product::orderby('nome', 'ASC')->pluck('nome', 'id')->toArray();
            return view('areaseb::core.collettivi_aperti.index', compact('items','products','sedi'));

    }


    public function fatturareOreAperte(Request $request){
        \DB::beginTransaction();
        $res = array(); 

        try{
        	if($request->ids_invoice){
        		$ids_invoice = explode(",",$request->ids_invoice);
        	} else {
        		$ids_invoice = null;
        	}   
        	
            $ids_ore = explode(",",$request->ids_ore);
            
            if($request->ids_item){
        		$ids_item = explode(",",$request->ids_item);
        	} else {
        		$ids_item = null;
        	}            
         
           	//0 step --> creare le item mancanti e agganciarle alla fattura temporanea, se non esiste crearla
            if(is_null($ids_invoice)){
            	
            	//prendere company_id dall'ora
            	
            	//se è un collettivo...
            	if(strstr($ids_ore[0], '*')){
            		list($ore, $tipo_cli) = explode('*', $ids_ore[0]);
            		$ore_check = explode('-', $ore);
            		$ora_check = Ora::whereIn('id', $ore_check)->first();
            		list($tipo, $id_cli) = explode('_', $tipo_cli);
            	} else {
            		//se sono ore normali
            		$ora_check = Ora::whereIn('id', $ids_ore)->first();
            		list($tipo, $id_cli) = explode('_', $ora_check->id_cliente);
            	}
            	            	
            	$old_invoice = new Invoice();
	            $old_invoice->numero = null;
	            $old_invoice->numero_registrazione = null;
	            $old_invoice->company_id = $tipo == 'Y' ? $id_cli : null;
	            $old_invoice->contact_id = $tipo == 'T' ? $id_cli : null;
	            
	            $old_invoice->data = date("Y-m-d");
	            $old_invoice->data_registrazione = date("Y-m-d");

	            $old_invoice->branch_id = $ora_check->id_cc;
	            $old_invoice->pagamento = 'RIDI';
	            $old_invoice->aperta = 1;
	            
	            $old_invoice->save();
	            
	            $ids_invoice[] = $old_invoice->id;
	            
	            if(is_null($ids_item)){
	            	
	            	if($tipo == 'C'){
	            		$ids_ore_temp = $ids_ore;
	            		$lista_ore = '';
	            		foreach($ids_ore_temp as $ids_ot){
	            			list($ore, $tipo_cli) = explode('*', $ids_ot);
	            			$lista_ore .= $ore.'-';
	            		}
	            		$ids_ore = explode('-', substr($lista_ore, 0, -1));
	            	}
	            	
	            	foreach($ids_ore as $ora){
	            		
	            		$ora = Ora::find($ora);
	            		
	            		$elenco_maestri = "";
						$contact_id = Master::find($ora->id_maestro)->contact_id;
						$mm = Contact::where('id', $contact_id)->get();
						foreach ($mm as $value) {
							$elenco_maestri = $elenco_maestri != '' ? $elenco_maestri.','.$value->nome.' '.$value->cognome : $value->nome.' '.$value->cognome;
						}

						$elenco_spec = "";
						$specs = Specialization::whereIn('id', [$ora->specialita])->get();						
						foreach ($specs as $value) {
							$elenco_spec = $elenco_spec != '' ? $elenco_spec.','.$value->nome : $value->nome;
						}
						
						$nome_disciplina = 'Discesa';
						if($ora->disciplina == 2 || $ora->disciplina == 3)
							$nome_disciplina = 'Fondo';
						if($ora->disciplina == 4)
							$nome_disciplina = 'Snowboard';
						
						if(substr($ora->id_cliente, 0, 1) == 'C'){
							$descrizione = '<b>Corso collettivo:</b> ' . Collettivo::find(substr($ora->id_cliente, 2))->nome .'
											<br /><b>Data:</b> ' . \Carbon\Carbon::createFromFormat('Y-m-d', $ora->data)->format('d/m/Y') . '
											<br /><b>Ora:</b> dalle ' . $ora->ora_in . ' alle ' . $ora->ora_out . '
											<br /><b>Maestro:</b> ' . $elenco_maestri . '
											<br /><b>Disciplina:</b> ' . $nome_disciplina . '
											<br /><b>Specialit&agrave;:</b> ' . $elenco_spec;
						} else {
							$descrizione = "<b>Data:</b> " . \Carbon\Carbon::createFromFormat('Y-m-d', $ora->data)->format('d/m/Y') . '
											<br /><b>Ora:</b> dalle ' . $ora->ora_in . ' alle ' . $ora->ora_out . '
											<br /><b>Ritrovo:</b> ' . $ora->ritrovo . '
											<br /><b>Maestro:</b> ' . $elenco_maestri . '
											<br /><b>Pax:</b> ' . $ora->pax . '
											<br /><b>Disciplina:</b> ' . $nome_disciplina . '
											<br /><b>Specialit&agrave;:</b> ' . $elenco_spec;
						}
						
	            		
										
						// calcolo quante ore devo inserire
						$origin = date_create($ora->data . ' ' . substr($ora->ora_in, 0, 8));
						$target = date_create($ora->data . ' ' . substr($ora->ora_out, 0, 8));
						$interval = date_diff($origin, $target);
						$diff_ore = $interval->format('%H');
						$diff_min = $interval->format('%I');
						if($diff_min == 30){
							$diff_ore += 0.5;
						}
	            		
	            		$item = new Item();
	            		if(substr($ora->id_cliente, 0, 1) == 'C'){
	            			$item->product_id = Product::where('nome', 'like', '%collettivo%')->first()->id;
	            			$item->importo = Product::where('nome', 'like', '%collettivo%')->first()->prezzo;
						    $item->perc_iva = Product::where('nome', 'like', '%collettivo%')->first()->perc_iva;
						    $item->iva = Product::where('nome', 'like', '%collettivo%')->first()->prezzo * (Product::where('nome', 'like', '%collettivo%')->first()->perc_iva / 100);
							$item->exemption_id = Product::where('nome', 'like', '%collettivo%')->first()->exemption_id;
	            		} else {
	            			$item->product_id = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->id;
	            			$item->importo = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->prezzo;
						    $item->perc_iva = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->perc_iva;
						    $item->iva = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->prezzo * (Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->perc_iva / 100);
							$item->exemption_id = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->exemption_id;
	            		}	
	            		$item->descrizione = $descrizione;
	            		$item->qta = $diff_ore;	            		
					    $item->invoice_id = $old_invoice->id;					    
					    $item->ora_id = $ora->id;
					    $item->save();
					    
					    $ids_item[] = $item->id;
					    
					    \DB::table('invoice_ora')->insert(['invoice_id' => $old_invoice->id, 'ora_id' => $ora->id]);
	            	}
	            }
	            
            }
           	
           	// se è un collettivo devo trovare tutte le item perchè me ne passa solo 1
           	if(strstr($ids_ore[0], '*')){
           		foreach($ids_item as $item){
           			$voci = Item::find($item)->invoice->items;
           			foreach($voci as $voce){
           				$ids_item[] = $voce->id;
           			}
           		}
           		array_unique($ids_item);
           		
        		$ids_ore_temp = $ids_ore;
        		$lista_ore = '';
        		foreach($ids_ore_temp as $ids_ot){
        			if(strstr($ids_ot, '*')){
	        			list($ore, $tipo_cli) = explode('*', $ids_ot);
	        			$lista_ore .= $ore.'-';
	        		} else {
	        			$lista_ore .= $ids_ot.'-';
	        		}
        		}
        		$ids_ore = explode('-', substr($lista_ore, 0, -1));
	            	
           	}
           	           	
            $items = Item::select('invoices.*','items.id as item_id', 'items.ora_id', 'items.product_id')->join("invoices","invoices.id","items.invoice_id")->whereIn('items.id', $ids_item)->get();
            $item = $items->first();
          
            //se ci sono tutte le items per ogni ora bene, altrimenti le creo 
            $id_ora_found = $items->pluck('ora_id')->toArray();
            $items_to_create = array_diff($ids_ore, $id_ora_found);
             	
            foreach($items_to_create as $ora_id){
	            
	            $ora = Ora::find($ora_id);
	            
	    		$elenco_maestri = "";
				$contact_id = Master::find($ora->id_maestro)->contact_id;
				$mm = Contact::where('id', $contact_id)->get();
				foreach ($mm as $value) {
					$elenco_maestri = $elenco_maestri != '' ? $elenco_maestri.','.$value->nome.' '.$value->cognome : $value->nome.' '.$value->cognome;
				}

				$elenco_spec = "";
				$specs = Specialization::whereIn('id', [$ora->specialita])->get();						
				foreach ($specs as $value) {
					$elenco_spec = $elenco_spec != '' ? $elenco_spec.','.$value->nome : $value->nome;
				}
				
				$nome_disciplina = 'Discesa';
				if($ora->disciplina == 2 || $ora->disciplina == 3)
					$nome_disciplina = 'Fondo';
				if($ora->disciplina == 4)
					$nome_disciplina = 'Snowboard';
				
				if(substr($ora->id_cliente, 0, 1) == 'C'){
					$descrizione = '<b>Corso collettivo:</b> ' . Collettivo::find(substr($ora->id_cliente, 2))->nome .'
									<br /><b>Data:</b> ' . \Carbon\Carbon::createFromFormat('Y-m-d', $ora->data)->format('d/m/Y') . '
									<br /><b>Ora:</b> dalle ' . $ora->ora_in . ' alle ' . $ora->ora_out . '
									<br /><b>Maestro:</b> ' . $elenco_maestri . '
									<br /><b>Disciplina:</b> ' . $nome_disciplina . '
									<br /><b>Specialit&agrave;:</b> ' . $elenco_spec;
				} else {
					$descrizione = "<b>Data:</b> " . \Carbon\Carbon::createFromFormat('Y-m-d', $ora->data)->format('d/m/Y') . '
									<br /><b>Ora:</b> dalle ' . $ora->ora_in . ' alle ' . $ora->ora_out . '
									<br /><b>Ritrovo:</b> ' . $ora->ritrovo . '
									<br /><b>Maestro:</b> ' . $elenco_maestri . '
									<br /><b>Pax:</b> ' . $ora->pax . '
									<br /><b>Disciplina:</b> ' . $nome_disciplina . '
									<br /><b>Specialit&agrave;:</b> ' . $elenco_spec;
				}
								
				// calcolo quante ore devo inserire
				$origin = date_create($ora->data . ' ' . substr($ora->ora_in, 0, 8));
				$target = date_create($ora->data . ' ' . substr($ora->ora_out, 0, 8));
				$interval = date_diff($origin, $target);
				$diff_ore = $interval->format('%H');
				$diff_min = $interval->format('%I');
				if($diff_min == 30){
					$diff_ore += 0.5;
				}
	    		
	    		$item_new = new Item();
	    		if(substr($ora->id_cliente, 0, 1) == 'C'){
        			$item_new->product_id = Product::where('nome', 'like', '%collettivo%')->first()->id;
        			$item_new->importo = Product::where('nome', 'like', '%collettivo%')->first()->prezzo;
				    $item_new->perc_iva = Product::where('nome', 'like', '%collettivo%')->first()->perc_iva;
				    $item_new->iva = Product::where('nome', 'like', '%collettivo%')->first()->prezzo * (Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->perc_iva / 100);
					$item_new->exemption_id = Product::where('nome', 'like', '%collettivo%')->first()->exemption_id;
        		} else {
        			$item_new->product_id = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->id;
        			$item_new->importo = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->prezzo;
				    $item_new->perc_iva = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->perc_iva;
				    $item_new->iva = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->prezzo * (Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->perc_iva / 100);
					$item_new->exemption_id = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->exemption_id;
        		}	
	    		$item_new->descrizione = $descrizione;
	    		$item_new->qta = $diff_ore;
			    $item_new->invoice_id = $item->id;
			    $item_new->ora_id = $ora->id;
			    $item_new->save();
			    
			    $ids_item[] = $item_new->id;
			    
			    \DB::table('invoice_ora')->insert(['invoice_id' => $item->id, 'ora_id' => $ora->id]);
	    	}	
            
            
            //riprendo le items aggiornate
            $items = Item::select('invoices.*','items.id as item_id', 'items.ora_id', 'items.product_id')->join("invoices","invoices.id","items.invoice_id")->whereIn('items.id', $ids_item)->get();
                       
            //1 step --> creare nuova Invoice
            $invoice = new Invoice();
            $invoice->numero = $this->getNumberFromType('R', date('Y'));
            $invoice->numero_registrazione = $this->getNumberFromType('R', date('Y'));
            $invoice->company_id = $item->company_id;
            $invoice->contact_id = $item->contact_id;
            
            $invoice->data = date("Y-m-d");
            $invoice->data_registrazione = date("Y-m-d");
 
            $invoice->branch_id = $item->branch_id;
            $invoice->pagamento = 'RIDI';
            $invoice->aperta = 0;
            
            $invoice->save();

            
            $arr_id_invoice = [];
            foreach ($items as $item) {
            	
                //2 step --> modificare invoice_id di Item in base all'Invoice appena creata
                // e, in caso di collettivo, imposto il saldo al netto degli acconti
                
                
                $ora = Ora::find($item->ora_id);
                                         
                if($ora && substr($ora->id_cliente, 0, 1) == 'C'){
                	
                	if($item->contact_id){
                		list($tipo, $id_collettivo) = explode('_', $ora->id_cliente);
	                	$acconti = CollettivoAcconti::where('id_collettivo', $id_collettivo)->where('id_cliente', $item->contact_id)->first();
	                	if($acconti){
	                		$saldo = $acconti->importo - $acconti->acconto1 - $acconti->acconto2;
	                	} else {
	                		$saldo = 0;
	                	}
                	} else {
                		$saldo = 0;
                	}               	
                	
                	
                	$lista_ore_coll = array();
                	
                	if($item->contact_id && in_array($item->ora_id, $ids_ore)){
                		
                		$coll_all = CollettivoAllievi::where('id_collettivo', substr($ora->id_cliente, 2))->where('partecipante', $item->contact_id)->get();
						
						foreach($coll_all as $ca){
							$lista_ore_coll[] = Ora::where('id_cliente', $ora->id_cliente)->where('id_maestro', $ca->id_maestro)->where('data', $ca->giorno)->first()->id;
						}	
											
                	} elseif($item->company_id && in_array($item->ora_id, $ids_ore)){
                		
                		$coll_all = CollettivoAllievi::where('id_collettivo', substr($ora->id_cliente, 2))->where('id_cliente', $item->company_id)->get();
						
						foreach($coll_all as $ca){
							$lista_ore_coll[] = Ora::where('id_cliente', $ora->id_cliente)->where('id_maestro', $ca->id_maestro)->where('data', $ca->giorno)->first()->id;
						}	
						
                	}
                	$lista_ore_coll_ok = array_unique($lista_ore_coll);
					
                	$voci = Item::where('invoice_id', $item->id)->whereIn('ora_id', $lista_ore_coll_ok)->get();
           	
                	foreach($voci as $voce){
                		$item_upd = Item::find($voce->id);
	                	$item_upd->invoice_id = $invoice->id;
	                	$item_upd->importo = $saldo;
	                	$item_upd->update();
                	}
                	
                } else {
                	$item_upd = Item::find($item->item_id);
                	$item_upd->invoice_id = $invoice->id;
                	$item_upd->update();
                }
                
                

                //3 step --> modificare invoice_id di invoice_ora in base all'Invoice appena creata
				if($ora && substr($ora->id_cliente, 0, 1) == 'C'){
					
					foreach($lista_ore_coll_ok as $ora_coll){
						\DB::table('invoice_ora')->where('ora_id', $ora_coll)->where('invoice_id', $item->id)
	                                ->update([
	                                            'invoice_id' =>$invoice->id
	                                    ]);	
					}
					
	            } else {
	            	$inv_ora_upd = \DB::table('invoice_ora')->where('ora_id', $item->ora_id)->where('invoice_id', $item->id)
	                                ->update([
	                                            'invoice_id' =>$invoice->id
	                                    ]);
	            }

                if(!in_array($item->id, $arr_id_invoice))
                {
                    array_push($arr_id_invoice,$item->id);
                }
                
            }

            //4 step -->cancella invoice ove possibile
            foreach ($arr_id_invoice as $id_invoice) {
                if(Item::where('invoice_id', $id_invoice)->count() == 0 && \DB::table('invoice_ora')->where('invoice_id', $id_invoice)->count() == 0)
                    Invoice::where('id', $id_invoice)->delete();
            }

            \DB::commit();
            $res['code'] = true;
            $res['message'] = 'Successo!';
            $res['data'] = null;
            $res['id'] = $invoice->id;
            echo json_encode($res);
        }
        catch(Exception $e) {
            $res['code'] = false;
            $res['message'] = 'Operation failed!!';
            $res['data'] = $e->getMessage();;
            echo json_encode($res);
    
        }
    }


    public function deleteAllItemsByInvoiceId(Request $request){
        \DB::beginTransaction();
        $res = array(); 
        try{
            $invoice_id = $request->invoice_id;
            $items = Item::where('items.invoice_id', $invoice_id)->delete();

            \DB::commit();
            $res['code'] = true;
            $res['message'] = 'Successo!';
            $res['data'] = null;
            echo json_encode($res);
        }
        catch(Exception $e) {
            \DB::rollback();
            $res['code'] = false;
            $res['message'] = 'Operation failed!!';
            $res['data'] = $e->getMessage();;
            echo json_encode($res);
    
        }
    }
    
    public function excel()
    {
        if(request()->input())
        {
            $invoices = Invoice::filter(request())->where('numero', '!=', '')->where('aperta', 0)->orderBy('data', 'DESC')->orderBy('numero', 'DESC');
        }
        else
        {
            $invoices = Invoice::where('data', date('Y-m-d'))->where('numero', '!=', '')->where('aperta', 0)->orderBy('data', 'DESC')->orderBy('numero', 'DESC')->with('company');
        }
        
        //return view('areaseb::core.accounting.invoices.excel', compact('invoices'));
        
        $filename = 'invoices-'.date('Y-m-d').'.xlsx';

        return \Excel::download(new \Areaseb\Core\Exports\InvoicesExport($invoices), $filename);
    }

}
