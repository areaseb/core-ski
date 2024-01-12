<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Hangout,Sede};
use App\User;
use \Carbon\Carbon;

class HangoutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ritrovi = Hangout::paginate(30);
        return view('areaseb::core.hangout.index', compact('ritrovi'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        return view('areaseb::core.hangout.create', compact('branches'));
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
            'luogo' => "required",
            'posto' => "required"
        ]);

        $ritrovo = new Hangout;
        $ritrovo->luogo = request('luogo');
        $ritrovo->posto = request('posto');
        $ritrovo->centro_costo = implode(",", request('branch_id')); 
        $ritrovo->save();

        return back()->with('message', 'Ritrovo Aggiunto');
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
    public function edit(Hangout $hangout)
    {
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        return view('areaseb::core.hangout.edit', compact('branches', 'hangout'));
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
            'luogo' => "required",
            'posto' => "required"
        ]);

        $ritrovo = Hangout::findOrFail($id);
        $ritrovo->luogo = request('luogo');
        $ritrovo->posto = request('posto');
        $ritrovo->centro_costo = implode(",", request('branch_id')); 
        $ritrovo->save();

		$ritrovi = Hangout::paginate(30);

        return view('areaseb::core.hangout.index', compact('ritrovi'))->with('message', 'Ritrovo Modificato');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Hangout $hangout)
    {
        $hangout->delete();    
        return 'done';    
       // return redirect(route('hangout.index'))->with('message', 'Ritrovo eliminato');
    }
}
