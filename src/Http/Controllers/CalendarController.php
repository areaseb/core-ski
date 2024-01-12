<?php

namespace Areaseb\Core\Http\Controllers;

use Areaseb\Core\Models\{Calendar, Label, Setting, Company, Contact, Event, Ora, Product, Collettivo, CollettivoAllievi, Availability, Sede, Housing, Hangout, Invoice, Item, Specialization, Master, Branch, ContactBranch, InvoiceOra};
use App\User;
use Illuminate\Http\Request;
use Cookie;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()->hasRole('super'))
        {
            $calendars = Calendar::all();
        }
        else
        {
            $calendars = auth()->user()->getAllCalendars();
        }

        return view('areaseb::core.calendars.index', compact('calendars'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
		$users = User::where('id','!=', auth()->user()->id)->with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        return view('areaseb::core.calendars.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $calendar = Calendar::create(['user_id' => auth()->user()->id, 'nome' => $request->nome, 'privato' => $request->privato, 'token' => \Str::random(33)]);
        foreach($request->user_id as $user){
        	\DB::table('calendars_user')->insert(['calendar_id' => $calendar->id, 'user_id' => $user]);
        }
        return redirect('calendars')->with('message', 'Calendario Creato');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function show(Calendar $calendar)
    {
		$contacts = Contact::all()->pluck('fullname' ,'id')->toArray();
		$companies = [''=>'']+Company::pluck('rag_soc', 'id')->toArray();
		$luoghi_companies = Company::pluck('address', 'id')->toArray();
		$users = User::where('id','!=', auth()->user()->id)->with('contact')->get()->pluck('contact.fullname', 'id')->toArray();		
		return view('areaseb::core.calendars.show', compact('users', 'companies', 'contacts', 'calendar', 'luoghi_companies'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function edit(Calendar $calendar)
    {
    	$users = User::where('id','!=', auth()->user()->id)->with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
    	$selected = \DB::table('calendars_user')->where('calendar_id', $calendar->id)->pluck('user_id')->toArray();
    	
        return view('areaseb::core.calendars.edit', compact('calendar', 'users', 'selected'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Calendar $calendar)
    {
        $calendar->nome = $request->nome;
        $calendar->privato = $request->privato;
        $calendar->save();
        
        \DB::table('calendars_user')->where('calendar_id', $calendar->id)->delete();
        foreach($request->user_id as $user){
        	\DB::table('calendars_user')->insert(['calendar_id' => $calendar->id, 'user_id' => $user]);
        }
        return redirect('calendars')->with('message', 'Calendario Aggiornato');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Calendar  $calendar
     * @return \Illuminate\Http\Response
     */
    public function destroy(Calendar $calendar)
    {
        foreach($calendar->events as $event)
        {
            if($event->eventable_id)
            {

                if(\DB::table('deal_events')->where('dealable_id', $event->eventable->id)->where('dealable_type', get_class($event->eventable))->exists())
                {
                    \DB::table('deal_events')->where('dealable_id', $event->eventable->id)->where('dealable_type', get_class($event->eventable))->delete();
                }

                $event->eventable->delete();
            }
            $event->delete();
        }
		
		\DB::table('calendars_user')->where('calendar_id', $calendar->id)->delete();
        $calendar->delete();
        return 'Calendario Eliminato';
    }

//calendars/overlayed
    public function overlayed()
    {
        if(request()->input())
        {
            $ids = request('ids');
            $arrIds = explode('-', $ids);

            if(count($arrIds) > 1)
            {
                $contacts= Contact::all()->pluck('fullname' ,'id')->toArray();
                $companies = Company::pluck('rag_soc', 'id')->toArray();
        		$luoghi_companies = Company::pluck('address', 'id')->toArray();
                $users = User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
                $calendar_ids = request('ids');
                $calendarIdName = Calendar::whereIn('id', $arrIds)->pluck('nome', 'id')->toArray();
                return view('areaseb::core.calendars.overlayed', compact('users', 'companies', 'contacts', 'calendar_ids', 'calendarIdName', 'luoghi_companies'));
            }
            else
            {
                return redirect('calendars/'.$arrIds[0]);
            }
        }

        return redirect('calendars/'.auth()->user()->default_calendar->id)->with('message', 'Il tuo calendario di default');
    }

//calendars/bind - GET
    public function bind()
    {
        if(auth()->user()->hasRole('super'))
        {
            $calendars = Calendar::all();
        }
        else
        {
            $calendars = auth()->user()->calendars;
        }
        return view('areaseb::core.calendars.bind', compact('calendars'));
    }


    public function setCookie(Request $request)
    {
    	Cookie::forget('calendar-cookie');
        Cookie::forever('calendar-cookie', strval($request->type));
        return response()->json(['Cookie set successfully.'])->cookie(
            'calendar-cookie', strval($request->type)
        );
    }

	// New method
	function loadIndex(Request $request){
		// Redirect to legacy function
		if(isset($_GET['old']))
			return $this->old_loadIndex($request);

		/**
		 * Timeline Calendar: display calendar of available teachers for current branch (sede)
		 */
		// Get day from request or set to today. YYYY-MM-DD
		if($request->has('day')) {
			$day_raw = explode(' ', str_replace('/', '-', $request->day))[0];

			$day = \Carbon\Carbon::createFromFormat('d-m-Y', $day_raw);
		} else {
			$day = \Carbon\Carbon::today();
		}

		// Get current branch	
		//$branch_id = ContactBranch::where('contact_id', auth()->user()->contact->id)->pluck('branch_id');
		//$branch_id = auth()->user()->contact->branchContact()->branch_id;

		// Get available teachers from current branch
		//$branch_contacts_ids = ContactBranch::where('branch_id', $branch_id)->pluck('id')->toArray();
		$branch_contacts_ids = Contact::query()->where('contact_type_id', 3)->pluck('id')->toArray();
		
		if(auth()->user()->hasRole('super')){
			$branch_id = null;
		} elseif(auth()->user()->hasRole('Maestro')) {
			if(Availability::whereDate('data_start', '<=', $day)->whereDate('data_end', '>=', $day)->where('contact_id', auth()->user()->contact->id)->exists()){
				$branch_id = Availability::whereDate('data_start', '<=', $day)->whereDate('data_end', '>=', $day)->where('contact_id', auth()->user()->contact->id)->first()->branch_id;
			} else {
				$branch_id = auth()->user()->contact->branchContact()->branch_id;
			}
		} else {
			$branch_id = auth()->user()->contact->branchContact()->branch_id;
		}
		
		$teachers = Master::whereHas('availability', function($q) use($day, $branch_id) {
			if(auth()->user()->hasRole('super')){
				$q->whereDate('data_start', '<=', $day)->whereDate('data_end', '>=', $day);
			} else {
				$q->whereDate('data_start', '<=', $day)->whereDate('data_end', '>=', $day)->where('branch_id', $branch_id);
			}
		})->get();	//->whereIn('contact_id', $branch_contacts_ids)
	
		// Get hours registered
		$teachers_ids = $teachers->pluck('id')->toArray();
		$hours = Ora::whereIn('id_maestro', $teachers_ids)->whereDate('data', '>=', $day)->whereDate('data', '<=', $day)->get();

		$teachers_hours = [];

		// Process hours
		foreach($hours as $key=>$hour) {
			// Count hours to teacher. Ignore type Label
/*			if($hour->ora_in != '' && $hour->ora_out != '' && substr($hour->id_cliente, 0, 1) != 'L') {
				$hour_a = \Carbon\Carbon::createFromFormat('H:i:s', $hour->ora_in);
				$hour_b = \Carbon\Carbon::createFromFormat('H:i:s', $hour->ora_out);

				if(!isset($teachers_hours[$hour->id_maestro]))
					$teachers_hours[$hour->id_maestro] = 0;

				$teachers_hours[$hour->id_maestro] += $hour_a->diffInSeconds($hour_b);
			}*/
			
			// Count hours to teacher. Ignore type Label
			$hour_start = \Carbon\Carbon::createFromFormat('H:i:s', '08:00:00');

			if($hour->ora_in != '' && $hour->ora_out != '' && substr($hour->id_cliente, 0, 1) != 'L') {
				$hour_a = \Carbon\Carbon::createFromFormat('H:i:s', $hour->ora_in);
				$hour_b = \Carbon\Carbon::createFromFormat('H:i:s', $hour->ora_out);

				$array_hour = $hour_a->diffInSeconds($hour_start) / 60 / 30;

				$hours_difference = ($hour_a->diffInSeconds($hour_b) / 60 / 30) + $array_hour;

				if(!isset($teachers_hours[$hour->id_maestro]))
					$teachers_hours[$hour->id_maestro] = [];

				for($n=$array_hour;$n<$hours_difference;$n+=0.5) {
					$teachers_hours[$hour->id_maestro][$n] = 30;
				}
			}

			// Set default title
			$hour->event_title = '';

			if($hour->ora_out != '') {		
				$post_icons = '';		

				// Check state of invoice
				if($hour->id_cliente == "") {
					if($hour->invoice) {
						if($hour->invoice->aperta == 1)
						{
							$imgAperta = asset('img/attenzione.png');
							$aperta = " <img src=\"$imgAperta\" align=\"center\" border=\"0\" class=\"fc-event-icon\"> ";
	
							$hour->event_title .= $aperta;
						}
					}

					// if ($hour->ora_out == '') { =
					$hour->event_title .= ' <img src="../img/attenzione.png" align="top" border="0" height="30%" class="fc-event-icon"> <a tabindex="1" href="#" onClick="openModOra(' . $hour->id . ')" title="Modifica ora" style="color: white;"><b>ATTENZIONE !!!<br>Cliente non impostato !</b></a> <img src="../img/attenzione.png" align="top" border="0" height="30%" class="fc-event-icon"> ';
				} else {
					list($tipo, $id_cliente) = explode('_', $hour->id_cliente);

					/*
					if($hour->invoice) {
						if($hour->invoice->aperta == 1 && $tipo != 'L') {
							$imgAperta = asset('img/aperta.png');
							$aperta = "<img src=\"$imgAperta\" align=\"center\" border=\"0\" class=\"fc-event-icon\"> ";

							$hour->event_title .= $aperta;
						}
					}
					*/

					if ($tipo == 'C') {
						$id_collettivo = substr($hour->id_cliente, 2);

						$allievi_c = CollettivoAllievi::where('id_collettivo', $id_collettivo)->where('giorno', $day->format('Y-m-d'))->where('id_maestro', $hour->id_maestro)->count();
						$collettivo = Collettivo::find($id_collettivo);
						
						$hour->event_title .= '<a tabindex="1" href="/collective/'.$id_collettivo.'" title="Modifica collettivo">'.substr($hour->ora_in, 0, -3).' Collettivo ' . $collettivo->nome . ' (' . $allievi_c .')</a> ';
					} elseif ($tipo == 'T') {
						$contact = Contact::find($id_cliente);

						if($contact != null){
							$invoice_open = false;

							if(!is_null($hour->invoice))
								if($hour->invoice->aperta == 1)
									$invoice_open = true;
									
							if(!$hour->invoice)
								$invoice_open = true;
								
							$contact_name = ($contact->nickname == '') ? htmlentities($contact->cognome, ENT_QUOTES) : htmlentities($contact->nickname, ENT_QUOTES);
							$hour->event_title .= '<a tabindex="1" href="#" onClick="openModOra(' . $hour->id . ')" title="Modifica ora">'.substr($hour->ora_in, 0, -3).' ';
							
							if($invoice_open || is_null($hour->invoice))
								$hour->event_title .= '<b>' . $contact_name . '</b></a>';
							else
								$hour->event_title .= $contact_name . '</a>';

							if($invoice_open || is_null($hour->invoice)) {
								$imgAperta = asset('img/attenzione.png');
								$aperta = " <img src=\"$imgAperta\" align=\"center\" border=\"0\" class=\"fc-event-icon\"> ";

								$hour->event_title .= $aperta;
							}
							
							$parole = explode(' ', $hour->ritrovo);
							$ritrovo = "";
							foreach ($parole as $w) {
							  $ritrovo .= mb_substr($w, 0, 1);
							}
							$hour->event_title .= " ($ritrovo)";
							
							if($hour->richiesto == "S"){
								$imgRichiesto = asset('img/richiesto.png');
								$hour->event_title .= ' <img src="' . $imgRichiesto . '" align="center" border="0" class="fc-event-icon"> ';
							}
							
							if(!is_null($contact->isDisabled($contact->id))){
								$imgDisabili = asset('img/disabili.png');
								$hour->event_title .= ' <a tabindex="1" href="/contacts/' . substr($hour->id_cliente, 2) . '" title="Visualizza scheda disabile"><img src="' . $imgDisabili . '" align="center" border="0" class="fc-event-icon"></a>';
							}

							if($contact->cod_fiscale == ""){
								$imgda_compilare = asset('img/da_compilare.png');
								$hour->event_title .= ' <a tabindex="1" href="/contacts/' . substr($hour->id_cliente, 2) . '" title="Modifica cliente"><img src="' . $imgda_compilare . '" align="center" border="0" class="fc-event-icon"></a>';
							}	
							
							if($contact->cellulare != "")
								$post_icons .= '<br /><a tabindex="1" href="tel:' . $contact->cellulare . '">' . $contact->cellulare . '</a>';
						}
					} elseif ($tipo == 'Y') {
						$contact = Company::find($id_cliente);

						//$contact->rag_soc = htmlentities($contact->rag_soc, ENT_QUOTES);
						$contact_name = ($contact->nickname == '') ? htmlentities($contact->rag_soc, ENT_QUOTES) : htmlentities($contact->nickname, ENT_QUOTES);

						if($contact != null) {			
							$invoice_open = false;

							if($hour->invoice)
								if($hour->invoice->aperta == 1)
									$invoice_open = true;
									
							if(!$hour->invoice)
								$invoice_open = true;

							$hour->event_title .= '<a tabindex="1" href="#" onClick="openModOra(' . $hour->id . ')" title="Modifica ora">'.substr($hour->ora_in, 0, -3).' ';
							
							if ($invoice_open)
								$hour->event_title .= '<b>' . $contact_name . '</b></a>';
							else
								$hour->event_title .= $contact_name . '</a>';

							if($invoice_open) {
								$imgAperta = asset('img/attenzione.png');
								$aperta = " <img src=\"$imgAperta\" align=\"center\" border=\"0\" class=\"fc-event-icon\"> ";

								$hour->event_title .= $aperta;
							}
							
							$parole = explode(' ', $hour->ritrovo);
							$ritrovo = "";
							foreach ($parole as $w) {
							  $ritrovo .= mb_substr($w, 0, 1);
							}
							$hour->event_title .= " ($ritrovo)";

							if($hour->richiesto == "S"){
								$imgRichiesto = asset('img/richiesto.png');
								$hour->event_title .= " <img src=\"$imgRichiesto\" align=\"center\" border=\"0\" class=\"fc-event-icon\"> ";
							}

							if($contact->cf == ""){
								$imgda_compilare = asset('img/da_compilare.png');
								$hour->event_title .= "<a tabindex=	\'1\' href=\"/contacts/$id_cliente\" title=\"Modifica cliente\"><img src=\"$imgda_compilare\" align=\"center\" border=\"0\"></a>";
							}

							if($contact->mobile == ""){
								$post_icons .= '<br /><a tabindex="1" href="tel:' . $contact->phone . '">' . $contact->phone . '</a>';
							} else {
								$post_icons .= '<br /><a tabindex="1" href="tel:' . $contact->mobile . '">' . $contact->mobile . '</a>';
							}
						}
					} elseif ($tipo == 'L') {
						$label = Label::find($id_cliente);

						$hour->special_color = $label->colore;
						$hour->event_title .= '<a tabindex="1" href="#" onClick="openModOra(' . $hour->id . ')" title="Modifica ora" style="color: #fff"><b>'.substr($hour->ora_in, 0, -3).' ' . $label->nome . '</b></a>';
					}
				}

				$lista_specialita_ora = explode(",", $hour->specialita);

				if(in_array("1", $lista_specialita_ora)) {
					$imgBiberon = asset('img/biberon.png');
					$bambino = " <img src=\"$imgBiberon\" align=\"center\" border=\"0\" class=\"fc-event-icon\"> ";

					$hour->event_title .= $bambino;
				}
				if(in_array("16", $lista_specialita_ora))
				{
					$imgDollaro = asset('img/dollaro.png');
					$dollaro = " <img src=\"$imgDollaro\" align=\"center\" border=\"0\" class=\"fc-event-icon\"> ";

					$hour->event_title .= $dollaro;
				}
				if(in_array("17", $lista_specialita_ora))
				{
					$imgTelefono = asset('img/telefono.png');
					$telefono = " <img src=\"$imgTelefono\" align=\"center\" border=\"0\" class=\"fc-event-icon\"> ";

					$hour->event_title .= $telefono;
				}
				if(in_array("18", $lista_specialita_ora))
				{
					$imgOraAperta = asset('img/aperta.png');
					$oraAperta = " <img src=\"$imgOraAperta\" align=\"center\" border=\"0\" class=\"fc-event-icon\"> ";

					$hour->event_title .= $oraAperta;
				}
				if($hour->ritrovo == "Esterno - Fuori sede" || $hour->ritrovo == "Esterno") {
					$imgfuori_sede = asset('img/fuori_sede.png');
					$ritrovo = " <img src=\"$imgfuori_sede\" align=\"center\" border=\"0\" class=\"fc-event-icon\"> ";

					$hour->event_title .= $ritrovo;
				}

				$hour->event_title .= $post_icons;
			}

			if($hour->ora_in != ""){
				if($hour->event_title == "") {
					/*
					$ora = explode(":",$ora);
					$dateFromModal = strval($g).'/'.strval($m).'/'.strval($a).'/'.strval($ora[0]).'/'.strval($ora[1]);
					$nome_cliente = "<a href=\"#\" onClick=\"openAddOraPreimpostata($ora[0],$ora[1],$id_maestro)\"  title=\"Aggiungi ora\"><i align=\"top\" class=\"fa fa-plus plus-icon-tab text-success\"></i></a>";
					$align = "center";
					*/
				}
			} else {
				/*
				$availability = Availability::where('contact_id',$id_maestro)->where('data_start','<=', $giorno_query)->where('data_end','>=', $giorno_query)->first();
				$id_disp = $availability->id;

				if($id_disp != ""){
					$ora = explode(":",$ora);
					$dateFromModal = strval($g).'/'.strval($m).'/'.strval($a).'/'.strval($ora[0]).'/'.strval($ora[1]);
					$result= $result."<td align=\"center\" valign=\"top\" width=\"4%\">
					<a href=\"#\" onClick=\"openAddOraPreimpostata($ora[0],$ora[1],$id_maestro)\"  title=\"Aggiungi ora\"><i align=\"top\" class=\"fa fa-plus plus-icon-tab text-success\"></i></a></td>";
				} else {
					$result= $result."<td bgcolor=\"black\">&nbsp;</td>";
				}

				$id_disp = "";
				*/
			}
		}

		// Set teacher's total hours
		foreach($teachers as $key=>$teacher) {
			if(!isset($teachers_hours[$teacher->id]))
				$teachers[$key]['total_hours'] = '0';
			else
				//$teachers[$key]['total_hours'] = $teachers_hours[$teacher->id] / 60 / 60;
				$teachers[$key]['total_hours'] = count($teachers_hours[$teacher->id]) / 2;
		}			
		
        if(auth()->user()->hasRole('super')){
	        $alloggi = Housing::orderby('luogo', 'ASC')->get();
	        $ritrovi = Hangout::orderby('luogo', 'ASC')->get();
	    } else {
	    	$branch_id = auth()->user()->contact->branchContact()->branch_id;
	    	$alloggi = Housing::where('centro_costo', 'like', '%'.$branch_id.'%')->orderby('luogo', 'ASC')->get();
	        $ritrovi = Hangout::where('centro_costo', 'like', '%'.$branch_id.'%')->orderby('luogo', 'ASC')->get();
	    }
	    
		$collettivi = Collettivo::orderby('nome', 'ASC')->get();
        $labels = Label::orderby('nome', 'ASC')->get();
        $branches = [''=>'']+Sede::orderby('nome', 'ASC')->pluck('nome', 'id')->toArray();
        $products = [''=>'']+Product::orderby('nome', 'ASC')->pluck('nome', 'id')->toArray();
        
/*        if(auth()->user()->hasRole('super')){		    
		    $clienti = Company::where('private', 0)->where('client_id', '!=', 4)->where('active', 1)->orderby('rag_soc', 'ASC')->get();
        	$contacts = Contact::whereIn('contact_type_id',[1,2])->where('attivo', 1)->orderby('cognome', 'ASC')->get();
		} else {
			$user_branch = auth()->user()->contact->branchContact()->branch_id;
	        $company_ids = \DB::table('company_branch')->where('branch_id', $user_branch)->pluck('company_id')->toArray();
		    $contact_ids = \DB::table('contact_branch')->where('branch_id', $user_branch)->pluck('contact_id')->toArray();
		    
		    $clienti = Company::where('private', 0)->where('client_id', '!=', 4)->whereIn('id', $company_ids)->where('active', 1)->orderby('rag_soc', 'ASC')->get();
        	$contacts = Contact::whereIn('contact_type_id',[1,2])->whereIn('id', $contact_ids)->where('attivo', 1)->orderby('cognome', 'ASC')->get();
		}*/

		$day_ok = $day->format('Y-m-d');

		if(auth()->user()->hasRole('super')){
	        $maestri_list = DB::table('masters')
				            ->join('contacts', 'masters.contact_id', '=', 'contacts.id')
				            ->select('masters.id', 'contacts.nome', 'contacts.cognome')
				            ->where('contacts.attivo', 1)
				            //->whereIn('contacts.id', $branch_contacts_ids)
				             ->whereExists(function ($query) use ($day_ok) {
								$query->select("*")
								->from('availabilities')
								->whereRaw('masters.contact_id = availabilities.contact_id and data_start <= "'.$day_ok.'" and data_end >= "'.$day_ok.'"');
							})
				            ->orderby('contacts.cognome', 'ASC')
				            ->get();
        } else {
        	$maestri_list = DB::table('masters')
				            ->join('contacts', 'masters.contact_id', '=', 'contacts.id')
				            ->select('masters.id', 'contacts.nome', 'contacts.cognome')
				            ->where('contacts.attivo', 1)
				            //->whereIn('contacts.id', $branch_contacts_ids)
				             ->whereExists(function ($query) use ($day_ok) {
								$query->select("*")
								->from('availabilities')
								->whereRaw('masters.contact_id = availabilities.contact_id and data_start <= "'.$day_ok.'" and data_end >= "'.$day_ok.'" and branch_id = '.auth()->user()->contact->branchContact()->branch_id);
							})
				            ->orderby('contacts.cognome', 'ASC')
				            ->get();        	
        }
/*		$maestri_list = Master::with('contact')->whereHas('availability', function($q) use($day, $branch_id) {
			if(auth()->user()->hasRole('super')){
				$q->whereDate('data_start', '<=', $day)->whereDate('data_end', '>=', $day);
			} else {
				$q->whereDate('data_start', '<=', $day)->whereDate('data_end', '>=', $day)->where('branch_id', auth()->user()->contact->branchContact()->branch_id);
			}
		})->get();*/
					
		$giorno_output = $day->format('Y-m-d');
		$maestri = $teachers;
		
		return view('areaseb::core.calendars.planning', compact(
			'alloggi','products','labels','ritrovi','branches','collettivi','giorno_output','maestri','maestri_list',
			'teachers', 'hours', 'day'
		));	// 'clienti', 'contacts'
	}

	/**
	 * hourId = info.event.id;
	 * let newStartHour = moment(info.event.start).format("HH:mm");
	 * let newEndHour = moment(info.event.end).format("HH:mm");
	 * let newTeacherId = null;			
	 */
	public function updateHour(Request $request) {
		$hour = Ora::find($request->hourId);

		$hour->ora_in = $request->newStartHour;
		$hour->ora_out = $request->newEndHour;

		if($request->newTeacherId) {
			$hour->id_maestro = $request->newTeacherId;
		}

		$item = Item::where('ora_id', $hour->id)->first();

		if($item) {
			$nome_disciplina = 'Discesa';
			if($hour->disciplina == 2)
				$nome_disciplina = 'Fondo';
			if($hour->disciplina == 3)
				$nome_disciplina = 'Fondo';
			if($hour->disciplina == 4)
				$nome_disciplina = 'Snowboard';

			$elenco_maestri = "";

			$contact_id = Master::find($hour->id_maestro)->contact_id;

			$mm = Contact::where('id', $contact_id)->get();
			foreach ($mm as $value) {
				$elenco_maestri = $elenco_maestri != '' ? $elenco_maestri.','.$value->nome.' '.$value->cognome : $value->nome.' '.$value->cognome;
			}

			$elenco_spec = "";
			$specs = Specialization::whereIn('id', [$hour->specialita])->get();
			
			foreach ($specs as $value) {
				$elenco_spec = $elenco_spec != '' ? $elenco_spec.','.$value->nome : $value->nome;
			}
			
			// Update item description
			$description = "<b>Data:</b> " . \Carbon\Carbon::createFromFormat('Y-m-d', $hour->data)->format('d/m/Y') . '
			<br /><b>Ora:</b> dalle ' . $hour->ora_in . ' alle ' . $hour->ora_out . '
			<br /><b>Ritrovo:</b> ' . $hour->ritrovo . '
			<br /><b>Maestro:</b> ' . $elenco_maestri . '
			<br /><b>Pax:</b> ' . $hour->pax . '
			<br /><b>Disciplina:</b> ' . $nome_disciplina . '
			<br /><b>Specialit&agrave;:</b> ' . $elenco_spec;
			
			$item->descrizione = $description;

			$item->save();
		}		
				
		$hour->save();
		
		return true;
	}


//METODI VECCHIO PORTALE

	function old_loadIndex(Request $request){
	    $oggi = date("d/m/Y");
		$anno = date("Y");

	    $human_month = array("error", "Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre" );
	    $human_day = array("Domenica", "Luned&igrave;", "Marted&igrave;", "Mercoled&igrave;", "Gioved&igrave;", "Venerd&igrave;", "Sabato");

	    $giorno = isset($request->giorno) ? (str_contains($request->giorno, '-') ?  date('d.m.Y',strtotime($request->giorno)) :  $request->giorno) : date("d.m.Y");
	    //dd($giorno);
	    $avanzamento = isset($request->avanzamento) ? $request->avanzamento : "";
	    
	    list($g, $m, $a) = explode(".", $giorno);
	    if($avanzamento == "a"){
	        $giorno = date("d.m.Y", mktime(0, 0, 0, $m, $g+1, $a));
	    } elseif($avanzamento == "i"){
	        $giorno = date("d.m.Y", mktime(0, 0, 0, $m, $g-1, $a));
	    }

	    list($g, $m, $a) = explode(".", $giorno);
	    $giorno_query = "$a-$m-$g";
	    $giorno_output = $giorno_query;
	    $day = $g;
	    $month = $human_month[(int)$m];
	    $human_giorno = $human_day[date("w", mktime(0, 0, 0, $m, $g, $a))];
	    $year = $a;

		//$centro_costo = $_SESSION['centro_costo'];
		$lista_centri = isset($request->centro_costo) ? explode(",", isset($request->centro_costo)) : null;

	    $maestri = $this->getMaestri($giorno_query);

	    $maestri_old = $this->getMaestri_old();	
	    
	    $appuntamenti = $this->getOre($giorno_query);
	    $result="";
	    $colspan = 0;
	    for($o = 8; $o <= 17.5; $o = $o + 0.5){
	        $colspan = $colspan + 1;
	    }

	    $colspan = $colspan + 2;
	    $userAgent = $request->header('User-Agent');
	    $iphone = (bool) strpos($userAgent,"iPhone");
	    $ipad = (bool) strpos($userAgent,'iPad');
	    $ipod = (bool) strpos($userAgent,'iPod');
	    $android = (bool) strpos($userAgent,"Android");
	    $webos = (bool) strpos($userAgent,"WebOS");

	    $result= $result."<table width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\" align=\"center\" class=\"tabelle\">
	                    <tr>
	                        <td colspan=\"".$colspan."\" class=\"intestazione\" align=\"center\">
	                            <a href=\"?giorno=$giorno&avanzamento=i\">&lt;&lt;</a> &nbsp; $human_giorno $day $month $year &nbsp; <a href=\"?giorno=$giorno&avanzamento=a\">&gt;&gt;</a>
	                        </td>
	                    </tr>
	                    <tr>
	                    <td class=\"intestazione\" width=\"14%\">&nbsp;</td>";

	                    $userAgent = $request->header('User-Agent');
	                    $iphone = (bool) strpos($userAgent,"iPhone");
	                    $ipad = (bool) strpos($userAgent,'iPad');
	                    $ipod = (bool) strpos($userAgent,'iPod');
	                    $android = (bool) strpos($userAgent,"Android");
	                    $webos = (bool) strpos($userAgent,"WebOS");

	                    for($o = 8; $o <= 17.5; $o = $o + 0.5){
	                        $elem = explode(".", $o);
	                        $intero = $elem[0];
	                        $resto = isset($elem[1]) ? $elem[1] : 0;
	                        //list($intero, $resto) = explode(".", $o);

	                        if(!$ipad && !$iphone && !$android && !$webos && !$ipod){

	                            if($resto == 0){
	                                $ora = "$intero:00";
	                            } else {
	                                $ora = "$intero:30";
	                            }

	                        } else {

	                            if($resto == 0){
	                                $ora = "$intero";
	                            } else {
	                                $ora = "";
	                            }

	                        }

	                        $result= $result."<td class=\"intestazione\" align=\"left\" width=\"4%\">$ora</td>";

	                    }

	                    $result= $result."<td class=\"intestazione\" width=\"7%\" align=\"center\" style=\"padding-left: 1.3%\">ORE</td>
	                    </tr>
	                </table>";
	                $result= $result."<table width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\" align=\"center\" class=\"tabelle\">";

	                $i = 0;
	                foreach($maestri_old as $maestro)
					{
	                    $id_maestro = $maestro[$i]->id;
	                    
	                    $nome_maestro = $maestro[$i]->nome;
	                    $cognome_maestro = $maestro[$i]->cognome;
	                    $cell = $maestro[$i]->cellulare;
	                    $colore_maestro = $maestro[$i]->color;
	                   // dd($colore_maestro);
	                    $imgAdd = asset('img/aggiungi.png');
	                    $result= $result."<tr>
	                                    <td class=\"intestazione\" width=\"15%\">
	                                        <div class=\"cella_maestro\" style=\"float: left;\">
	                                            <a href=\"/contacts-master/$id_maestro\" style=\"color: white;\">$cognome_maestro $nome_maestro</a>
	                                            <br><span style=\"font-weight: normal;\">$cell</span>";
	                    $result= $result."		</div>
	                                        <div style=\"float: right; padding-right: 10px;\">
	                                            <a href=\"#\" onClick=\"openAddOra($id_maestro)\" title=\"Aggiungi ora\"><i align=\"top\" class=\"fa fa-plus plus-icon\"></i></a>
	                                        </div>
	                                    </td>";

	                    $tot_ore = 0;
	                    //inizio ciclo appuntamenti
	                    //dd($appuntamenti,$this->oreMaestroPerMezzora($this->oreMaestro(25, $appuntamenti)));

	                    foreach($this->oreMaestroPerMezzora($this->oreMaestro($id_maestro, $appuntamenti)) as $ore)
						{

	                        $timeArr = explode(':',$ore['ora_in']);
	                        $ora = intval($timeArr[0]).":".$timeArr[1];
	                        $id_ora = $ore['id'];
	                        $ora_in = $ore['ora_in'];
	                        $ora_out = $ore['ora_out'];
	                        $richiesto = $ore['richiesto'];
	                        $id_cliente = $ore['id_cliente'];
	                        $cc_ora = $ore['id_cc'];
	                        $specialita_ora = $ore['specialita'];
	                        $ritrovo = $ore['ritrovo'];
	                        $aperta = isset($ore['note']) ? $ore['note'] : "";

	                        //$aperta = $ore['note'];
	                        $nome_cliente = "";
							if($ora_out != ''){

	                            $lista_specialita_ora = explode(",", $specialita_ora);
	                            //dd($specialita_ora,$lista_specialita_ora);
	                            if(in_array("1", $lista_specialita_ora))
	                            {
	                                $imgBiberon = asset('img/biberon.png');
	                                $bambino = "<img src=\"$imgBiberon\" align=\"center\" border=\"0\" width=\"15\">";
	                            } else {
	                                $bambino = "";
	                            }

	                            if($ritrovo == "Esterno - Fuori sede")
	                            {
	                                $imgfuori_sede = asset('img/fuori_sede.png');
	                                $ritrovo = "<img src=\"$imgfuori_sede\" align=\"center\" border=\"0\" width=\"15\">";
	                            } else {
	                                $ritrovo = "";
	                            }

	                            if(in_array("16", $lista_specialita_ora))
	                            {
	                                $imgDollaro = asset('img/dollaro.png');
	                                $dollaro = "<img src=\"$imgDollaro\" align=\"center\" border=\"0\" width=\"15\">";
	                            } else {
	                                $dollaro = "";
	                            }

	                            if(in_array("17", $lista_specialita_ora))
	                            {
	                                $imgTelefono = asset('img/telefono.png');
	                                $telefono = "<img src=\"$imgTelefono\" align=\"center\" border=\"0\" width=\"15\">";
	                            } else {
	                                $telefono = "";
	                            }
	                            
	                            if(substr($aperta, 0, 2) == "OA")
	                            {
	                                $imgAperta = asset('img/aperta.png');
	                                $aperta = "<img src=\"$imgAperta\" align=\"center\" border=\"0\" width=\"15\">";
	                            } else {
	                                $aperta = "";
	                            }

	                            list($h_in, $m_in) = explode(":", $ora_in);
	                            list($h_out, $m_out) = explode(":", $ora_out);
	                            $dif_ore = $h_out - $h_in;
	                            $dif_minuti = $m_out - $m_in;
	                            $dif_minuti_ok = ($dif_minuti * 100) / 60;
	                            if($dif_minuti_ok < 0){
	                                $dif_ore--;
	                            }
	                            $dif_minuti_ok = abs($dif_minuti_ok);
	                            $diff = "$dif_ore.$dif_minuti_ok";

	                            $moltiplicatore = ($diff / 0.5);

	                            $width = $moltiplicatore * 4.1;
	                            $width = $width - ($width * 0.045);
								//$width = ($diff / 0.5) * 4;
	                            
	                            
								//recupero il cliente
	                                if(substr($id_cliente, 0, 1) == "C"){
	                                    $id_collettivo = substr($id_cliente, 2);
	                                    //recupero il cliente
	                                    $allievi_c =CollettivoAllievi::where('id_collettivo',$id_collettivo)->where('giorno',$giorno_query)->where('id_maestro',$id_maestro)->count();
	                                    /*$query21 = "select count(id) from collettivo_allievi where id_collettivo = $id_collettivo and giorno = \"$giorno_query\" and id_maestro = $id_maestro;";
	                                    $risultato21 = mysql_query($query21, $connessione) or die("Query inserimento fallita $query");

	                                    //recupero il maestro
	                                    while ($row21 = mysql_fetch_row($risultato21)) {
	                                        for ($j21 = 0; $j21 < count($row21); $j21++) {
	                                            $stringa21 = trim("$row21[$j21]");     //Toglie gli spazi inutili
	                                                $array21[$j21] = $stringa21;
	                                            }
	                                        list($allievi_c) = $array21;

	                                        $allievi_c = "($allievi_c)";
	                                    }*/

	                                    $collettivo = Collettivo::find($id_collettivo);

	                                    //dd($allievi_c, $collettivo);
	                                // $query21 = "select nome from collettivo where id = $id_collettivo;";
	                                // $risultato21 = mysql_query($query21, $connessione) or die("Query inserimento fallita $query21 ".mysql_error());

	                                    //recupero i dati
	                                /* while ($row21 = mysql_fetch_row($risultato21)) {
	                                        for ($j21 = 0; $j21 < count($row21); $j21++) {
	                                            $stringa21 = trim("$row21[$j21]");     //Toglie gli spazi inutili
	                                            $array21[$j21] = $stringa21;
	                                        }
	                                        list($nome_cliente) = $array21;

	                                        $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                                <a href=\"worker/view_collettivo.php?id=$id_collettivo\" title=\"Modifica collettivo\">Collettivo ".$nome_cliente." $allievi_c</a> $bambino $ritrovo $dollaro $telefono $aperta";

	                                        $tot_ore = $tot_ore + $diff;
	                                    }*/
	                                    $allievi_c = "($allievi_c)";
	                                    $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                                <a href=\"/collective/$id_collettivo\" title=\"Modifica collettivo\">Collettivo ".$collettivo->nome." $allievi_c </a> $bambino $ritrovo $dollaro $telefono $aperta";

	                                    $tot_ore = $tot_ore + $diff;

	                                }
	                                elseif($id_cliente != "") {
	                                    list($tipo, $id_cliente) = explode('_', $id_cliente);
	                                    //dd($id_cliente);
	                                    if($tipo == 'T'){
	                                        $contact = Contact::find($id_cliente);
	                                        if($contact != null){
	                                            $contact->disabile = $contact->isDisabled($id_cliente) != null ? "S" : "N";
	                                            //$query21 = "select cognome, nickname, tel, cell, disabile, cf from cliente where id = $id_cliente;";
	                                            //$risultato21 = mysql_query($query21, $connessione) or die("Query inserimento fallita $query21 ".mysql_error());

	                                            //recupero i dati
	                                            //while ($row21 = mysql_fetch_row($risultato21)) {
	                                                /*for ($j21 = 0; $j21 < count($row21); $j21++) {
	                                                    $stringa21 = trim("$row21[$j21]");     //Toglie gli spazi inutili
	                                                    $array21[$j21] = $stringa21;
	                                                }*/
	                                                //list($cognome_cliente, $nick_cliente, $tel_cliente, $cell_cliente, $disabile, $cf) = $array21;

	                                                $cognome_cliente = $contact->cognome;
	                                                $tel_cliente = $contact->cellulare;
	                                                $cell_cliente = $contact->cellulare;
	                                                $disabile = $contact->disabile;
	                                                $cf = $contact->cod_fiscale;


	                                                if($contact->nickname == ""){
	                                                    $nome_cli = "$cognome_cliente";
	                                                } else {
	                                                    $nome_cli = "$contact->nickname";
	                                                }

	                                                if($richiesto == "S"){
	                                                    $imgRichiesto = asset('img/richiesto.png');
	                                                    $nome_cli = $nome_cli . " <img src=\"$imgRichiesto\" align=\"center\" border=\"0\" width=\"15\">";
	                                                }

	                                                if($cell_cliente == ""){
	                                                    $ntelefono = $tel_cliente;
	                                                } else {
	                                                    $ntelefono = $cell_cliente;
	                                                }

	                                                if($disabile == "S"){
	                                                    $imgDisabili = asset('img/disabili.png');
	                                                    $cliente_disabile = "<a href=\"/contacts/$id_cliente\" title=\"Visualizza scheda disabile\"><img src=\"$imgDisabili\" align=\"center\" border=\"0\" width=\"15\"></a>";
	                                                } else {
	                                                    $cliente_disabile = "";
	                                                }

	                                                if($cf == ""){
	                                                    $imgda_compilare = asset('img/da_compilare.png');
	                                                    $cf = "<a href=\"/contacts/$id_cliente\" title=\"Modifica cliente\"><img src=\"$imgda_compilare\" align=\"center\" border=\"0\" width=\"15\"></a>";
	                                                } else {
	                                                    $cf = "";
	                                                }

	                                                //pagato
	                                                //PER IL MOMENTO
	                                                /*$query21 = "select id, saldo, centro_costo from fattura where id_ora like \"%$id_ora,%\"";	// and id_cliente = $id_cliente;";
	                                                $risultato21 = mysql_query($query21, $connessione) or die("Query inserimento fallita $query21 ".mysql_error());

	                                                //recupero i dati
	                                                while ($row21 = mysql_fetch_row($risultato21))
	                                                {
	                                                    for ($j21 = 0; $j21 < count($row21); $j21++)
	                                                    {
	                                                        $stringa21 = trim("$row21[$j21]");
	                                                        $array21[$j21] = $stringa21;
	                                                    }
	                                                    list($id_fatt, $saldo, $cc) = $array21;
	                                                }
	                                            */

	                                            $elem = Invoice::select('invoices.*')->join('invoice_ora','invoice_ora.invoice_id','=','invoices.id')
	                                                                                ->where('invoice_ora.ora_id',$id_ora)
	                                                                                ->first();

	                                            /*if($saldo == "N"){
	                                                $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                                        <a onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\"><b>$nome_cli</b></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta<br>
	                                                                                        $ntelefono";
	                                                $tot_ore = $tot_ore + $diff;
	                                            } elseif($saldo == "S"){
	                                                $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                                        <a onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\">$nome_cli</a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta<br>
	                                                                                        $ntelefono";
	                                                $tot_ore = $tot_ore + $diff;
	                                            } elseif($saldo == "A" || $saldo == "" || $saldo == null){
	                                                $centro_costo = $_SESSION['centro_costo'];
	                                                $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                                        <a onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\"><b>$nome_cli</b></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta <a href=\"worker/mod_fattura.php?page=planning&id_ora=$id_ora&centro_costo=$cc_ora&cliente=$id_cliente&id=$id_fatt\" title=\"Emetti fattura\"><img src=\"../img/attenzione.png\" align=\"center\" border=\"0\"></a><br>
	                                                                                        $ntelefono";
	                                                $tot_ore = $tot_ore + $diff;
	                                            }*/
	        
	                                            $saldo = "";

	                                            $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                <a href=\"#\" onClick=\"openModOra($id_ora)\" title=\"Modifica ora\"><b>$nome_cli</b></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta<br>
	                                                                                        $ntelefono";
	                                                $tot_ore = $tot_ore + $diff;

	                                            //dd($cognome_cliente);
	                                        }
	                                    }
	                                    
	                                    if($tipo == 'Y'){
	                                        $contact = Company::find($id_cliente);
	                                        if($contact != null){
	                                            //$query21 = "select cognome, nickname, tel, cell, disabile, cf from cliente where id = $id_cliente;";
	                                            //$risultato21 = mysql_query($query21, $connessione) or die("Query inserimento fallita $query21 ".mysql_error());

	                                            //recupero i dati
	                                            //while ($row21 = mysql_fetch_row($risultato21)) {
	                                                /*for ($j21 = 0; $j21 < count($row21); $j21++) {
	                                                    $stringa21 = trim("$row21[$j21]");     //Toglie gli spazi inutili
	                                                    $array21[$j21] = $stringa21;
	                                                }*/
	                                                //list($cognome_cliente, $nick_cliente, $tel_cliente, $cell_cliente, $disabile, $cf) = $array21;

	                                                $cognome_cliente = $contact->rag_soc;
	                                                $tel_cliente = $contact->phone;
	                                                $cell_cliente = $contact->mobile;
	                                                $cf = $contact->cf;


	                                                if($contact->nickname == ""){
	                                                    $nome_cli = "$cognome_cliente";
	                                                } else {
	                                                    $nome_cli = "$contact->nickname";
	                                                }

	                                                if($richiesto == "S"){
	                                                    $imgRichiesto = asset('img/richiesto.png');
	                                                    $nome_cli = $nome_cli . " <img src=\"$imgRichiesto\" align=\"center\" border=\"0\" width=\"15\">";
	                                                }

	                                                if($cell_cliente == ""){
	                                                    $ntelefono = $tel_cliente;
	                                                } else {
	                                                    $ntelefono = $cell_cliente;
	                                                }

	                                                $cliente_disabile = "";

	                                                if($cf == ""){
	                                                    $imgda_compilare = asset('img/da_compilare.png');
	                                                    $cf = "<a href=\"/contacts/$id_cliente\" title=\"Modifica cliente\"><img src=\"$imgda_compilare\" align=\"center\" border=\"0\" width=\"15\"></a>";
	                                                } else {
	                                                    $cf = "";
	                                                }

	                                                //pagato
	                                                //PER IL MOMENTO
	                                                /*$query21 = "select id, saldo, centro_costo from fattura where id_ora like \"%$id_ora,%\"";	// and id_cliente = $id_cliente;";
	                                                $risultato21 = mysql_query($query21, $connessione) or die("Query inserimento fallita $query21 ".mysql_error());

	                                                //recupero i dati
	                                                while ($row21 = mysql_fetch_row($risultato21))
	                                                {
	                                                    for ($j21 = 0; $j21 < count($row21); $j21++)
	                                                    {
	                                                        $stringa21 = trim("$row21[$j21]");
	                                                        $array21[$j21] = $stringa21;
	                                                    }
	                                                    list($id_fatt, $saldo, $cc) = $array21;
	                                                }
	                                            */

	                                            $elem = Invoice::select('invoices.*')->join('invoice_ora','invoice_ora.invoice_id','=','invoices.id')
	                                                                                ->where('invoice_ora.ora_id',$id_ora)
	                                                                                ->first();

	                                            /*if($saldo == "N"){
	                                                $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                                        <a onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\"><b>$nome_cli</b></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta<br>
	                                                                                        $ntelefono";
	                                                $tot_ore = $tot_ore + $diff;
	                                            } elseif($saldo == "S"){
	                                                $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                                        <a onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\">$nome_cli</a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta<br>
	                                                                                        $ntelefono";
	                                                $tot_ore = $tot_ore + $diff;
	                                            } elseif($saldo == "A" || $saldo == "" || $saldo == null){
	                                                $centro_costo = $_SESSION['centro_costo'];
	                                                $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                                        <a onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\"><b>$nome_cli</b></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta <a href=\"worker/mod_fattura.php?page=planning&id_ora=$id_ora&centro_costo=$cc_ora&cliente=$id_cliente&id=$id_fatt\" title=\"Emetti fattura\"><img src=\"../img/attenzione.png\" align=\"center\" border=\"0\"></a><br>
	                                                                                        $ntelefono";
	                                                $tot_ore = $tot_ore + $diff;
	                                            }*/
	        
	                                            $saldo = "";

	                                            $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                <a tabindex=\'1\' href=\"#\" onClick=\"openModOra($id_ora)\" title=\"Modifica ora\"><b>$nome_cli</b></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta<br>
	                                                                                        $ntelefono";
	                                                $tot_ore = $tot_ore + $diff;

	                                            //dd($cognome_cliente);

	                                        }
	                                    }
	                                    
	                                    if($tipo == 'L')
	                                    {
	                                        $label = Label::find($id_cliente);
	                                        $cognome_cliente = $label->nome;
	                                        $nome_cli = "$cognome_cliente";

	                                        if($richiesto == "S"){
	                                            $imgRichiesto = asset('img/richiesto.png');
	                                            $nome_cli = $nome_cli . " <img src=\"$imgRichiesto\" align=\"center\" border=\"0\" width=\"15\">";
	                                        }

	        
	                                        $elem = Invoice::select('invoices.*')->join('invoice_ora','invoice_ora.invoice_id','=','invoices.id')
	                                                                                    ->where('invoice_ora.ora_id',$id_ora)
	                                                                                    ->first();

	                                        $saldo = "";
	        
	                                        $nome_cliente = "";
	                                        if($cognome_cliente == "NON DISPONIBILE"){
	                                            $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: black; width: ".$width."%; z-index: 10;\">
	                                                                                    <a tabindex=\'1\' onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\" style=\"color: white;\"><b>$nome_cli</b></a>";
	                                            $tot_ore = $tot_ore - $diff;
	                                        }

	                                        if($cognome_cliente == "SCI CLUB"){
	                                            $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: #0082DA; width: ".$width."%; z-index: 10;\">
	                                                                                    <a tabindex=\'1\' onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\" style=\"color: white;\"><b>$nome_cli</b></a>";
	                                            $tot_ore = $tot_ore - $diff;
	                                        }

	                                        if($cognome_cliente == "OPZIONE"){
	                                            $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: #FFCC00; width: ".$width."%; z-index: 10;\">
	                                                                                    <a tabindex=\'1\' onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\" style=\"color: black;\"><b>$nome_cli</b></a>";
	                                            $tot_ore = $tot_ore - $diff;
	                                        }

	                                        if($cognome_cliente == "COSTA"){
	                                            $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: #787878; width: ".$width."%; z-index: 10;\">
	                                                                                    <a tabindex=\'1\' onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\" style=\"color: black;\"><b>$nome_cli</b></a>";
	                                            $tot_ore = $tot_ore - $diff;
	                                        }
	        
	                                                //}
	                                    }
	                                }
	                                elseif($id_cliente == "" && $ora_out != ""){

	                                    $imgAttenzione = asset('img/attenzione.png');
	                                    $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                        ";

	                                    /*$nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: $colore_maestro; width: ".$width."%; z-index: 10;\">
	                                                                        <a onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Cliente inesistente ma ora fissata - ATTENZIONE !!!\"><img src=\"$imgAttenzione\" align=\"center\" border=\"0\"><img src=\"$imgAttenzione\" align=\"center\" border=\"0\"><img src=\"$imgAttenzione\" align=\"center\" border=\"0\"></a> $cliente_disabile $bambino $ritrovo $cf $dollaro $telefono $aperta<br>
	                                                                        $ntelefono";*/
	                                $tot_ore = $tot_ore + $diff;
	                                } else {
	                                    $nome_cliente = "<div class=\"cella_cal\" style=\"position: absolute; padding: 3px; margin: -3px -3px -3px -5px; border-left: solid 2px #0082DA; background-color: red; width: ".$width."%; z-index: 10;\">
	                                                                            <img src=\"../img/attenzione.png\" align=\"top\" border=\"0\" height=\"30%\"> <img src=\"../img/attenzione.png\" align=\"top\" border=\"0\" height=\"30%\"> <a tabindex=\'1\' onClick=\"javascript: window.open('worker/modora.php?id=$id_ora&giorno=$giorno&avanzamento=$avanzamento', 'modora', 'width=900,height=580,left=150,top=50,scrollbars=yes');\" title=\"Modifica ora\" style=\"color: white;\"><b>ATTENZIONE !!!<br>Cliente non impostato !</b></a> <img src=\"../img/attenzione.png\" align=\"top\" border=\"0\" height=\"30%\"> <img src=\"../img/attenzione.png\" align=\"top\" border=\"0\" height=\"30%\">";
	                                }


						    } //end second while select form ora

	                        if($ora_in != ""){
	                            if($nome_cliente == ""){
	                                $ora = explode(":",$ora);
	                                $dateFromModal = strval($g).'/'.strval($m).'/'.strval($a).'/'.strval($ora[0]).'/'.strval($ora[1]);
	                                $nome_cliente = "<a tabindex=\'1\' href=\"#\" onClick=\"openAddOraPreimpostata($ora[0],$ora[1],$id_maestro)\"  title=\"Aggiungi ora\"><i align=\"top\" class=\"fa fa-plus plus-icon-tab text-success\"></i></a>";
	                                $align = "center";
	                            } else {
	                                $align = "left";
	                            }
	                            $result= $result."<td align=\"$align\" valign=\"top\" width=\"4%\">
	                                        $nome_cliente
	                                    </div>
	                                </td>";
	                            $nome_cliente = '';
	                        } 
	                        else {

	                            //disponibilita maestro
	                           // $query21 = "select id from maestro_disponibilita where data_in <= \"$giorno_query\" and \"$giorno_query\" <= data_out and id_maestro = $id_maestro;";
	                                                         
	                            $availability = Availability::where('contact_id',$id_maestro)->where('data_start','<=', $giorno_query)->where('data_end','>=', $giorno_query)->first();
	    

	                            //recupero i dati
	                            /*while ($row21 = mysql_fetch_row($risultato21)) {
	                                for ($j21 = 0; $j21 < count($row21); $j21++) {
	                                    $stringa21 = trim("$row21[$j21]");     //Toglie gli spazi inutili
	                                    $array21[$j21] = $stringa21;
	                                }
	                                list($id_disp) = $array21;
	                            }*/
	                            $id_disp = $availability->id;

	                            if($id_disp != ""){
	                                $ora = explode(":",$ora);
	                                $dateFromModal = strval($g).'/'.strval($m).'/'.strval($a).'/'.strval($ora[0]).'/'.strval($ora[1]);
	                                $result= $result."<td align=\"center\" valign=\"top\" width=\"4%\">
	                                <a tabindex=\'1\' href=\"#\" onClick=\"openAddOraPreimpostata($ora[0],$ora[1],$id_maestro)\"  title=\"Aggiungi ora\"><i align=\"top\" class=\"fa fa-plus plus-icon-tab text-success\"></i></a></td>";
	                            } else {
	                                $result= $result."<td bgcolor=\"black\">&nbsp;</td>";
	                            }

	                            $id_disp = "";
	                        }

	                        $telefono = "";
	                        $cliente_disabile = "";
	                        $ora_in = "";

	                    } 
	                    //fine ciclo appuntamenti
	                    $tot_ore = number_format($tot_ore, 1, ",", ".");
	                    $result= $result."<td class=\"intestazione\" align=\"center\" width=\"5%\">$tot_ore</td>";

	                    $tot_ore = 0;
	                    $id_maestro = "";
	                    $query2 = "";
	                    $nome_cliente = "";

	                    $i++;

	                } 

	                $result= $result."</table>
	                <table width=\"100%\" cellpadding=\"3\" cellspacing=\"0\" border=\"0\" align=\"center\" class=\"tabelle\">
	                    <tr>
	                        <td class=\"intestazione\" width=\"14%\">&nbsp;</td>";


	                for($o = 8; $o <= 17.5; $o = $o + 0.5){
	                    $elem = explode(".", $o);
	                    $intero = $elem[0];
	                    $resto = isset($elem[1]) ? $elem[1] : 0;

	                    if(!$ipad && !$iphone && !$android && !$webos && !$ipod){

	                        if($resto == 0){
	                            $ora = "$intero:00";
	                        } else {
	                            $ora = "$intero:30";
	                        }

	                    } else {

	                        if($resto == 0){
	                            $ora = "$intero";
	                        } else {
	                            $ora = "";
	                        }

	                    }

	                    $result= $result."<td class=\"intestazione\" align=\"left\" width=\"4%\">$ora</td>";

	                }

	                $result= $result."		<td class=\"intestazione\" width=\"7%\" align=\"center\" style=\"padding-left: 1.3%\">ORE</td>
	                                </tr>
	                            </table>
	                            <br>";
	        $collettivi = Collettivo::orderby('nome', 'ASC')->get();
	        $alloggi = Housing::orderby('luogo', 'ASC')->get();
	        $ritrovi = Hangout::orderby('luogo', 'ASC')->get();
	        $labels = Label::orderby('nome', 'ASC')->get();
	        $branches = [''=>'']+Sede::orderby('nome', 'ASC')->pluck('nome', 'id')->toArray();
	        $products = [''=>'']+Product::orderby('nome', 'ASC')->pluck('nome', 'id')->toArray();
	        $clienti = Company::where('private',0)->where('client_id', '!=', 4)->orderby('rag_soc', 'ASC')->get();
	        $contacts = Contact::whereIn('contact_type_id',[1,2])->orderby('cognome', 'ASC')->get();
	        $maestri_list = DB::table('masters')
				            ->join('contacts', 'masters.contact_id', '=', 'contacts.id')
				            ->select('masters.id', 'contacts.nome', 'contacts.cognome')
				            ->orderby('contacts.cognome', 'ASC')
				            ->get();
	        				//Contact::with('master')->where('contact_type_id',3)->orderby('cognome', 'ASC')->get();

	        foreach ($clienti as $cli) {
	            $cli->sedi = $cli->listBranches();
	        }

	        foreach ($contacts as $con) {
	            $con->sedi = $con->branch($con->id);
	        }

			return view('areaseb::core.calendars.old_planning', 
				compact('giorno_query','userAgent','iphone','ipad','ipod', 'android', 'webos', 'result','products','alloggi','g', 'm', 'a','contacts',
					'labels','ritrovi','branches','clienti','collettivi','giorno_output','maestri','maestri_list','giorno','human_giorno', 
					'day', 'month', 'year'));
	}


	function getMaestri($date)
	{

	    $row = Contact::select('contacts.*','masters.color')
	                    ->join('masters','masters.contact_id','=','contacts.id')
	                    ->join('contact_branch','contact_branch.contact_id','=','contacts.id')
	                    ->join('availabilities','availabilities.contact_id','=','contacts.id')
	                    ->where('contacts.contact_type_id',3)
	                    ->where('availabilities.data_start','<=', $date)
	                    ->where('availabilities.data_end','>=', $date);

	    if(auth()->user()->role != 'super'){

	        /*$contact_logged = Contact::select('contacts.*','contact_branch.branch_id')
	                                ->join('contact_branch','contact_branch.contact_id','=','contacts.id')
	                                ->where('contacts.user_id',auth()->user()->id)
	                                ->first();

	        $branch_id = $contact_logged->branch_id;*/
	        $branch_id = auth()->user()->contact->branchContact()->branch_id;
	        $row = $row->where('contact_branch.branch_id',$branch_id);
	    }
	                   
	    $row = $row->orderby('contacts.cognome', 'ASC')
	                    ->get();

	    
	    return $row;
	    
	    
/*	    $arr = [];$count = 0;
		foreach($row as $key => $value)
			{
				if($key == 'id')
				{
					$arr[$count][$key] = intval($value);
				}
				else
				{
					$arr[$count][$key] = $value;
				}
	            $count++;
			}

		return $arr;*/

		/*include "../config/dbconnection.php";

		$query = "select id, nome, cognome, cell, colore from maestro where $q order by cognome;";
		$risultato = mysql_query($query, $connessione) or die("Query inserimento fallita $query ".mysql_error());
		$arr = [];$count = 0;
		while ($row = mysql_fetch_assoc($risultato))
		{
			foreach($row as $key => $value)
			{
				if($key == 'id')
				{
					$arr[$count][$key] = intval($value);
				}
				else
				{
					$arr[$count][$key] = $value;
				}

			}
			$count++;
		}

		return $arr;*/
	}



	function getMaestri_old()
	{

	    $row = Contact::select('contacts.*','masters.color')
	    		->join('masters','masters.contact_id','=','contacts.id')
	            ->join('contact_branch','contact_branch.contact_id','=','contacts.id')
	    		->where('contacts.contact_type_id', 3);
	    
			    if(auth()->user()->role != 'super'){
			        $branch_id = auth()->user()->contact->branchContact()->branch_id;
			        $row = $row->where('contact_branch.branch_id',$branch_id);
			    }
	    		$row = $row->orderby('contacts.cognome', 'DESC')
	    				->get();
	    		
	    $arr = [];$count = 0;
		foreach($row as $key => $value)
			{
				if($key == 'id')
				{
					$arr[$count][$key] = intval($value);
				}
				else
				{
					$arr[$count][$key] = $value;
				}
	            $count++;
			}

		return $arr;

		/*include "../config/dbconnection.php";

		$query = "select id, nome, cognome, cell, colore from maestro where $q order by cognome;";
		$risultato = mysql_query($query, $connessione) or die("Query inserimento fallita $query ".mysql_error());
		$arr = [];$count = 0;
		while ($row = mysql_fetch_assoc($risultato))
		{
			foreach($row as $key => $value)
			{
				if($key == 'id')
				{
					$arr[$count][$key] = intval($value);
				}
				else
				{
					$arr[$count][$key] = $value;
				}

			}
			$count++;
		}

		return $arr;*/
	}

	function mutateMaestri($arr)
	{
		$arr2 = [];
		foreach ($arr as $value)
		{
			$arr2[$value['id']] = [
				'nome' => $value['nome'],
				'cognome' => $value['cognome'],
				'cell' =>  $value['cell'],
				'colore' => $value['colore']
			];
		}

		return $arr2;
	}

	function getOre($giorno)
	{

	    $arr = [];$count = 0;
	    $row = Ora::where('data', $giorno)->orderBy('ora_in')->get()->toArray();
	    //return $row;
	    foreach($row as $key => $value)
	        {
	                if($key == 'id')
	                {
	                    $arr[$count][$key] = intval($value);
	                }
	                else
	                {
	                    $arr[$count][$key] = $value;
	                }
	                $count++;
	        }
		return $arr;
	}

	function oreMaestro($id_maestro, $ore)
	{
	   // dd($id_maestro, $ore);
		$arr = []; $count = 0;
		foreach ($ore as $ora)
		{
			if(isset($ora['id_maestro']) && $ora['id_maestro'] == $id_maestro)
			{
				$arr[$count] = $ora;
				$count++;
			}
		}
		return $arr;
	}


	function oreMaestroPerMezzora($oreMaestro)
	{
		$arr = [];
	    
		for($o = 8; $o <= 17.5; $o = $o + 0.5)
		{
	        $elem = explode(".", $o);

	        $intero = $elem[0];
	        $resto = isset($elem[1]) ? $elem[1] : 0;
	        //list($intero, $resto) = $elem;
	        //dd(explode(".", $o));
	        if($resto == 0){
	            $ora = sprintf('%02d',$intero).":00:00";
	        } else {
	            $ora = sprintf('%02d',$intero).":30:00";
	        }
	        $arr[] = $ora;		
		}

		$arr2 = [];
		foreach($arr as $ora)
		{
			$turno = $this->findHour($oreMaestro, $ora);
			if($turno)
			{
				$arr2[] = $turno;
			}
			else
			{
				$arr2[] = [
					'id' => '',
					'ora_in' => $ora,
					'ora_out' => '',
					'id_maestro' => '',
					'richiesto' => '',
					'id_cliente' => '',
					'id_cc' => '',
					'specialita' => '',
					'ritrovo' => ''
				];
			}
		}
		return $arr2;
	}

	function findHour($oreMaestro, $ora)
	{
		foreach($oreMaestro as $turno)
		{
			if($turno['ora_in'] == $ora)
			{
				return $turno;
			}
		}
		return false;
	}


	function manageInvoice($ora,$aperta){
	    \DB::beginTransaction();
	    list($tipo, $id_cliente) = explode('_', $ora->id_cliente);

	    $branch_id = $ora->id_cc;
	    /*if($tipo == 'Y'){
	        $cmb = \DB::table('company_branch')->where('company_id', $id_cliente)->first();
	        if($cmb != null)
	            $branch_id = $cmb->branch_id;
	    }          
	    if($tipo == 'T'){
	        $ccb = \DB::table('contact_branch')->where('contact_id', $id_cliente)->first();
	        if($ccb != null)
	            $branch_id = $ccb->branch_id;
	    }*/

	    $data = date("d/m/Y");
	   
	    if($aperta == 1 ){
	        $invoice_check = null;
	        if($tipo == 'Y') {
	            $invoice_check = Invoice::where('aperta', 1)->where('company_id', $id_cliente)->first();
	        } elseif($tipo == 'T') {
	            $invoice_check = Invoice::where('aperta', 1)->where('contact_id', $id_cliente)->first();
	        }

	        if(!$invoice_check){
	            $invoice = new Invoice();
	            if($tipo == 'Y')
	                $invoice->company_id = $id_cliente;
	            if($tipo == 'T')
	                $invoice->contact_id = $id_cliente;
	            $invoice->branch_id = $branch_id;
	            $invoice->data = $data;
	            $invoice->data_registrazione = $data;
	            $invoice->pagamento = "RIDI";
	            $invoice->saldato = 0;
	            $invoice->aperta = $aperta;
	            $invoice->save();
	        } else {
	            $invoice = $invoice_check;
	        }           
	    } else {
	    	$numero = Invoice::where('tipo', 'R')->whereYear('data', date('Y'))->max('numero');
	    	
	        $invoice = new Invoice();
	        if($tipo == 'Y')
	            $invoice->company_id = $id_cliente;
	        if($tipo == 'T')
	            $invoice->contact_id = $id_cliente;
	      
	        $invoice->branch_id = $branch_id;
	        $invoice->data = $data;
	        $invoice->data_registrazione = $data;
	        $invoice->pagamento = "RIDI";
	        $invoice->saldato = 0;
	        $invoice->numero = $numero + 1;
	        $invoice->numero_registrazione = $numero + 1;
	        $invoice->aperta = $aperta;
	        $invoice->save();
	    }

	    $nome_disciplina = 'Discesa';
	    if($ora->disciplina == 2 || $ora->disciplina == 3)
	        $nome_disciplina = 'Fondo';
	    if($ora->disciplina == 4)
	        $nome_disciplina = 'Snowboard';

	    $elenco_spec = "";
	    $specs= Specialization::whereIn('id', [$ora->specialita])->get();
	 
	    foreach ($specs as $value) {
	        $elenco_spec = $elenco_spec != '' ? $elenco_spec.','.$value->nome : $value->nome;
	    }

	    $elenco_maestri = "";

		$teacher = Master::find($ora->id_maestro);
	    $mm= Contact::where('id', $teacher->contact_id)->get();
	    foreach ($mm as $value) {
	        $elenco_maestri = $elenco_maestri != '' ? $elenco_maestri.','.$value->nome.' '.$value->cognome : $value->nome.' '.$value->cognome;
	    }

	    $descr = "<b>Data:</b> ".date('d/m/Y', strtotime($ora->data))."
	            <br /><b>Ora:</b> dalle ".substr($ora->ora_in, 0, 5)." alle ".substr($ora->ora_out, 0, 5)."
	            <br /><b>Ritrovo:</b> $ora->ritrovo
	            <br /><b>Maestro:</b> $elenco_maestri
	            <br /><b>Pax:</b> $ora->pax
	            <br /><b>Disciplina:</b> $nome_disciplina
	            <br /><b>Specialit&agrave;:</b> $elenco_spec";

	    $rel_invoice_ora = array('invoice_id' => $invoice->id,'ora_id' => $ora->id);
	    DB::table('invoice_ora')->insert($rel_invoice_ora);
		
		// calcolo quante ore devo fatturare
		$origin = date_create($ora->data . ' ' . substr($ora->ora_in, 0, 8));
		$target = date_create($ora->data . ' ' . substr($ora->ora_out, 0, 8));
		$interval = date_diff($origin, $target);
		$diff_ore = $interval->format('%H');
		$diff_min = $interval->format('%I');
		if($diff_min == 30){
			$diff_ore += 0.5;
		}
		
	    $itemInvoice = new Item();
	    $itemInvoice->product_id = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->id;
	    $itemInvoice->descrizione = $descr;
	    $itemInvoice->qta = $diff_ore;
	    $itemInvoice->importo = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->prezzo;
	    $itemInvoice->perc_iva = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->perc_iva;
	    $itemInvoice->iva = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->prezzo * (Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->perc_iva / 100);

	    $itemInvoice->invoice_id = $invoice->id;
	    $itemInvoice->exemption_id = Product::where('nome', 'like', '%'.$nome_disciplina.'%')->first()->exemption_id;
	    $itemInvoice->ora_id = $ora->id;
	    $itemInvoice->save();
	    \DB::commit();
	    return $invoice->id;

	}

	function insertOraStep1(Request $request){
	    \DB::beginTransaction();
	    $res = array(); 
		
		if(($request->partecipante == '' || $request->partecipante == 'null') && ($request->cliente == '' || $request->cliente == 'null') && $request->collettivo == '' && $request->label == ''){
			$res['code'] = false;
	        $res['message'] = 'Devi selezionare il cliente!';
	        $res['data'] = 'Cliente non selezionato!';
	        echo json_encode($res);
	        
	        exit;
		}
		
	    try{
	        $branch;      
	        if(isset($request->branches) && $request->branches != null)
	            $branch = $request->branches;

	        //dd($request);
	        $maestri = [];
	        $ora_id = null;
	        $collettivo_allievi_id = null;
	        
	        if(isset($request->colletivo)) {	        	
	            $student = new CollettivoAllievi();
	            $student->id_collettivo = $request->colletivo;
	            $student->id_cliente = $request->partecipante;
	            $student->livello =  $request->livello;
	            $student->giorno = $request->data_in;
	            $student->save();

	           Contact::where('id', $request->partecipante)->update(['livello'=> $request->livello]);

	            $collettivo_allievi_id = $student->id;
	            $query = "select id, nome, cognome from contacts 
	                    where id in
	                    (
	                                select distinct id_maestro from ora 
	                                where id_cliente = \"C_$request->colletivo\"
	                    ) 
	                    order by cognome;";

	            $maestri = \DB::select($query);
	        }
	        else
	        {      
	        	if(isset($request->partecipante)){
	        		Contact::where('id', $request->partecipante)->update(['livello'=> $request->livello]);	        		
	        	}
	        		        	
	            $rit = Hangout::find($request->ritrovo);
	            $ritrovo_desc = $rit != null ? $rit->luogo : '';
	        
				// Get teachers to print in checkboxes
				$date_start = $request->data_in;
				$date_end = $request->data_out;
			
				$hour_start = $request->ora_in;
				$hour_end = $request->ora_out;
			
				// Get range of days
				$days = \Carbon\CarbonPeriod::create($date_start, $date_end);
			
				// Get teachers available
				$forced_branch = null;

				if($request->branches != '')
					$forced_branch = $request->branches;

				$teachers_by_day = [];
				$branch_contacts_ids = Contact::query($forced_branch)->where('contact_type_id', 3)->pluck('id')->toArray();

				// Set weekdays to skip
				$days_to_skip = [];

				if(isset($request->freq_1) && $request->freq_1 == '0')
					$days_to_skip[] = 1;

				if(isset($request->freq_2) && $request->freq_2 == '0')
					$days_to_skip[] = 2;

				if(isset($request->freq_3) && $request->freq_3 == '0')
					$days_to_skip[] = 3;

				if(isset($request->freq_4) && $request->freq_4 == '0')
					$days_to_skip[] = 4;

				if(isset($request->freq_5) && $request->freq_5 == '0')
					$days_to_skip[] = 5;

				if(isset($request->freq_6) && $request->freq_6 == '0')
					$days_to_skip[] = 6;

				if(isset($request->freq_0) && $request->freq_0 == '0')
					$days_to_skip[] = 0;

				if(isset($request->freq_C) && $request->freq_C == '1')
					$days_to_skip = []; // Reset filter

				if($date_start == $date_end)
					$days_to_skip = [];

				foreach($days as $day) {
					// Check day of week
					$teachers_available = [];

					// Skip days
					if(in_array($day->dayOfWeek, $days_to_skip))
						continue;
			
					$teachers_available = Master::whereHas('availability', function($q) use($day, $branch) {		//whereIn('contact_id', $branch_contacts_ids)->
						$q->whereDate('data_start', '<=', $day)->whereDate('data_end', '>=', $day)->where('branch_id', $branch);
					})->whereDoesntHave('hours', function($q) use($hour_start, $hour_end, $day) {
						$q->where(function ($query) use ($hour_start, $hour_end, $day) {
							$query->whereDate('data', $day);
							$query->where('ora_in', '>=', $hour_start)->where('ora_out', '<=', $hour_end);
						})->orWhere(function ($query) use ($hour_start, $hour_end, $day) {
							$query->whereDate('data', $day);
							$query->where('ora_in', '<=', $hour_start)->where('ora_out', '>=', $hour_end);
						})->orWhere(function ($query) use ($hour_start, $hour_end, $day) {
							$query->whereDate('data', $day);
							$query->where('ora_in', '<', $hour_start)->where('ora_out', '>', $hour_start);
						})->orWhere(function ($query) use ($hour_start, $hour_end, $day) {
							$query->whereDate('data', $day);
							$query->where('ora_in', '<', $hour_end)->where('ora_out', '>', $hour_end);
						});
					})->where('disciplina', $request->disciplina)
					->pluck('contact_id')->toArray();

					$teachers_by_day[$day->format('d-m-Y')] = Contact::join('masters', 'contacts.id', '=', 'masters.contact_id')->select('contacts.*', 'masters.id as master_id')->where('contacts.contact_type_id', 3)->whereIn('contacts.id', $teachers_available)->orderBy('contacts.cognome', 'asc')->get();

					/*
					foreach($teachers_by_day as $key=>$teacher) {
						foreach($teachers_by_day[$key])
						
					}
					*/
	                  
					/*
						$ora = new Ora;
						$ora->data = $date_start;
						$ora->ora_in = $request->ora_in;
						$ora->ora_out = $request->ora_out;
						if(isset($request->label))
							$ora->id_cliente = 'L_'.$request->label;
						else
							$ora->id_cliente = isset($request->partecipante) ? 'T_'.$request->partecipante : 'Y_'.$request->cliente;

						$ora->id_maestro = $request->maestro;
						$ora->pax = $request->pax;
						$ora->ritrovo = $ritrovo_desc;
						$ora->disciplina = $request->disciplina;
						$ora->venditore = $request->venditore;

						if($request->venditore == 'S'){
							$ora->nome_venditore = auth()->user()->name;
						} elseif($request->venditore == 'M'){
							$ora->nome_venditore = Contact::where('id', $request->maestro_v)->first()->fullname;
						}
					
						$ora->note = $request->note;
						$ora->specialita = $request->lista_spec;
						$ora->id_alloggio = $request->alloggio;
						$ora->id_cc = $request->branches;
						$ora->save();

						$ora_id = $ora_id != '' ? $ora_id.','.$ora->id : $ora->id;
					*/
	            }
	        }

			if(!isset($maestri))
				$maestri = [];
	       
	        $obj = [
	            'collettivo_allievi_id' => $collettivo_allievi_id,
	            'maestri' => $maestri,
	        ];

			// Hour info to be send to the second function
			$hour_info = [
				'pax' => $request->pax,
				'ritrovo' => $ritrovo_desc,
				'disciplina' => $request->disciplina,
				'venditore' => $request->venditore,
				'note' => $request->note,
				'specialita' => $request->lista_spec,
				'id_alloggio' => $request->alloggio,
				'id_cc' => $request->branches,

				'ora_in' => $request->ora_in,
				'ora_out' => $request->ora_out,
			];

			if(isset($request->label))
				$hour_info['id_cliente'] = 'L_'.$request->label;
			else
				$hour_info['id_cliente'] = isset($request->partecipante) ? 'T_'.$request->partecipante : 'Y_'.$request->cliente;
			
			if($request->venditore == 'S'){
				$hour_info['nome_venditore'] = auth()->user()->name;
			} elseif($request->venditore == 'M'){
				$hour_info['nome_venditore'] = Master::where('id', $request->maestro_v)->first()->contact->fullname;
			}

	        \DB::commit();
	        $res['code'] = true;
	        $res['message'] = 'Successo!';
	        $res['data'] = $obj;
	       
			return [
				'code' => true,
				'message' => 'Successo!',
				'data' => $obj,
				'hour_info' => $hour_info,
				'teachers' => $teachers_by_day
			];
	    }
	    catch(Exception $e) {
	        \DB::rollback();
	        $res['code'] = false;
	        $res['message'] = 'Operation failed!!';
	        $res['data'] = $e->getMessage();
	        echo json_encode($res);

	    }

	}

	function insertOraStep2ByCollettivo(Request $request){
	    $res = array(); 
	    try{
	                  
	        $c= CollettivoAllievi::find($request->colletivo_allievi_id);
	        $c->livello = $request->livello;
	        $c->eta = $request->eta;
	        $c->save();

	        

	        $res['code'] = true;
	        $res['message'] = 'Successo!';
	        $res['data'] = $request->colletivo_allievi_id;
	        echo json_encode($res);
	    }
	    catch(Exception $e) {
	        $res['code'] = false;
	        $res['message'] = 'Operation failed!!';
	        $res['data'] = $e->getMessage();
	        echo json_encode($res);

	    }
	}


	/**
	 * teachers => 01-01-2023,14560 (id table masters)
	 */
	function insertOraStep2ByCliente(Request $request){
		$teachers = [];

		foreach($request->teachers as $teacher) {
			$data = explode(',', $teacher);
			
			$teachers[] = [
				'day' => $data[0],
				'contact_id' => $data[1],
			];
		}

		foreach ($teachers as $teacher) {
			$ora = new Ora;
			$date = \Carbon\Carbon::createFromFormat('d-m-Y', $teacher['day'])->format('Y-m-d');
			$ora->data = $date;
			$ora->ora_in = $request->hour_info['ora_in'];
			$ora->ora_out = $request->hour_info['ora_out'];
			$ora->id_cliente = $request->hour_info['id_cliente'];

			// Contact ID
			$teacher_db = Master::where('contact_id', $teacher['contact_id'])->first();
			$ora->id_maestro = $teacher_db->id;
			$ora->richiesto = $request->richiesto;
			$ora->pax = $request->hour_info['pax'];
			$ora->ritrovo = $request->hour_info['ritrovo'];
			$ora->disciplina = $request->hour_info['disciplina'];
			$ora->venditore = $request->hour_info['venditore'];
			$ora->note = $request->hour_info['note'];
			$ora->specialita = $request->hour_info['specialita'];
			$ora->id_alloggio = $request->hour_info['id_alloggio'];
			$ora->id_cc = $request->hour_info['id_cc'];
			$ora->save();

			$fattura_id = $this->manageInvoice($ora, $request->aperta);
		}

		$res['code'] = true;
		$res['message'] = 'Successo!';
		$res['data'] = $fattura_id; // not used
		echo json_encode($res);
	}


	function updateFatturaOra(Request $request){
	    $res = array(); 
	    try{

	        $original_invoice = Invoice::find($request->invoice_id);
			
			if(!$original_invoice){
				$res['code'] = false;
		        $res['message'] = 'Non trovo la fattura da aggiornare!!';
		        $res['data'] = $request->invoice_id;
		        echo json_encode($res);
		        
		        exit;
			}
			
			$invoice = $original_invoice->replicate();

	        $invoice->numero = $invoice->getMaxNumber($invoice->tipo, $invoice->branch_id);
	        $invoice->numero_registrazione = $invoice->getMaxNumber($invoice->tipo, $invoice->branch_id);
	        $invoice->data =  date("d/m/Y");
	        $invoice->data_registrazione =  date("d/m/Y");
	        $invoice->aperta = 0;
	        $invoice->save();

			// Move item
			$item = Item::where(['ora_id' => $request->hour_id, 'invoice_id' => $original_invoice->id])->first();
			$item->invoice_id = $invoice->id;
			$item->save();

			// Update invoice_ora table
			$invoice_ora = InvoiceOra::where(['ora_id' => $request->hour_id, 'invoice_id' => $original_invoice->id])->first();
			$invoice_ora->invoice_id = $invoice->id;
			$invoice_ora->save();

			// Check if original invoice is empty and delete
			$original_invoice_items = Item::where('invoice_id', $original_invoice->id)->get();
			if($original_invoice_items->count() < 1)
				$original_invoice->delete();

	        $res['code'] = true;
	        $res['message'] = 'Successo!';
	        $res['data'] = $invoice->id;
	        echo json_encode($res);
	    }
	    catch(Exception $e) {
	        $res['code'] = false;
	        $res['message'] = 'Operation failed!!';
	        $res['data'] = $e->getMessage();
	        echo json_encode($res);

	    }
	}
	
	function getLevel(Request $request){
		
		return Contact::findOrFail($request->id)->livello;
		
	}
	
	function getDisabled(Request $request){
		
		$disabled = (\DB::table('contact_disabled')->where('contact_id', $request->id)->first()) ? 'S' : 'N';
		
		if($disabled == 'S'){
			return 'true';
		} else {
			return 'false';
		}		
		
	}
	
	function getOraById(Request $request){
	    $res = array(); 
	    try{

	        $ora= Ora::find($request->id_ora);
	        
	        if(!$ora){
	        	$res['code'] = false;
		        $res['message'] = 'Non ho trovato l\'ora!';
		        $res['data'] = $request->id_ora;
		        echo json_encode($res);
	        	
	        	exit;
	        }
	        
	        $rit = Hangout::where('luogo',$ora->ritrovo)->first();
	        $ora->ritrovo_id = $rit != null ? $rit->id : null;

	        $inv_ora = \DB::table('invoice_ora')->where('ora_id', $request->id_ora)->first();
			if($inv_ora)
	        	$inv = Invoice::where('id',  $inv_ora->invoice_id)->first();
			else
				$inv = null;
	    
	        $ora->invoice_id = $inv_ora != null ? $inv_ora->invoice_id : null;
	        $ora->saldato = $inv != null ? $inv->saldato : null;
	        $ora->item_label = "";
	        $ora->livello = "";
	        list($tipo, $id_cliente) = explode('_', $ora->id_cliente);
	        if($tipo == 'C'){
	        	$item = Collettivo::find($id_cliente);
	            $ora->item_label = '<a tabindex=\'1\' href="/collective/'.$item->id.'">'.$item->nome.'</a>';
	        }
	        if($tipo == 'T'){
	            $cont = Contact::find($id_cliente);
	            $ora->item_label = '<a tabindex=\'1\' href="/contacts/'.$cont->id.'">'.$cont->nome.' '.$cont->cognome.'</a>';
	            $ora->livello = $cont->livello;
	            $ora->nome_cliente = $cont->nome.' '.$cont->cognome;
	        }
	        if($tipo == 'Y'){
	            $com = Company::find($id_cliente);
	            $ora->item_label = '<a tabindex=\'1\' href="/companies/'.$com->id.'">'.$com->rag_soc.'</a>';
	            $ora->nome_cliente = $com->rag_soc;
	        }
	        if($tipo == 'L'){
	            $lbl = Label::find($id_cliente);
	            $ora->item_label = $lbl->nome;
	        }


	        $res['code'] = true;
	        $res['message'] = 'Successo!';
	        $res['data'] = $ora;
	        echo json_encode($res);
	    }
	    catch(Exception $e) {
	        $res['code'] = false;
	        $res['message'] = 'Operation failed!!';
	        $res['data'] = $e->getMessage();
	        echo json_encode($res);

	    }
	}



	function deleteOraById(Request $request){
	    \DB::beginTransaction();
	    $res = array(); 
	    try{
	        Item::where('invoice_id', $request->id_invoice)->where('ora_id', $request->id_ora)->delete();
	        \DB::table('invoice_ora')->where('ora_id', $request->id_ora)->where('invoice_id', $request->id_invoice)->delete();
	        if(Item::where('invoice_id', $request->id_invoice)->count() == 0)
	            Invoice::where('id', $request->id_invoice)->delete();

	        

	        Ora::find($request->id_ora)->delete();

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
	        $res['data'] = $e->getMessage();
	        echo json_encode($res);

	    }
	}


	function addDocument(Request $request){
	    $res = array(); 
	   	
	   	if(!$request->prodotto_doc){
	   		$res['code'] = false;
	        $res['message'] = 'Prodotto non selezionato';
	        $res['data'] = $request->prodotto_doc;
	        echo json_encode($res);
	   	}
	   	
	    try{
	        $invoice = Invoice::where('tipo', $request->tipo_doc)
	                            ->where('numero', $request->n_doc)
	                            ->where('data', $request->data_doc)
	                            ->first();
	        if($invoice != null){
	        	$lista_ore = explode('-', $request->ora_id);
	            $ore = Ora::whereIn('id', $lista_ore)->get();
				
				foreach($ore as $ora){
		            $nome_disciplina = 'Discesa';
				    if($ora->disciplina == 2 || $ora->disciplina == 3)
				        $nome_disciplina = 'Fondo';
				    if($ora->disciplina == 4)
				        $nome_disciplina = 'Snowboard';


		            $elenco_maestri = "";
		            $teacher = Master::find($ora->id_maestro);
		    		$mm= Contact::where('id', $teacher->contact_id)->get();
		            foreach ($mm as $value) {
		                $elenco_maestri = $elenco_maestri != '' ? $elenco_maestri.','.$value->nome.' '.$value->cognome : $value->nome.' '.$value->cognome;
		            }

		            $elenco_spec = "";
		            $specs= Specialization::whereIn('id', [$ora->specialita])->get();
		            foreach ($specs as $value) {
		                $elenco_spec = $elenco_spec != '' ? $elenco_spec.','.$value->nome : $value->nome;
		            }

		            $descr = "<b>Data:</b> ".date('d/m/Y', strtotime($ora->data))."
							<br /><b>Ora:</b> dalle ".substr($ora->ora_in, 0, 5)." alle ".substr($ora->ora_out, 0, 5)."
		                    <br /><b>Ritrovo:</b> $ora->ritrovo
		                    <br /><b>Maestro:</b> $elenco_maestri
		                    <br /><b>Pax:</b> $ora->pax
		                    <br /><b>Disciplina:</b> $nome_disciplina
		                    <br /><b>Specialit&agrave;:</b> $elenco_spec";


		            $itemInvoice = Item::where('ora_id', $ora->id)->where('invoice_id', $request->invoice_id)->first();
		            
		            if($itemInvoice){
		            	$itemInvoice->product_id = $request->prodotto_doc;
			            $itemInvoice->descrizione = $descr;
			            $itemInvoice->invoice_id = $invoice->id;
			            $itemInvoice->update();
						
						if(InvoiceOra::where('invoice_id', $request->invoice_id)->where('ora_id', $ora->id)->exists()){
							$invoice_ora = InvoiceOra::where('invoice_id', $request->invoice_id)->where('ora_id', $ora->id)->update([
								'invoice_id' => $invoice->id,
							]);
						} else {
							\DB::table('invoice_ora')->insert(['invoice_id' => $invoice->id, 'ora_id' => $ora->id]);
						}
						
		            } else {
		            	$itemInvoice = new Item();
		            	$itemInvoice->product_id = $request->prodotto_doc;
			            $itemInvoice->descrizione = $descr;
			            $itemInvoice->qta = 1;
			            $itemInvoice->importo = 0;
			            $itemInvoice->sconto = 0;
			            $itemInvoice->perc_iva = 0;
			            $itemInvoice->iva = 0;
			            $itemInvoice->invoice_id = $invoice->id;
			            $itemInvoice->exemption_id = 12;
			            $itemInvoice->ora_id = $ora->id;
			            $itemInvoice->save();
			            
			            \DB::table('invoice_ora')->insert(['invoice_id' => $invoice->id, 'ora_id' => $ora->id]);
		            }		            
			
					if(Invoice::find($request->invoice_id)){
						if(Invoice::find($request->invoice_id)->items->count() == 0 && \DB::table('invoice_ora')->where('invoice_id', $request->invoice_id)->count() == 0)
							Invoice::find($request->invoice_id)->delete();	
					}
					
				}			

	            $res['code'] = true;
	            $res['message'] = 'Successo!';
	            $res['data'] = $invoice->id;
	            echo json_encode($res);
	        }
	        else{
	            $res['code'] = false;
	            $res['message'] = 'Fattura non trovata';
	            $res['data'] = null;
	            echo json_encode($res);
	        }           
	    }
	    catch(Exception $e) {
	        $res['code'] = false;
	        $res['message'] = 'Operation failed!!';
	        $res['data'] = $e->getMessage();
	        echo json_encode($res);

	    }
	}

	function updateOra(Request $request){
		
	    \DB::beginTransaction();
	    $res = array(); 
	    
	    try{
			$maestro = Master::find($request->maestro);
			
			if(!$maestro){
	            $res['code'] = false;
	            $res['message'] = 'Devi selezionare un maestro! ';
	            $res['data'] = null;
	            echo json_encode($res);
	            return;
	        }
	        
	        $contact_id = $maestro->contact_id;

	        //PRIMA VERIFICHIAMO LA DISPONIBILITA NUOVO MAESTRO E NUOVE DATE
	        $query = "select count(*) as total from availabilities 
	                                where contact_id  = $contact_id  
	                                and data_start <= \"$request->data_in\" 
	                                and \"$request->data_in\" <= data_end ";

	        $maestro_disp_in_gg = intval(\DB::select($query)[0]->total);

	        if($maestro_disp_in_gg == 0){
	            $res['code'] = false;
	            $res['message'] = 'Il Maestro selezionato non è disponibile! ';
	            $res['data'] = null;
	            echo json_encode($res);
	            return;
	        }
			
			
	        $query_disp = "
	                                SELECT count(*) as count 
	                                FROM ora 
	                                WHERE id_maestro = $request->maestro and data = \"$request->data_in\" and id != $request->ora_id 
	                                    and (
		                                    ora_in < \"$request->ora_in\"  and \"$request->ora_in\"  < ora_out or
		                                    ora_in < \"$request->ora_out\"  and \"$request->ora_out\"  < ora_out
	                                    )";
	                                    



	        $count_ora_impegnata = intval(\DB::select($query_disp)[0]->count);
         
	        if($count_ora_impegnata > 0){
	            $res['code'] = false;
	            $res['message'] = 'L\'ora selezionata risulta già impegnata';
	            $res['data'] = null;
	            echo json_encode($res);
	            return;
	        }
	        
	        if(strstr($request->client, 'null') || is_null($request->client) || $request->client == '' || strlen($request->client) < 3){
	            $res['code'] = false;
	            $res['message'] = 'Devi selezionare il cliente!';
	            $res['data'] = null;
	            echo json_encode($res);
	            return;
	        }

	       	// se il cliente è stato cambiato, bisogna:
	       	// - cambiare l'id cliente nell'ora
	       	// - controllare se quel cliente ha già una fattura aperta, in caso contrario crearla
	       	// - spostare l'item sotto la fattura del cliente nuovo e aggiornare la descrizione
	       	// - se la fattura collegata al cliente vecchio è rimasta vuota va cancellata e tolta anche da invoice_ora
	       	// - controllare che la fattura del cliente nuovo sia presente anche su invoice_ora
	       	
	       	         
	        $rit = Hangout::find($request->ritrovo);
	        $ritrovo_desc = $rit != null ? $rit->luogo : '';
	        
	        $ora = Ora::find($request->ora_id);
	        $ora->data = $request->data_in;
//	        $ora->id_cliente = $request->client;
	        $ora->ora_in = $request->ora_in;
	        $ora->ora_out = $request->ora_out;
	        $ora->pax = $request->pax;
	        $ora->ritrovo = $ritrovo_desc;
	        $ora->disciplina = $request->disciplina;
	        $ora->specialita = $request->lista_spec;
	        $ora->venditore = $request->venditore;
	        $ora->note = $request->note;
	        $ora->id_alloggio = $request->alloggio;
	        $ora->id_cc = $request->branches;
	        $ora->id_maestro = $request->maestro;
	        $ora->richiesto = $request->richiesto;
	        $ora->save();

	        $nome_disciplina = 'Discesa';
		    if($ora->disciplina == 2)
		        $nome_disciplina = 'Fondo';
		    if($ora->disciplina == 3)
		        $nome_disciplina = 'Fondo';
		    if($ora->disciplina == 4)
		        $nome_disciplina = 'Snowboard';
	    
	        $elenco_spec = "";
	        $specs= Specialization::whereIn('id', [$ora->specialita])->get();
	     
	        foreach ($specs as $value) {
	            $elenco_spec = $elenco_spec != '' ? $elenco_spec.','.$value->nome : $value->nome;
	        }
	    	
	    	if(substr($ora->id_cliente, 0, 1) == 'T'){
	    		list($tipo, $id_cliente) = explode('_', $ora->id_cliente);
	    		Contact::where('id', $id_cliente)->update(['livello'=> $request->livello]);	
	    	}
	    	
	        $elenco_maestri = "";
	        $teacher = Master::find($ora->id_maestro);
	    	$mm= Contact::where('id', $teacher->contact_id)->get();
	        foreach ($mm as $value) {
	            $elenco_maestri = $elenco_maestri != '' ? $elenco_maestri.','.$value->nome.' '.$value->cognome : $value->nome.' '.$value->cognome;
	        }
	    
	        $descr = "<b>Data:</b> ".date('d/m/Y', strtotime($ora->data))."
	                <br /><b>Ora:</b> dalle ".substr($ora->ora_in, 0, 5)." alle ".substr($ora->ora_out, 0, 5)."
	                <br /><b>Ritrovo:</b> $ora->ritrovo
	                <br /><b>Maestro:</b> $elenco_maestri
	                <br /><b>Pax:</b> $ora->pax
	                <br /><b>Disciplina:</b> $nome_disciplina
	                <br /><b>Specialit&agrave;:</b> $elenco_spec";

	        $itemInvoice = Item::where('ora_id', $request->ora_id)->first();
	      
	        if($itemInvoice != null){
	            $itemInvoice->descrizione = $descr;      
	            $itemInvoice->save();
	        }
	        
	        
/*			if($request->cliente){
				$client = 'Y_'.$request->cliente;
			} elseif($request->partecipante){
				$client = 'T_'.$request->partecipante;
			} else {
				$client = $hour->id_cliente;
			}*/
			
			$client = $request->client;
			
			if($client != $ora->id_cliente){
				
				if(substr($client, 0, 1) == 'Y'){
					
					$nuova_fatt = Invoice::where('company_id', substr($client, 2))->where('aperta', 1)->first();
					
					$element = \DB::table('company_branch')->where('company_id', substr($client, 2))->first();
		            if($element != null){
		                $branch_id = $element->branch_id;
		            } else {
		            	$branch_id = auth()->user()->contact->branchContact()->branch_id;
		            }
		        
		        } elseif(substr($client, 0, 1) == 'T'){
		        	
		        	$nuova_fatt = Invoice::where('contact_id', substr($client, 2))->where('aperta', 1)->first();
					
					$element = \DB::table('contact_branch')->where('contact_id', substr($client, 2))->first();
		            if($element != null){
		                $branch_id = $element->branch_id;
		            } else {
		            	$branch_id = auth()->user()->contact->branchContact()->branch_id;
		            }
		            
		        } else {
		        	$nuova_fatt = null;
		        	$branch_id = auth()->user()->contact->branchContact()->branch_id;
		        }
			            
				if(!$nuova_fatt && substr($client, 0, 1) != 'L'){
					  
					$nuova_fatt = new Invoice;
			        $nuova_fatt->tipo_doc = 'Pr';
			        $nuova_fatt->tipo = 'R';
			        $nuova_fatt->numero = null;
			        $nuova_fatt->numero_registrazione = null;
			        $nuova_fatt->data = date('Y-m-d');
			        $nuova_fatt->data_registrazione = date('Y-m-d');
			        if(substr($client, 0, 1) == 'Y'){
			        	$nuova_fatt->company_id = substr($client, 2);
			        	$nuova_fatt->contact_id = null;
			        } elseif(substr($client, 0, 1) == 'T'){
			        	$nuova_fatt->company_id = null;
			        	$nuova_fatt->contact_id = substr($client, 2);
			        }
			        
			        $nuova_fatt->branch_id = $branch_id;

			        $nuova_fatt->riferimento = null;

			        $nuova_fatt->pagamento = 'RIDI';
			        $nuova_fatt->tipo_saldo = null;
			        $nuova_fatt->data_saldo = null;
			        $nuova_fatt->data_scadenza = null;
			        $nuova_fatt->aperta = 1;

			        $nuova_fatt->spese = 0.00;
			        $nuova_fatt->perc_ritenuta = 0.00;
			        $nuova_fatt->rate = null;
			        $nuova_fatt->saldato = 0;
		        	$nuova_fatt->bollo = null;
		        	$nuova_fatt->bollo_a = null;
			        

			        $nuova_fatt->pa_n_doc = null;
			        $nuova_fatt->pa_data_doc = null;
			        $nuova_fatt->pa_cup = null;
			        $nuova_fatt->pa_cig = null;
			        $nuova_fatt->ddt_n_doc = null;
			        $nuova_fatt->ddt_data_doc = null;

			        $nuova_fatt->split_payment = null;

			        $nuova_fatt->save();
				}
				
				if(substr($client, 0, 1) != 'L'){
					$vecchia_fatt_id = $itemInvoice->invoice_id;
					$vecchia_fatt = Invoice::find($vecchia_fatt_id);
					
					$itemInvoice->invoice_id = $nuova_fatt->id;	
					$itemInvoice->save();
										
					if(\DB::table('invoice_ora')->where('invoice_id', $vecchia_fatt_id)->where('ora_id', $ora->id)->exists()){
			    		\DB::table('invoice_ora')->where('invoice_id', $vecchia_fatt_id)->where('ora_id', $ora->id)
			                                ->update([
			                                            'invoice_id' =>$nuova_fatt->id
			                                    ]);	
			    	} else {
			    		\DB::table('invoice_ora')->insert(['invoice_id' => $nuova_fatt->id, 'ora_id' => $ora->id]);
			    	}
			    	
					if($vecchia_fatt->items->count() == 0){
						$vecchia_fatt->delete();
					}
				}
				
			}
			
			$ora->id_cliente = $client;
			$ora->save();
	        
	        \DB::commit();
	        $res['code'] = true;
	        $res['message'] = 'Successo!';
	        $res['data'] = $request->invoice_id;
	        echo json_encode($res);
	    }
	    catch(Exception $e) {
	        \DB::rollback();
	        $res['code'] = false;
	        $res['message'] = 'Operation failed!!';
	        $res['data'] = $e->getMessage();
	        echo json_encode($res);

	    }
	}


	function addDocumentsItems(Request $request){
	    $res = array(); 
	    try{
	        $invoice = Invoice::where('tipo', $request->tipo_doc)
	                            ->where('numero', $request->n_doc)
	                            ->where('data', $request->data_doc)
	                            ->first();
	        if($invoice != null){

	            $ids_ore = explode(",",$request->ids_ore);
				$ids_invoices = explode(",",$request->ids_invoices);
		
	            foreach ($ids_ore as $invoice_ore_id) {
	            	if(strstr($invoice_ore_id, '*')){
	            		list($invoice_ore_id, $cliente) = explode('*', $invoice_ore_id);
	            	}
	            	
					$invoice_hours = explode('-', $invoice_ore_id);
					
					foreach($invoice_hours as $ora_id) {
						$ora= Ora::find($ora_id);

						$nome_disciplina = 'Discesa';
						if($ora->disciplina == 2)
							$nome_disciplina = 'Fondo';
						if($ora->disciplina == 3)
							$nome_disciplina = 'Fondo';
						if($ora->disciplina == 4)
							$nome_disciplina = 'Snowboard';
	
	
						$elenco_maestri = "";
						$teacher = Master::find($ora->id_maestro);
						$mm= Contact::where('id', $teacher->contact_id)->get();
						foreach ($mm as $value) {
							$elenco_maestri = $elenco_maestri != '' ? $elenco_maestri.','.$value->nome.' '.$value->cognome : $value->nome.' '.$value->cognome;
						}
	
						$elenco_spec = "";
						$specs= Specialization::whereIn('id', [$ora->specialita])->get();
						foreach ($specs as $value) {
							$elenco_spec = $elenco_spec != '' ? $elenco_spec.','.$value->nome : $value->nome;
						}
						
						if(substr($ora->id_cliente, 0, 1) == 'C'){
							$descr = "<b>Corso collettivo:</b> " . Collettivo::find(substr($ora->id_cliente, 2))->nome ." 
									<br /><b>Data:</b> ".date('d/m/Y', strtotime($ora->data))."
									<br /><b>Ora:</b> dalle ".substr($ora->ora_in, 0, 5)." alle ".substr($ora->ora_out, 0, 5)."
									<br /><b>Maestro:</b> $elenco_maestri
									<br /><b>Disciplina:</b> $nome_disciplina
									<br /><b>Specialit&agrave;:</b> $elenco_spec";				
						} else {
							$descr = "<b>Data:</b> ".date('d/m/Y', strtotime($ora->data))."
									<br /><b>Ora:</b> dalle ".substr($ora->ora_in, 0, 5)." alle ".substr($ora->ora_out, 0, 5)."
									<br /><b>Ritrovo:</b> $ora->ritrovo
									<br /><b>Maestro:</b> $elenco_maestri
									<br /><b>Pax:</b> $ora->pax
									<br /><b>Disciplina:</b> $nome_disciplina
									<br /><b>Specialit&agrave;:</b> $elenco_spec";
						}
						
						
						$itemInvoice = Item::where('ora_id', $ora_id)->whereIn('invoice_id', $ids_invoices)->first();
						if($itemInvoice){
							$itemInvoice->product_id = $request->prodotto_doc;
							$itemInvoice->descrizione = $descr;
							$itemInvoice->invoice_id = $invoice->id;
							$itemInvoice->update();
						} else {
							$product = Product::find($request->prodotto_doc);
							// calcolo quante ore devo inserire
							$origin = date_create($ora->data . ' ' . substr($ora->ora_in, 0, 8));
							$target = date_create($ora->data . ' ' . substr($ora->ora_out, 0, 8));
							$interval = date_diff($origin, $target);
							$diff_ore = $interval->format('%H');
							$diff_min = $interval->format('%I');
							if($diff_min == 30){
								$diff_ore += 0.5;
							}
							
							$itemInvoice = new Item();
							$itemInvoice->product_id = $product->id;
							$itemInvoice->descrizione = $descr;
							$itemInvoice->invoice_id = $invoice->id;
							$itemInvoice->qta = $diff_ore;
	            			$itemInvoice->importo = $product->prezzo;
						    $itemInvoice->perc_iva = $product->perc_iva;
						    $itemInvoice->iva = $product->prezzo * ($product->perc_iva / 100);
							$itemInvoice->exemption_id = $product->exemption_id;
							$itemInvoice->ora_id = $ora_id;							
							$itemInvoice->save();
						}
						
												

						// Update invoice_ora
						$invoice_ora = InvoiceOra::where('ora_id', $ora_id)->whereIn('invoice_id', $ids_invoices)->first();
						if($invoice_ora) {
							$invoice_ora->invoice_id = $invoice->id;
							$invoice_ora->save();
						} else {
							\DB::table('invoice_ora')->insert(['invoice_id' => $invoice->id, 'ora_id' => $ora_id]);
						}
					}	                
	            }
	            
	            $res['code'] = true;
	            $res['message'] = 'Successo!';
	            $res['data'] = $invoice->id;
	            echo json_encode($res);
	        
	        }
	        else{
	            $res['code'] = false;
	            $res['message'] = 'Documento non trovato!';
	            $res['data'] = null;
	            echo json_encode($res);
	        }           
	    }
	    catch(Exception $e) {
	        $res['code'] = false;
	        $res['message'] = 'Operation failed!!';
	        $res['data'] = $e->getMessage();
	        echo json_encode($res);

	    }
	}
	
	
	function small_calendar($mese, $anno, $id, Request $request){
		
		if ($request->x == ""){
			$mese_ = $mese;
			$anno_ = $anno;
			$giorno_ = date("d");
		} else {
			$mese_ = (int)strftime( "%m" ,(int)$request->x);
			$anno_ = (int)strftime( "%Y" ,(int)$request->x);
			$giorno_ = (int)strftime( "%d" ,(int)$request->x);
		}

		$response = '';
		
		$prev = mktime(0, 0, 0, $mese_ -1, 1,  $anno_);
		$next = mktime(0, 0, 0, $mese_ +1, 1,  $anno_);


		$human_month = array("error", "Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre" ); 


		$settimana   = array("Lun", "Mar", "Mer", "Gio", "Ven", "Sab", "Dom"); 
		$colonne     = 7;

		//giorni del mese in questione
		$giorni = date("t",mktime(0, 0, 0, $mese_, 1, $anno_));  

		//trovo il primo lunedÃ¬ del mese in questione
		$giorno_in = date('w' , mktime (0, 0, 0, $mese_, 1, $anno_) ); //numero da 0 a 6 che identifica il giorno della settimana
		$_lun = (9 - $giorno_in);

		if( $_lun > 7 ) {  
			$_lun = $_lun - 7;  
		}

		$primo_lunedi = date('d', mktime(0, 0, 0, $mese_, $_lun, $anno_)); 


		$response .= "<table width=\"70%\" cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"tabelle\" id=\"calendarietto\">"; //table
		$response .= "\n\t<tr height=\"20\">\n\t\t<td colspan=\"".$colonne."\" align=\"center\" class=\"intestazione\"><a href=\"?x=".$prev."&id=$id#calendarietto\">&lt;&lt;</a> <span>".$human_month[(int)$mese_]." ".$anno_."</span> <a tabindex=\'1\' href=\"?x=".$next."&id=$id#calendarietto\">&gt;&gt;</a></td>\n\t</tr>"; //mese/anno

		foreach($settimana as $val){
			$response .= "\n\t\t<td height=\"20\" class=\"intestazione\">".$val."\t</td>";
		}

		$response .= "</tr>";

		//eccezione per i mesi che iniziano la domenica
		if($giorno_in == 0){
			$response .= "<tr>";
			for($i = 0; $i < $colonne - 1; $i++){
				$response .= "<td>&nbsp;</td>";
			}
			$response .= "<td>1</td>
						</tr>";
		}
	

		for($i = 0; $i < ($giorni + $giorno_in - 1); $i++){
			
			$k = $i + 1; //noi usiamo la settimana che inizia da lunedÃ¬
				
			if(($k % $colonne) + 1 == 0){
				$response .= "\n\t<tr>";
			}
			
			if($k < $giorno_in){
				$response .= "\n\t\t<td>&nbsp;</td>";
			} else {
			
				$giorno_= $k-($giorno_in-1);
				$a = strtotime(date($anno_."-".$mese_."-".$giorno_));
				$b = strtotime(date("Y-m-d"));
				
				$id_data = Availability::where('contact_id', $id)->where('data_start', '<=', "$anno_-$mese_-$giorno_")->where('data_out', '>=', "$anno_-$mese_-$giorno_")->get();
						  		
		  		if($id_data){
		  			$classe .= "occupato";
		  		} 
		  		
		  		if($a == $b){
		  			$classe .= " selected";	
		  		}
		  		
		  		$response .= "\n\t\t<td class=\"$classe\">$giorno_</td>";
		  		  		
		  		$classe = "";
		  		$id_data = "";
		  	
			}
			
			if(($k % $colonne) == 0){
				$response .= "\n\t</tr>";
			}
			
		} 

		//chiudo la tabella
		$ultimo_giorno = date('w' , mktime (0, 0, 0, $mese_, $giorni, $anno_) );

		//riempio gli spazi vuoti
		for($z = 0; $z <= (7 - ($ultimo_giorno + 1)); $z++){
			$response .= "\n\t\t<td>&nbsp;</td>";
		}
		$response .= "\n\t</tr>";	
		$response .= "\n</table>";	
		
		return $response;

	}
	
	public static function scheduler(){
		
		$today = strtotime(date("d-m-Y"));
		
		if(date("m") <= 10){
			$anno_meno = date("Y")-1;
			$date_in = "01-12-$anno_meno";
			$date_out = "15-04-".date("Y");
		} else {
			$anno_piu = date("Y")+1;
			$date_in = "01-12-".date("Y");
			$date_out = "15-04-$anno_piu";
		}
		
		$event_date_in = strtotime($date_in); 
		$event_date_out = strtotime($date_out); 
		
		if($event_date_in <= $today && $today <= $event_date_out){
		
		
			//setto le variabili data e ora
			$oggi = date("d.m.Y");
			$oggidb = date("Y-m-d");
			
			$domani = date('d/m/Y', mktime(0,0,0,date('m'),date('d')+1,date('Y')));
			$domanidb = date('Y-m-d', mktime(0,0,0,date('m'),date('d')+1,date('Y')));
			
			$dopodomani = date('d/m/Y', mktime(0,0,0,date('m'),date('d')+2,date('Y')));
			$dopodomanidb = date('Y-m-d', mktime(0,0,0,date('m'),date('d')+2,date('Y')));
			
			$elenco_mail_inviate = "";
			
			$maestri = Master::whereHas('availability', function($q) use($domanidb) {
									$q->whereDate('data_start', '<=', $domanidb)->whereDate('data_end', '>=', $domanidb);
								})->get();
								
			foreach($maestri as $maestro){
				
				$ore = Ora::where('data', $domanidb)->where('id_maestro', $maestro->id)->orderBy('data')->orderBy('ora_in')->get();
				$elenco = '';
				
				foreach($ore as $ora){
					
					switch($ora->venditore){
			  			case "S":
			  				$nome_vend = "Segreteria";
			  				break;
			  			case "N":
			  				$nome_vend = "Noleggio";
			  				break;
			  			case "H":
			  				$nome_vend = "Altro";
			  				break;
			  			case "M":
			  				$m = Master::find($ora->nome_venditore)->contact->fullname;
			  				$nome_vend = "Maestro $m";
			  				break;
			  			case "P":
			  				$nome_vend = "Prevendita";
			  				break;
			  		}
			  		
			  		$note = $ora->note;
			  		$ritrovo = $ora->ritrovo;
			  		
			  		if($note == ""){
		  				$note = "&nbsp;";
			  		}
			  		if($ritrovo == ""){
			  			$ritrovo = "&nbsp;";
			  		}
			  		
			  		$ora_in = substr($ora->ora_in, 0, -3);
			  		$ora_out = substr($ora->ora_out, 0, -3);
			  		
			  		if(substr($ora->id_cliente, 0, 1) == 'C'){
			  			
			  			$coll = Collettivo::find(substr($ora->id_cliente, 2));
			  			$nome_c = 'Collettivo ' . $coll->nome;
			  			$cognome_c = '';
			  			$tel_c = '';
			  			$cell_c = '';
			  			$specialita = $coll->specialita;
			  			
			  		} elseif(substr($ora->id_cliente, 0, 1) == 'Y') {
			  			
			  			$company = Company::find(substr($ora->id_cliente, 2));
			  			$nome_c = $company->rag_soc;
			  			$cognome_c = '';
			  			$tel_c = $company->phone;
			  			$cell_c = $company->mobile;
			  			$specialita = $ora->specialita;
			  			
			  		} elseif(substr($ora->id_cliente, 0, 1) == 'T') {
			  			
			  			$contact = Contact::find(substr($ora->id_cliente, 2));
			  			$nome_c = $contact->nome;
			  			$cognome_c = $contact->cognome;
			  			$tel_c = '';
			  			$cell_c = $contact->cellulare;
			  			$specialita = $ora->specialita;
			  			
			  		} elseif(substr($ora->id_cliente, 0, 1) == 'L') {
			  			
			  			$label = Label::find(substr($ora->id_cliente, 2));
			  			$nome_c = $label->nome;
			  			$cognome_c = '(segnaposto)';
			  			$tel_c = '';
			  			$cell_c = '';
			  			$specialita = '';
			  			
			  		}
			  		
			  		if($specialita != ""){
						
					  	$lista_spec = explode(",", $specialita);
				  		$elenco_spec = '';
				  		
					  	for($i = 0; $i <= count($lista_spec)-1; $i++){
					  		
					  		if(Specialization::find($lista_spec[$i])){
					  			$elenco_spec .= '- ' . Specialization::find($lista_spec[$i])->nome . '<br>';
					  		}						  	
						  	
					  	}
				  	} else {
				  		$elenco_spec = "Nessuna";
				  	}
				  	
				  	list($a, $m, $g) = explode("-", $ora->data);
		  			$data = "$g/$m/$a";
		  			
		  			$elenco .= "<tr>	
	  								<td align=\"center\">
		  								$data
		  							</td>
		  							<td align=\"center\">
		  								dalle $ora_in alle $ora_out
		  							</td>
		  							<td align=\"center\">
		  								$nome_vend
		  							</td>
		  							<td align=\"center\">
		  								$ritrovo
		  							</td>
		  							<td align=\"center\">
		  								$elenco_spec
		  							</td>
		  							<td>
		  								<b>$nome_c $cognome_c</b><br>
		  								Tel.: $tel_c<br>
		  								Cell.: $cell_c
		  							</td>
		  							<td>
		  								$note
		  							</td>
		  						</tr>";
		  		
			  		$nome_c = "";
			  		$cognome_c = "";
			  		$tel_c = "";
			  		$cell_c = "";
			  		$elenco_spec = "";
			  		
				}
				
				$testo = "<table width=\"1000\" cellpadding=\"3\" cellspacing=\"0\" border=\"1\" style=\"border: 1px solid #0082DA;\">
							<tr style=\"background-color: #0082DA; color: white; font-weight: bold;\">	
							<td align=\"center\">
  								<b>DATA</b>
  							</td>
  							<td align=\"center\">
  								<b>ORARIO</b>
  							</td>
  							<td align=\"center\">
  								<b>VENDUTA DA</b>
  							</td>
  							<td align=\"center\">
  								<b>RITROVO</b>
  							</td>
  							<td align=\"center\">
  								<b>SPECIALIT&Agrave;</b>
  							</td>
  							<td align=\"center\">
  								<b>CLIENTE</b>
  							</td>
  							<td align=\"center\">
  								<b>NOTE</b>
  							</td>
  						</tr>
  						$elenco
  					</table>";
  					
  					// invia mail al maestro
  					$from = "info@sciedipassione.com";
  					$to = $maestro->contact->email;
  					
  					$elenco_mail_inviate .= "$to<br>";
					
					$content = $testo;
						
		    		$data = array(
		    			'from' => $from,
				        'setting' => Setting::base(),
				        'content' => $content,
				        'email' =>  $to,
				        'title' => "CALENDARIO ORE DEL GIORNO $domani",
				        'subject' => 'Scie di Passione - Calendario',       
				    );


				    config()->set('mail.host', Setting::smtp(1)['MAIL_HOST']);
				    config()->set('mail.port', Setting::smtp(1)['MAIL_PORT']);
				    config()->set('mail.encryption', Setting::smtp(1)['MAIL_ENCRYPTION']);
				    config()->set('mail.username', Setting::smtp(1)['MAIL_USERNAME']);
				    config()->set('mail.password', Setting::smtp(1)['MAIL_PASSWORD']);


				    \Mail::send('areaseb::emails.scheduler.master-mail',$data, function ($message) use ($data)
				    {
				        $message->to($data['email'])
				                ->subject($data['subject'])
				                ->from($data['from']);
				    });
				
			}
			
			// invia mail alla segreteria
			$from = "info@sciedipassione.com";
			
			$content = "Il calendario personale &egrave; stato inviato ai seguenti indirizzi:<br><br>

$elenco_mail_inviate";
				
    		$data = array(
    			'from' => $from,
		        'setting' => Setting::base(),
		        'content' => $content,
		        'email' =>  $from,
		        'title' => "CALENDARIO ORE DEL GIORNO $domani",
		        'subject' => 'Scie di Passione - Calendario',       
		    );


		    config()->set('mail.host', Setting::smtp(1)['MAIL_HOST']);
		    config()->set('mail.port', Setting::smtp(1)['MAIL_PORT']);
		    config()->set('mail.encryption', Setting::smtp(1)['MAIL_ENCRYPTION']);
		    config()->set('mail.username', Setting::smtp(1)['MAIL_USERNAME']);
		    config()->set('mail.password', Setting::smtp(1)['MAIL_PASSWORD']);


		    \Mail::send('areaseb::emails.scheduler.office-mail',$data, function ($message) use ($data)
		    {
		        $message->to($data['email'])
		                ->subject($data['subject'])
		                ->from($data['from']);
		    });
			
		}
		
		return 'Inviate';
	}

}
