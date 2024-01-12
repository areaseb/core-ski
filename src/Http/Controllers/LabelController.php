<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Label};
use App\User;
use \Carbon\Carbon;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $labels = Label::paginate(30);
        return view('areaseb::core.labels.index', compact('labels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('areaseb::core.labels.create');
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
            'nome' => "required",
        ]);

        $label = new Label;
        $label->nome = request('nome');
        $label->colore = request('colore');
        $label->save();

        return back()->with('message', 'Label Aggiunta');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Label $label)
    {
        return view('areaseb::core.labels.edit', compact('label'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate(request(),[
            'nome' => "required",
        ]);

        $label = Label::findOrFail($id);
        $label->nome = request('nome');
        $label->colore = request('colore');
        $label->save();
		
		$labels = Label::paginate(30);
        return view('areaseb::core.labels.index', compact('labels'))->with('message', 'Label Aggiunta');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Label $label)
    {
        try
        {
            $label->delete();    
            return 'done';    
            //return redirect(route('labels.index'))->with('success', 'Segnaposto cancellato con successo!');
        }
        catch(\Exception $e)
        {
            return redirect(route('labels.index'))->with('error', 'Eliminazione fallita.');
            //return "Questo elemento è usato da un'altro modulo";
        }
    }
}
