<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\Counter;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CounterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        if(request('utente')) {
        	$user_id = request('utente');
        } else {
        	$user_id = auth()->user()->id;
        }  
        
        if(request('mese')) {
        	$mese = request('mese');
        } else {
        	$mese = date('m');
        } 
        
        if(request('anno')) {
        	$anno = request('anno');
        } else {
        	$anno = date('Y');
        }  
        
        $counter = new Counter(); 
        $hours = Counter::whereMonth('in', $mese)->whereYear('in', $anno)->where('user_id', $user_id)->pluck('out', 'in')->toArray();
        
        $users[''] = '';
        $users += User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        
        $user_name = User::where('id', $user_id)->with('contact')->get()->pluck('contact.fullname')->toArray();
        
        return view('areaseb::core.accounting.counters', compact('hours', 'users', 'user_name'));
    }
    
    public function in()
    {
        $user = auth()->user();
        
        // controllo che non ci siano giornate aperte
        $checker = Counter::where('user_id', $user->id)->whereNotNull('in')->whereNull('out')->pluck('id')->toArray();
        
        if(count($checker) == 0){
        
	        $counter = new Counter();
			$counter->in = date('Y-m-d H:i:s');
			$counter->out = null;
			$counter->user_id = $user->id;
			$counter->save();
	        
	        return redirect(route('counters'))->with('message', 'Ingresso registrato correttamente');
	        
	    } else {
	    	return redirect(route('counters'))->with('error', "Ingresso gia' effettuato");
	    }
    }
            
    public function out()
    {
        $user = auth()->user();
        
        $query = DB::table('counters')->where('user_id', $user->id)->whereNotNull('in')->whereNull('out')->update(['out' => date('Y-m-d H:i:s')]);
        
        return redirect(route('counters'))->with('message', 'Uscita registrata correttamente');
    }          
}
