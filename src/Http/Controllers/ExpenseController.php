<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Expense,Category};

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::categoryOf('Expense')->orderBy('nome', 'asc')->get();
        $ids = [];$count = 0;
        foreach($categories as $category)
        {
            foreach($category->expenses()->where('nome', '!=', 'Da Categorizzare')->pluck('id')->toArray() as $prod)
            {
                $ids[$count] = $prod;
                $count++;
            }
        }

        $expenses  = Expense::whereIn('id', $ids)->orderByRaw('FIELD (id, ' . implode(', ', $ids) . ') DESC')->with('categories')->paginate(50);
        return view('areaseb::core.accounting.expenses.index', compact('expenses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categorie = Category::categoryOf('Expense')->pluck('nome', 'id')->toArray();
        $selectedCategories = [1];
        return view('areaseb::core.accounting.expenses.create', compact('categorie', 'selectedCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'nome' => 'required',
            'prezzo' => 'nullable|numeric'
        ]);

        $expense = Expense::create([
            'nome' => $request->nome,
            // 'prezzo' => $request->prezzo
        ]);

        if($request->categorie)
        {
            foreach($request->categorie as $nome)
            {
                if(is_numeric($nome))
                {
                    $category = Category::find($nome);
                }
                else
                {
                    $nome = ucfirst(strtolower($nome));
                    $category = Category::firstOrCreate(['nome' => $nome]);
                }

                $expense->categories()->save($category);
            }
        }

        return redirect(route('expenses.index'))->with('message', 'Costo Creato');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function show(Expense $expense)
    {
        dd($expense);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function edit(Expense $expense)
    {
        $categorie = Category::categoryOf('Expense')->pluck('nome', 'id')->toArray();
        $selectedCategories = $expense->categories()->pluck('id')->toArray();
        return view('areaseb::core.accounting.expenses.edit', compact('expense', 'categorie', 'selectedCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Expense $expense)
    {
        $this->validate(request(), [
            'nome' => 'required'
        ]);

        $expense->nome = $request->nome;
        // $expense->prezzo = $request->prezzo;
        $expense->save();

        if(!$this->hasWords($request->categorie))
        {
            $expense->categories()->sync($request->categorie);
        }
        return redirect(route('expenses.index'))->with('message', 'Costo Aggiornato');
    }

    private function hasWords($arr)
    {
        foreach($arr as $nome)
        {
            if(!is_numeric($nome))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Expense  $expense
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return 'done';
    }

//expenses/modify - GET
    public function modifyCategories()
    {
        $categories = Category::categoryOf('Expense')->get();
        return view('areaseb::core.accounting.expenses.modify', compact('categories'));
    }

}
