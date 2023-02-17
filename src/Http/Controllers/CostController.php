<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Category, Company, Cost, Expense, Stat};
use App\User;
use App\Fe\Actions\UploadIn;

class CostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Cost::filter(request());

        if(request('category_id'))
        {
            $selectExps = [''=>'']+Category::find(request('category_id'))->expenses()->pluck('nome', 'id')->toArray();
        }
        else
        {
            $selectExps = [''=>'']+Expense::pluck('nome', 'id')->toArray();
        }

        if(request('expense_id'))
        {
            $query = $query->where('expense_id', request('expense_id'));
        }

        $totale = Stat::TotaleQueryCosti((clone $query));
        $imponibile = Stat::ImponibileQueryCosti((clone $query));
        $imposte = Stat::ImposteQueryCosti((clone $query));
        $daSaldare = Stat::TotaleQueryCostiDaPagare((clone $query));
        $categories = Stat::CategoriaQueryCosti((clone $query));

        $costs = $query->paginate(50);

        $companies = [''=>'']+Company::supplier()->orderBy('rag_soc', 'ASC')->pluck('rag_soc', 'id')->toArray();
        $selectCats = [''=>'']+Category::categoryOf('Expense')->pluck('nome', 'id')->toArray();

        return view('areaseb::core.accounting.costs.index', compact('costs', 'totale', 'imponibile', 'imposte', 'categories', 'companies', 'selectCats', 'selectExps', 'daSaldare'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = [''=>'']+Company::supplier()->pluck('rag_soc', 'id')->toArray();
        $expenses = [''=>'']+Expense::pluck('nome', 'id')->toArray();
        $selectedCompany = '';
        $selectedExpense = '';
        return view('areaseb::core.accounting.costs.create', compact('companies', 'expenses', 'selectedCompany', 'selectedExpense'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(request(),[
            'numero' => 'nullable|unique:costs',
            'company_id' => 'required',
            'expense_id' => 'required',
            'data' => 'required',
            'imponibile' => 'required|numeric',
            'totale' => 'required|numeric',
        ]);

        //dd($request->except('_token', 'anno'));
        $cost = Cost::create($request->except('_token', 'anno'));

        if(is_null($request->data))
        {
            $anno = date('Y');
        }
        else
        {
            $arr = explode('/',$request->data);
            $anno = end($arr);
        }

        //$perc_iva = round(($request->ivato/$request->totale)*100);


        $cost->update(['anno' => $anno]);
        //$cost->update(['anno' => $anno, 'iva' => $perc_iva]);
        Cost::storeCostInCalendar($cost);

        return redirect(route('costs.index'))->with('message', 'Acquisto Creato');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Cost  $cost
     * @return \Illuminate\Http\Response
     */
    public function show(Cost $cost)
    {
        dd($cost);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Cost  $cost
     * @return \Illuminate\Http\Response
     */
    public function edit(Cost $cost)
    {
        $companies = []+Company::supplier()->pluck('rag_soc', 'id')->toArray();
        $expenses = []+Expense::pluck('nome', 'id')->toArray();
        $selectedCompany = $cost->company_id;
        $selectedExpense = $cost->expense_id;

        return view('areaseb::core.accounting.costs.edit', compact('cost', 'companies', 'expenses', 'selectedCompany', 'selectedExpense'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cost  $cost
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cost $cost)
    {
        $this->validate(request(),[
            'company_id' => 'required',
            'expense_id' => 'required',
            'data' => 'required',
            'imponibile' => 'required|numeric',
            'totale' => 'required|numeric',
        ]);

        $cost->update($request->except('_token', 'anno', 'previous'));

        if(is_null($request->data))
        {
            $anno = date('Y');
        }
        else
        {
            $arr = explode('/',$request->data);
            $anno = end($arr);
        }


        $perc_iva = 0;
        if($request->totale)
        {
            $perc_iva = round(($request->ivato/$request->totale)*100);
        }

        $cost->update(['anno' => $anno]);
        //$cost->update(['anno' => $anno, 'iva' => $perc_iva]);

        Cost::updateCostInCalendar($cost);

        return redirect(route('costs.index'))->with('message', 'Acquisto Aggiornato');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Cost  $cost
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cost $cost)
    {
        Cost::deleteCostFromCalendar($cost);
        $cost->delete();
        return 'done';
    }

//api/invoices/saldato - POST
    public function toggleSaldato(Request $request)
    {
        $cost = Cost::find($request->id);
            $cost->saldato = intval($request->saldato);
        $cost->save();

        Cost::updateCostInCalendar($cost);

        if(intval($request->saldato) === 1)
        {
            return "Ora il costo risulta pagato";
        }
        return 'Costo non saldato';
    }

//api/ta/costs/ - GET
    public function taindex()
    {
        return Cost::select('numero as name', 'id')->get();
    }


//api/costs/import - GET
    public function import()
    {
        return view('areaseb::core.accounting.costs.import');
    }

//api/costs/import - POST
    public function importProcess(Request $request)
    {

        if(config('core.modules')['fe'])
        {
            foreach($request->file as $file)
            {
                $class = new UploadIn($file);
                try
                {
                    $resp = $class->init();

                }
                catch(\Exception $e)
                {
                    return back()->with('error', 'Fattura non caricata');
                }
            }
        }
        if($resp == 'already done')
        {
            return back()->with('error', 'Fattura Non Caricata perché già presente');
        }
        return back()->with('message', 'Fattura Aggiunta');
    }

//api/costs/export?anno=2020&company=&mese=02&range=&saldato=&tipo= - GET
    public function exportXmlInZip()
    {
        if(request()->input())
        {
            $costs = Cost::filter(request())->get();
        }
        else
        {
            $costs = Cost::anno(date('Y'))->get();
        }

        foreach($costs as $cost)
        {
            if(!$cost->media()->exists()){
                dd($cost);
            }
        }

        $zip_file = 'costs.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach($costs as $cost)
        {
            if($cost->real_xml)
            {
                $zip->addFile($cost->real_xml, $cost->media()->xml()->first()->filename);
            }
        }
        $zip->close();

        return response()->download($zip_file);
    }


    public function updateSaldoForm(Request $request, Cost $cost)
    {
            $cost->data_saldo = $request->data_saldo ?? null;
            $cost->saldato = true;
        $cost->save();
        return back()->with('message', 'Costo Saldato');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Cost  $cost
     * @return \Illuminate\Http\Response
     */
    public function media(Cost $cost)
    {
        $model = $cost;
        return view('areaseb::core.accounting.costs.media', compact('model'));
    }
}
