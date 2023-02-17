<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Deals\App\Models\{Deal, DealEvent, DealGenericQuote};
use App\Classes\Accounting\Requests\{EditInvoice, CreateInvoice};
use Areaseb\Core\Models\{Category, Company, Exemption, Invoice, InvoiceNotice, Item, Primitive, Product, Setting, Stat};
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
            $query = Invoice::filter(request())->orderBy('data', 'DESC')->orderBy('numero', 'DESC');
            $totQuery = Stat::TotaleQuery($query);
            $month_stats = null;
            $month_vat_stats = null;
            $graphData = null;
        }
        else
        {
            $query = Invoice::orderBy('data', 'DESC')->orderBy('numero', 'DESC')->with('company');
            $totQuery = Stat::TotaleQuery();
            $month_stats = Stat::TotaleMeseImponibile();
            $graphData = Stat::invoicePageGraph();
            $month_vat_stats = Stat::TotaleMeseVat();
        }
        $daSaldare = (clone $query)->where('saldato', 0)->count();
        $invoices = $query->paginate(50);
        return view('areaseb::core.accounting.invoices.index', compact('invoices', 'month_stats', 'month_vat_stats', 'graphData', 'totQuery', 'daSaldare'));
    }

//insoluti - GET
    public function insoluti()
    {
        if(request()->input())
        {
            $query = Invoice::filter(request())->orderBy('data', 'DESC')->orderBy('numero', 'DESC')->where('saldato', 0);
            $totQuery = Stat::TotaleQueryInsoluti($query);
            $month_stats = null;
        }
        else
        {
            $query = Invoice::where('saldato', 0)->orderBy('data', 'DESC')->orderBy('numero', 'DESC')->with('company');
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

        $companies = ['' => '']+Company::orderBy('rag_soc', 'ASC')->pluck('rag_soc', 'id')->toArray();
        $products = ['' => '']+Product::groupedOpt();
        $exemptions = ['' => '']+Exemption::where('connettore', 'Aruba')->orderBy('nome', 'ASC')->pluck('nome', 'id')->toArray();
        $selectedCompany = [];

        if(request('deal') && class_exists('Deals\App\Models\Deal'))
            $selectedCompany = [Deal::findOrFail(request('deal'))->company_id];

        $items = [];
        $invoices = ['' => '']+Invoice::whereDate('data', '>', Carbon::today()->subMonths(1)->format('Y-m-d'))->get()->pluck('company_official_name', 'id')->toArray();
        return view('areaseb::core.accounting.invoices.create', compact('companies', 'selectedCompany', 'products', 'exemptions', 'items', 'invoices', 'deals'));
    }

    public function store(Request $request)
    {
        $invoice = new Invoice;
            $invoice->tipo_doc = request('tipo_doc');
            $invoice->tipo = request('tipo');
            $invoice->numero = request('numero');
            $invoice->numero_registrazione = request('numero');
            $invoice->data = request('data');
            $invoice->data_registrazione = request('data');
            $invoice->company_id = request('company_id');
            $invoice->riferimento = request('riferimento');

            $invoice->pagamento = request('pagamento');
            $invoice->tipo_saldo = request('tipo_saldo');
            $invoice->data_saldo = request('data_saldo');
            $invoice->data_scadenza = $this->getDataScadenza($request);

            $invoice->spese = request('spese') ?? 0.00;
            $invoice->ritenuta = request('ritenuta') ?? 0.00;
            $invoice->rate = request('rate');
            $invoice->saldato = request('data_saldo') ? 1 : 0;
            $invoice->bollo = request('bollo') ?? 0.00;
            $invoice->bollo_a = request('bollo_a');

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

        return redirect(route('invoices.index'))->with('message', 'Fattura Creata');
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

        $companies = ['' => '']+Company::orderBy('rag_soc', 'ASC')->pluck('rag_soc', 'id')->toArray();
        $products = ['' => '']+Product::groupedOpt();
        $exemptions = ['' => '']+Exemption::orderBy('nome', 'ASC')->pluck('nome', 'id')->toArray();
        $selectedCompany = [$invoice->company_id];
        $items = $invoice->items()->with('product')->get();
        $invoices = Invoice::whereDate('data', '>', Carbon::today()->subMonths(6)->format('Y-m-d'))->where('id', '!=', $invoice->id)->get()->pluck('company_official_name', 'id')->toArray();
        return view('areaseb::core.accounting.invoices.edit', compact('invoice', 'companies', 'selectedCompany', 'products', 'exemptions', 'items', 'invoices', 'deals'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $invoice->tipo_doc = request('tipo_doc');
        $invoice->tipo = request('tipo');
        $invoice->numero = request('numero');
        $invoice->numero_registrazione = request('numero');
        $invoice->data = request('data');
        $invoice->data_registrazione = request('data');
        $invoice->company_id = request('company_id');
        $invoice->riferimento = request('riferimento');

        $invoice->pagamento = request('pagamento');
        $invoice->tipo_saldo = request('tipo_saldo');
        $invoice->data_saldo = request('data_saldo');
        $invoice->data_scadenza = $this->getDataScadenza($request);
        $invoice->bollo = request('bollo') ?? 0.00;
        $invoice->bollo_a = request('bollo_a');

        $invoice->spese = request('spese') ?? 0.00;
        $invoice->ritenuta = request('ritenuta') ?? 0.00;
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

        return redirect('invoices')->with('message', 'Fattura Aggiornata');
    }

    public function show(Invoice $invoice)
    {
        $company = $invoice->company;
        return view('areaseb::core.accounting.invoices.show', compact('invoice', 'company'));
    }

    public function destroy(Invoice $invoice)
    {
        if($invoice->status != 1)
        {
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
        if($id)
        {
            $element = Invoice::findOrFail($id);
            if($anno == $element->data->format('Y') && $element->tipo == $type)
            {
                return $element->numero;
            }
            elseif($anno == $element->data->format('Y') && ($type == 'U' || $type == 'A'))
            {
                return $element->numero;
            }

            if($type == 'R' || $type == 'D')
            {
                $maxR = Invoice::where('tipo', 'R')->whereYear('data', $anno)->max('numero');
                $maxD = Invoice::where('tipo', 'D')->whereYear('data', $anno)->max('numero');
                return max($maxR, $maxD)+1;
            }

            $maxF = Invoice::where('tipo', 'F')->whereYear('data', $anno)->max('numero');
            $maxU = Invoice::where('tipo', 'U')->whereYear('data', $anno)->max('numero');
            $maxA = Invoice::where('tipo', 'A')->whereYear('data', $anno)->max('numero');
            return max($maxF, $maxU, $maxA)+1;
        }

        if($type == 'P')
        {
            return Invoice::where('tipo', 'P')->whereYear('data', $anno)->max('numero') + 1;
        }

        if($type == 'R' || $type == 'D')
        {
            $maxR = Invoice::where('tipo', 'R')->whereYear('data', $anno)->max('numero');
            $maxD = Invoice::where('tipo', 'D')->whereYear('data', $anno)->max('numero');

            return max($maxR, $maxD)+1;
        }

        $maxF = Invoice::where('tipo', 'F')->whereYear('data', $anno)->max('numero');
        $maxU = Invoice::where('tipo', 'U')->whereYear('data', $anno)->max('numero');
        $maxA = Invoice::where('tipo', 'A')->whereYear('data', $anno)->max('numero');

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


            $imposte += $i->iva;
            $imponibile += ($i->importo*$i->qta*(1-$percSconto));

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
        if(config('core.modules')['fe'])
        {
            $settings = Setting::fe();
            if($settings->connettore == 'Fatture in Cloud')
            {
                $sender = new \App\FeiC\Invoice;
                $sender = $sender->init($invoice);
                if(isset($sender->success))
                {
                    if($sender->success)
                    {
                        $invoice->update(['status' => 7]);
                        return back()->with('message', 'Fattura inviata');
                    }
                }
                elseif($sender->error)
                {
                    $error = str_replace("\n", '', $sender->error);
                    return back()->with('error', $error);
                }
                dd($sender);

            }
            elseif($setting->connettore == 'Aruba')
            {
                // $sender = new \App\Classes\Fe\Actions\Send($invoice, Setting::fe());
                // $sender->init();
                return back()->with('message', 'Fattura NON inviata');
            }
            return back()->with('message', 'Fattura NON inviata');
        }
        return back()->with('message', 'Non hai il modulo per la Fattura Elettronica');
    }


    public function getDataScadenza($request)
    {
        $deadlines = config('invoice.payment_types_dead_lines');
        $q = $deadlines[$request->pagamento];
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
        $new->tipo = 'F';
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

}
