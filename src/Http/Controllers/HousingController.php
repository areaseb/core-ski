<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Housing,Sede};
use App\User;
use \Carbon\Carbon;

class HousingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $alloggi = Housing::paginate(30);

        return view('areaseb::core.housing.index', compact('alloggi'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    { 
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        return view('areaseb::core.housing.create', compact('branches'));
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
            'hotel' => "required"
        ]);

        $alloggio = new Housing;
        $alloggio->luogo = request('luogo');
        $alloggio->hotel = request('hotel');
        $alloggio->centro_costo = implode(",", request('branch_id')); 
        $alloggio->save();

        return back()->with('message', 'Alloggio Aggiunto');
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
    public function edit(Housing $housing)
    {
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        return view('areaseb::core.housing.edit', compact('branches', 'housing'));
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
            'hotel' => "required"
        ]);

        $alloggio = Housing::findOrFail($id);
        $alloggio->luogo = request('luogo');
        $alloggio->hotel = request('hotel');
        $alloggio->centro_costo = implode(",", request('branch_id')); 
        $alloggio->save();

		$alloggi = Housing::paginate(30);

        return view('areaseb::core.housing.index', compact('alloggi'))->with('message', 'Alloggio Modificato');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Housing $housing)
    {
        $housing->delete();    
        return 'done';      
        //return redirect(route('housing.index'))->with('message', 'Alloggio eliminato');
    }
}
