<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
//use App\User;
use Areaseb\Core\Models\{Calendar, Client, Contact, Company, Event, Report, Setting, Invoice, Cost};


class PagesController extends Controller
{
    public function home()
    {
        $aziendeLables = '';$aziendeData = '';
        foreach(Client::company()->get() as $type)
        {
            $aziendeLables .= '"'.$type->nome.'",';
            $aziendeData .= $type->companies()->count().',';
        }
        $aziende = (object) [
            'labels' => substr($aziendeLables, 0, -1),
            'data' => substr($aziendeData, 0, -1),
            'total' => Company::count()
        ];

        $contattiLables = '';$contattiData = '';
        foreach(Client::contact()->get() as $type)
        {
            $contattiLables .= '"'.$type->nome.'",';
            $contattiData .= Contact::whereIn('company_id', $type->companies()->pluck('id'))->count().',';
        }
        $contatti = (object) [
            'labels' => substr($contattiLables, 0, -1),
            'data' => substr($contattiData, 0, -1),
            'total' => Contact::count()
        ];
        $view = Setting::dashboard();

    // imponibili per mese

        for($m = 1; $m <= date('m'); $m++)
        {
        	$imponibili_mese = Invoice::where('data', 'like', date('Y').'-'.str_pad($m, 2, "0", STR_PAD_LEFT).'-%')->where('tipo', '<>', 'P')->where('tipo', '<>', 'D')->where('aperta', 0)->get();
        	$imponibile_mese = 0;

        	foreach($imponibili_mese as $im)
        	{
        		if($im->tipo == "A"){
        			$imponibile = -1 * abs($im->imponibile);
        		} else {
        			$imponibile = $im->imponibile;
        		}
        		$imponibile_mese += $imponibile;
        	}
        	$array_imponibile[] = $imponibile_mese;
        }

    // costi per mese

        for($m = 1; $m <= date('m'); $m++)
        {
        	$costo_mese = Cost::where('data', 'like', date('Y').'-'.str_pad($m, 2, "0", STR_PAD_LEFT).'-%')->where('anno', date('Y'))->sum('imponibile');
        	$array_costo[] = $costo_mese;
        }

    //utili per mese

    	for($m = 0; $m <= date('m')-1; $m++){

			$array_utile[] = $array_imponibile[$m] - $array_costo[$m];

		}

	// fatturato anno prima

		$anno_prima = date('Y')-1;
		$imponibile_precedente = 0;

		for($m = 1; $m <= date('m'); $m++)
        {
        	$imponibili_mese = Invoice::where('data', 'like', $anno_prima.'-'.str_pad($m, 2, "0", STR_PAD_LEFT).'-%')->where('tipo', '<>', 'P')->where('tipo', '<>', 'D')->where('aperta', 0)->get();
        	$imponibile_mese = 0;

        	foreach($imponibili_mese as $im)
        	{
        		if($im->tipo == "A"){
        			$imponibile = -1 * abs($im->imponibile);
        		} else {
        			$imponibile = $im->imponibile;
        		}
        		$imponibile_mese += $imponibile;
        	}
        	$imponibile_precedente += $imponibile_mese;
        }

    // costi anno prima

        $costo_precedente = Cost::where('data', 'like', $anno_prima.'-%-%')->where('anno', $anno_prima)->sum('imponibile');

    // utile anno prima

    	$utile_precedente = $imponibile_precedente - $costo_precedente;

        return view('areaseb::welcome', compact('aziende', 'contatti', 'view', 'array_imponibile', 'array_costo', 'array_utile', 'imponibile_precedente', 'costo_precedente', 'utile_precedente'));
    }


    public function showCalendar()
    {
        $contacts= Contact::all()->pluck('fullname' ,'id')->toArray();
        $companies[''] = '';
        $companies += Company::pluck('rag_soc', 'id')->toArray();
        $users = User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        $userEvents = Event::where('user_id', auth()->user()->id)->select('title', 'starts_at as start', 'ends_at as end' ,'allday', 'backgroundColor', 'backgroundColor as borderColor')->get();

        return view('areaseb::core.calendars.show', compact('users', 'companies', 'contacts', 'userEvents'));
    }

//faqs - GET
    public function faqs()
    {

        $videos = [
            '00 Intro' => 'dG63-gde5dk',
            '00 Settings'=>'BSgmgMNVzhg',
            '01 Aziende'=>'u77ZoYqYTF0',
            '02 Calendari'=>'t4eCfGSBr6M',
            '03 Trattative'=>'8uPlPcOtFuU',
            '04 Preventivi killer'=>'JemuP_xZyMU',
            '05 Conferme ordine'=>'7yFKQoEeXIk',
            '06 Statistiche'=>'5DukwSedoRA',
            '07 Progetti'=>'xkkV9fJqeU4',
            '08 Newsletter'=>'ZSivAb5yZmc',
            '09 Utenti e ruoli'=>'nHTYDfrJ0HM',
            '10 Agenti'=>'M54qJyMpZFs',
            '11 Fatture'=>'huw8GeUis3Q',
            '12 Rinnovi'=>'0i2iY-LUQY0',
            '13 GMap'=>'sTtxnRttOz8',
            '14 Testimonials' => 'b1oPrR_Dw-I'
        ];


        return view('areaseb::core.faqs.index', compact('videos'));
    }


//faqs/{id} - GET
    public function faq($id)
    {
        return view('areaseb::core.faqs.show');
    }



//logout - POST
    public function logout()
    {
        \Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect(route('login'));
    }
}
