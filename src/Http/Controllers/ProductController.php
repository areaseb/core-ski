<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Category, Company, Product, Setting};
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::categoryOf('Product')->orderBy('nome', 'asc')->get();
        if(request()->input())
        {
            $query = Product::filter(request());
        }
        else
        {
            $ids = [];$count = 0;
            foreach($categories as $category)
            {
                foreach($category->products()->pluck('id')->toArray() as $prod)
                {
                    $ids[$count] = $prod;
                    $count++;
                }
            }

            $query = Product::whereIn('id', $ids)->orderByRaw('FIELD (id, ' . implode(', ', $ids) . ') ASC')->with('categories');
        }

        $products = $query->paginate(50);

        return view('areaseb::core.accounting.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categorie = Category::categoryOf('Product')->pluck('nome', 'id')->toArray();
        $products = [];$select = [];
        foreach(Product::select('codice', 'nome', 'id')->get() as $p)
        {
            $text = $p->codice;
            if($p->nome)
            {
                $text .= " - ".$p->nome;
            }
            $products[$p->id] = $text;
            $select[] = ['text' => $text, 'id' => $p->id];
        }

        $selectedCategories[] = Category::where('nome', 'Da Categorizzare')->first()->id;
        return view('areaseb::core.accounting.products.create', compact('categorie', 'selectedCategories', 'products', 'select'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $inputs = null;
        if(isset($request->input))
        {
            if(isset($request->input['id']))
            {
                $inputs = [];
                foreach($request->input['id'] as $key => $input)
                {
                    $inputs[$key]['id'] = $input;
                    $inputs[$key]['qta'] = $request->input['qta'][$key];
                }
            }
        }

        $this->validate(request(), [
            'nome' => 'required'
        ]);

        $product = Product::create([
            'nome' => $request->nome,
            'descrizione' => $request->descrizione,
            'codice' => $request->codice,
            'prezzo' => $request->prezzo,
            'costo' => $request->costo,
            'periodo' => $request->periodo,
            'children' => $inputs,
            'perc_iva' => $request->perc_iva,
        ]);

        if(Schema::hasColumn('products', 'perc_agente'))
        {
            $product->update(['perc_agente' => isset($request->perc_agente) ? $request->perc_agente : 0.00]);
        }

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

                $product->categories()->save($category);
            }
        }

        $posts = $request->except('_method', '_token', 'table_length');
        foreach(Setting::ActiveLangs() as $lang)
        {
            if(isset($posts['name_'.$lang]))
            {
                $product->update(['name_'.$lang => $posts['name_'.$lang]]);
            }
            if(isset($posts['desc_'.$lang]))
            {
                $product->update(['desc_'.$lang => $posts['desc_'.$lang]]);
            }
        }

        return redirect(route('products.index'))->with('success', 'Prodotto Creato');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        dd($product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function edit(Product $product)
     {
         $categorie = Category::categoryOf('Product')->pluck('nome', 'id')->toArray();
         $selectedCategories = $product->categories()->pluck('id')->toArray();

         $products = [];$select = [];
         foreach(Product::select('codice', 'nome', 'id')->where('id', '!=', $product->id)->get() as $p)
         {
             $text = $p->codice;
             if($p->nome)
             {
                 $text .= " - ".$p->nome;
             }
             $products[$p->id] = $text;
             $select[] = ['text' => $text, 'id' => $p->id];
         }

         return view('areaseb::core.accounting.products.edit', compact('categorie', 'product', 'selectedCategories', 'products', 'select'));
     }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {

        $posts = $request->except('_method', '_token', 'table_length');
        foreach(Setting::ActiveLangs() as $lang)
        {
            if(isset($posts['name_'.$lang]))
            {
                $product->update(['name_'.$lang => $posts['name_'.$lang]]);
            }
            if(isset($posts['desc_'.$lang]))
            {
                $product->update(['desc_'.$lang => $posts['desc_'.$lang]]);
            }
        }

        $inputs = null;
        if(isset($request->input))
        {
            if(isset($request->input['id']))
            {
                $inputs = [];
                foreach($request->input['id'] as $key => $input)
                {
                    $inputs[$key]['id'] = $input;
                    $inputs[$key]['qta'] = $request->input['qta'][$key];
                }
            }
        }

        $this->validate(request(), [
            'nome' => 'required'
        ]);

        $product->update([
            'nome' => $request->nome,
            'descrizione' => $request->descrizione,
            'codice' => $request->codice,
            'prezzo' => $request->prezzo,
            'costo' => $request->costo,
            'periodo' => $request->periodo,
            'children' => $inputs,
            'perc_iva' => $request->perc_iva
        ]);

        if(Schema::hasColumn('products', 'perc_agente'))
        {
            $product->update(['perc_agente' => isset($request->perc_agente) ? $request->perc_agente : 0.00]);
        }

        $cat = [];
        if($request->categorie)
        {
            foreach($request->categorie as $nome)
            {
                if(is_numeric($nome))
                {
                    $category = Category::find($nome);
                    $cat[] = $category->id;
                }
                else
                {
                    $nome = ucfirst(strtolower($nome));
                    $category = Category::firstOrCreate(['nome' => $nome]);
                    $cat[] = $category->id;
                }
            }
            $product->categories()->sync($cat);
        }

        return redirect(route('products.index'))->with('success', 'Prodotto Aggiornato');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        try
        {
            $product->delete();
        }
        catch(\Exception $e)
        {
            return "Questo elemento è usato da un'altro modulo";
        }

        return 'done';
    }

    public function apiShow(Product $product)
    {
        return $product;
    }

    public function apiShowLocale(Product $product, $locale)
    {
        $name = 'name_'.$locale;
        $desc = 'desc_'.$locale;

        $nome = $product->nome;
        if($product->$name)
        {
            $nome = $product->$name;
        }

        $descrizione = $product->descrizione;
        if($product->$desc)
        {
            $descrizione = $product->$desc;
        }


        return [
            'id' => $product->id,
            'nome' => $nome,
            'descrizione' => $descrizione,
            'codice' => $product->codice,
            'prezzo' => $product->prezzo,
            'periodo' => $product->periodo,
            'children' => $product->children,
            'perc_iva' => $product->perc_iva,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at
        ];
    }

//api/products/{product}/children/{company_id} - GET
    public function apiShowChildren(Product $product, $company_id)
    {
        $company = Company::where('id', $company_id)->first();

        $locale = 'it';
        if($company->lingua != "it")
        {
            $locale = $company->lingua;
        }

        $i = config('app.iva');
        if(!is_null($company->exemption_id))
        {
            $i = $company->exemption->perc;
        }

        $sconto = 0;
        if($company->s1)
        {
            $sconto = $company->sconto;
        }

        $response = [];
        if(isset($product->children))
        {
            if(!is_null($product->children))
            {
                $count = 0;
                foreach ($product->children as $value)
                {

                    $p = Product::find($value['id']);

                    if(intval($i) == 22)
                    {
                        $i = $p->perc_iva;
                    }


                    if(config('app.sale_on_vat'))
                    {
                        $importo = ($p->prezzo * (1+($i/100))) * ((100-$sconto)/100);
                        $sivato = $importo * (1-($i/100));
                    }
                    else
                    {
                        $importo = $p->prezzo  * ((100-$sconto)/100);
                        $sivato = $importo * (1-($i/100));
                    }

                    $desc = 'desc_'.$locale;

                    if($p->$desc)
                    {
                        $desc = $p->$desc;
                    }
                    else
                    {
                        $desc = $p->descrizione;
                    }

                    $data['descrizione'] = $desc;
                    $data['id'] = 666666;
                    $data['importo'] = $importo;
                    $data['iva'] = $sivato;//fullprice- price ivato
                    $data['invoice_id'] = 666666;
                    $data['qta'] = $value['qta'];
                    $data['product_id'] = $value['id'];
                    $data['sconto'] = round($sconto, 2);
                    $data['product'] = $p;
                    $data['perc_iva'] = $i;
                    $response[$count] = $data;
                    $count++;
                }
                return $response;
            }
        }
        return [];
    }


//products/{product}/media
    public function media(Product $product)
    {
        $model = $product;
        return view('areaseb::core.accounting.products.media', compact('model'));
    }

}
