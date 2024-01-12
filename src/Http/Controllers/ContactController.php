<?php

namespace Areaseb\Core\Http\Controllers;

use Areaseb\Core\Models\{City, Contact, Country, CollettivoAcconti, Ora, CollettivoAllievi, Client, Company, NewsletterList, Collettivo, Sede, Invoice, Item, TypeUser, DisabileSedute, DisabileTipo, DisabileAttrezzi, Specialization, Product, Master};
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use \Spatie\Permission\Models\Role;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->input())
        {
            if(request('id'))
            {
                // $query = Contact::with('client');
                $query = Contact::where('id', request('id'));
            }
            else
            {
                $query = Contact::filter(request());
            }
        }
        else
        {

            if(auth()->user()->hasRole('testimonial'))
            {
                $query = auth()->user()->testimonial->contacts();
            }
            elseif(auth()->user()->hasRole('agent'))
            {
                $query = auth()->user()->agent->contacts();
            }
            else
            {
                $query = Contact::query();
            }
        }

        $contacts = $query->orderby('created_at', 'DESC')->paginate(100);

        return view('areaseb::core.contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $companies[''] = '';
        $companies += Company::where('client_id', '!=', 4)->pluck('rag_soc', 'id')->toArray();
        $users[''] = '';
        $users += User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        $lists = NewsletterList::pluck('nome', 'id')->toArray();
        $pos = ['' => '']+Contact::uniquePos();
        $origins = ['' => '']+Contact::uniqueOrigin();

        $testimonials = $this->testimonialsArray();
        $agents = $this->agentsArray();
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        $contact_type = [''=>'']+TypeUser::where('id','!=', 3)->pluck('descrizione', 'id')->toArray();

        $disabile_sedute = [''=>'']+DisabileSedute::pluck('nome', 'id')->toArray();
        $disabile_tipo = [''=>'']+DisabileTipo::pluck('nome', 'id')->toArray();
        $disabile_attrezzi = [''=>'']+DisabileAttrezzi::pluck('nome', 'id')->toArray();
        $specializzazioni = [''=>'']+Specialization::pluck('nome', 'id')->toArray();

        return view('areaseb::core.contacts.create', compact('provinces', 'specializzazioni', 'contact_type', 'disabile_sedute', 'disabile_tipo','disabile_attrezzi','branches','countries', 'companies', 'users', 'lists', 'pos', 'origins', 'testimonials', 'agents'));
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
            'cognome' => 'required',
            'email' => 'required|email'
        ]);
        $data = request()->input();
        //dd($data);
        $data['_method'] = 'POST';
        $contact = Contact::createOrUpdate(new Contact, $data);

        if(!is_null($request->list_id))
        {
            if(count($request->list_id) > 0)
            {
                $contact->lists()->attach($request->list_id);
            }
        }

        if(isset($request->testimonial_id))
        {
            \Areaseb\Referrals\Models\Testimonial::find($request->testimonial_id)->contacts()->attach($contact->id);
        }

        if(isset($request->agent_id))
        {
            \Areaseb\Agents\Models\Agent::find($request->agent_id)->contacts()->attach($contact->id);
        }

        if(isset($request->prev))
        {
            return redirect($request->prev)->with('message', 'Contatto Aggiunto');
        }

        return redirect(route('contacts.index'))->with('message', 'Contatto Creato');
    }

	public static function getStoricoOreCliente($client_id)
    {
        // Ore
        $invoices = Invoice::where('contact_id', $client_id)->where('aperta', 0)->pluck('id')->toArray();
//        $invoices_items = Item::whereIn('invoice_id', $invoices)->pluck('ora_id')->toArray();
//        $invoices_items = array_filter($invoices_items, fn ($item) => !is_null($item));  // pulisce dai valori null
		$invoices_items = \DB::table('invoice_ora')->whereIn('invoice_id', $invoices)->pluck('ora_id')->toArray();
        
        $items = Ora::where('id_cliente', 'T_'.$client_id)->whereIn('id', $invoices_items)->get();
        
        foreach ($items as $item) {

            $maestro = Master::find($item->id_maestro)->contact;
            if($maestro != null)
                $item->maestro = $maestro->nome.' '.$maestro->cognome;
            
            $item->invoice_id = Item::where('ora_id', $item->id)->whereIn('invoice_id', $invoices)->first()->invoice_id;
            
            $item->sede_lbl = '';
            $sede = Sede::find($item->id_cc);
            if($sede != null)
                $item->sede_lbl = $sede->nome;

            $item->disciplina_desc = "";

            switch ($item->disciplina) {
                case 1:
                    $item->disciplina_desc = "Discesa";
                    break;
                case 2:
                    $item->disciplina_desc = "Fondo";
                    break;
                case 3:
                    $item->disciplina_desc = "Fondo";
                    break;
                case 4:
                    $item->disciplina_desc = "Snowboard";
            }

            $time1 = strtotime($item->ora_in);
            $time2 = strtotime($item->ora_out);
            $item->datediff = round(abs($time2 - $time1) / 3600,2);
        }
        
                
        // collettivi        
        $collettivi = CollettivoAllievi::where('partecipante', $client_id)->get();
        
        $ore = collect();
        foreach($collettivi as $coll){
        	if(Ora::where('id_cliente', 'C_'.$coll->id_collettivo)->where('data', $coll->giorno)->where('id_maestro', $coll->id_maestro)->exists()){
        		$ore->push(Ora::where('id_cliente', 'C_'.$coll->id_collettivo)->where('data', $coll->giorno)->where('id_maestro', $coll->id_maestro)->first());
        	}        	
        }
        
        $itemsc = Item::select('items.id as item_id','items.importo','items.ora_id','invoices.*','ora.data as data_inv','ora.ora_in as ora_in_inv', 'ora.ora_out as ora_out_inv', 
                    'ora.id_cliente', 'ora.id_maestro', 'ora.id_cc as sede_id', 'ora.disciplina')
		            ->join("invoices","invoices.id","items.invoice_id")
		            ->join("ora","ora.id","items.ora_id")
		            ->where('invoices.aperta', 1)
		            ->where('invoices.contact_id', $client_id)
		            ->where('ora.id_cliente', 'LIKE', 'C%')
		            ->orderBy('data_inv', 'ASC')
		            ->groupBy('ora.id_cliente')
		            ->get(); 
            
        foreach ($itemsc as $item) {
            $item->cliente = null;
            $item->cliente_id = null;

            list($tipo, $id_cliente) = explode('_', $item->id_cliente);
            $item->tipo = $tipo;
            $item->cliente_id = $id_cliente;

			$item->invoice_id = $item->id;

            $collett_id = null;
            if($tipo == 'C'){
                $coll = Collettivo::find($id_cliente);
                if($coll != null){
                    $item->collettivo =$coll->nome;
                    $collett_id=$coll->id;
                }
                    
            }
            
            $dates = CollettivoAllievi::where('id_collettivo', $collett_id)->where('partecipante', $client_id);		//Collettivo::find($collett_id)->getDate();	//  $items->distinct('data_inv')->pluck('data_inv')->toArray();
						
            $item->collett_id = $collett_id;
            $item->sede_lbl = '';
            $sede = Sede::find($item->sede_id);
            if($sede != null)
                $item->sede_lbl = $sede->nome;

            $item->disciplina_desc = "";
            
            $item->date = $dates;

            switch ($item->disciplina) {
                case 1:
                    $item->disciplina_desc = "Discesa";
                    break;
                case 2:
                case 3:
                    $item->disciplina_desc = "Fondo";
                    break;
                case 4:
                    $item->disciplina_desc = "Snowboard";
            }

            $item->maestri = $coll->listMastersInTable();

        }
        
        return [$items, $itemsc];
    }

    public static function getOreAperteCliente($client_id)
    {
        //ore fatturate e chiuse
        $invoices = Invoice::where('contact_id', $client_id)->where('aperta', 0)->pluck('id')->toArray();
        $invoices_items = Item::whereIn('invoice_id', $invoices)->pluck('ora_id')->toArray();
        $invoices_items = array_filter($invoices_items, fn ($item) => !is_null($item));  // pulisce dai valori null
        
        $items = Ora::where('id_cliente', 'T_'.$client_id)->whereNotIn('id', $invoices_items)->get();
        
        foreach ($items as $item) {

            $maestro = Master::find($item->id_maestro)->contact;
            if($maestro != null)
                $item->maestro = $maestro->nome.' '.$maestro->cognome;
            
            $item->sede_lbl = '';
            $sede = Sede::find($item->id_cc);
            if($sede != null)
                $item->sede_lbl = $sede->nome;

            $item->disciplina_desc = "";

            switch ($item->disciplina) {
                case 1:
                    $item->disciplina_desc = "Discesa";
                    break;
                case 2:
                    $item->disciplina_desc = "Fondo";
                    break;
                case 3:
                    $item->disciplina_desc = "Fondo";
                    break;
                case 4:
                    $item->disciplina_desc = "Snowboard";
            }

            $time1 = strtotime($item->ora_in);
            $time2 = strtotime($item->ora_out);
            $item->datediff = round(abs($time2 - $time1) / 3600,2);
        }

        return $items;
    }


    public static function getCollettiviApertiCliente($client_id)
    {
		//ore fatturate e chiuse
        $invoices = Invoice::where('contact_id', $client_id)->where('aperta', 0)->pluck('id')->toArray();
        $invoices_items = Item::whereIn('invoice_id', $invoices)->pluck('ora_id')->toArray();
        $invoices_items = array_filter($invoices_items, fn ($item) => !is_null($item));  // pulisce dai valori null
        
        $collettivi = CollettivoAllievi::where('partecipante', $client_id)->get();
        
        $ore = collect();
        foreach($collettivi as $coll){
        	if(Ora::where('id_cliente', 'C_'.$coll->id_collettivo)->where('data', $coll->giorno)->where('id_maestro', $coll->id_maestro)->exists()){
        		$ore->push(Ora::where('id_cliente', 'C_'.$coll->id_collettivo)->where('data', $coll->giorno)->where('id_maestro', $coll->id_maestro)->first());
        	}        	
        }
       
        $ore_open = $ore->whereNotIn('id', $invoices_items);
        
        $items = Item::select('items.id as item_id','items.importo','items.ora_id','invoices.*','ora.data as data_inv','ora.ora_in as ora_in_inv', 'ora.ora_out as ora_out_inv', 
                    'ora.id_cliente', 'ora.id_maestro', 'ora.id_cc as sede_id', 'ora.disciplina')
            ->join("invoices","invoices.id","items.invoice_id")
            ->join("ora","ora.id","items.ora_id")
            ->where('invoices.aperta', 1)
            ->where('invoices.contact_id', $client_id)
            ->where('ora.id_cliente', 'LIKE', 'C%')
            ->orderBy('data_inv', 'ASC')
            ->get();
                
        $voci_aperte = $items->unique('ora_id')->pluck('ora_id')->toArray();  
        
        $items_to_create = $ore_open->whereNotIn('id', $voci_aperte);
    	
    	if(count($items_to_create) > 0){
    		
    		if(count($items) > 0){
    			$invoice_id = $items->first()->id;  
        	} else {
	        	$old_invoice = new Invoice();
	            $old_invoice->numero = null;
	            $old_invoice->numero_registrazione = null;
	            $old_invoice->company_id = null;
	            $old_invoice->contact_id = $client_id;
	            
	            $old_invoice->data = date("Y-m-d");
	            $old_invoice->data_registrazione = date("Y-m-d");

	            $old_invoice->branch_id = $ore->first() ? $ore->first()->id_cc : auth()->user()->contact->branchContact()->branch_id;
	            $old_invoice->pagamento = 'RIDI';
	            $old_invoice->aperta = 1;
	            
	            $old_invoice->save();
	            
	            $invoice_id = $old_invoice->id; 
	        }
    		
	        foreach($items_to_create as $ora){
		                        
	    		$elenco_maestri = "";
				$contact_id = Master::find($ora->id_maestro)->contact_id;
				$mm = Contact::where('id', $contact_id)->get();
				foreach ($mm as $value) {
					$elenco_maestri = $elenco_maestri != '' ? $elenco_maestri.','.$value->nome.' '.$value->cognome : $value->nome.' '.$value->cognome;
				}

				$elenco_spec = "";
				$specs = Specialization::whereIn('id', [$ora->specialita])->get();						
				foreach ($specs as $value) {
					$elenco_spec = $elenco_spec != '' ? $elenco_spec.','.$value->nome : $value->nome;
				}
				
				$nome_disciplina = 'Discesa';
				if($ora->disciplina == 2 || $ora->disciplina == 3)
					$nome_disciplina = 'Fondo';
				if($ora->disciplina == 4)
					$nome_disciplina = 'Snowboard';
				
				$descrizione = '<b>Corso collettivo:</b> ' . Collettivo::find(substr($ora->id_cliente, 2))->nome .'
								<br /><b>Data:</b> ' . \Carbon\Carbon::createFromFormat('Y-m-d', $ora->data)->format('d/m/Y') . '
								<br /><b>Ora:</b> dalle ' . $ora->ora_in . ' alle ' . $ora->ora_out . '
								<br /><b>Maestro:</b> ' . $elenco_maestri . '
								<br /><b>Disciplina:</b> ' . $nome_disciplina . '
								<br /><b>Specialit&agrave;:</b> ' . $elenco_spec;
				
				$acconti = CollettivoAcconti::where('id_collettivo', substr($ora->id_cliente, 2))->where('id_cliente', $client_id)->first();
				
				if($acconti){
					$importo = $acconti->importo - $acconti->acconto1 - $acconti->acconto2;
				} else {
					$importo = Product::where('nome', 'like', '%collettivo%')->first()->prezzo;
				}
	    		
	    		$item_new = new Item();
				$item_new->product_id = Product::where('nome', 'like', '%collettivo%')->first()->id;
				$item_new->importo = $importo;
			    $item_new->perc_iva = Product::where('nome', 'like', '%collettivo%')->first()->perc_iva;
			    $item_new->iva = $importo * (Product::where('nome', 'like', '%collettivo%')->first()->perc_iva / 100);
				$item_new->exemption_id = Product::where('nome', 'like', '%collettivo%')->first()->exemption_id;	
	    		$item_new->descrizione = $descrizione;
	    		$item_new->qta = 1;
			    $item_new->invoice_id = $invoice_id;
			    $item_new->ora_id = $ora->id;
			    $item_new->save();
			    
			    \DB::table('invoice_ora')->insert(['invoice_id' => $invoice_id, 'ora_id' => $ora->id]);
	    	}  
        }
            
        $items = Item::select('items.id as item_id','items.importo','items.ora_id','invoices.*','ora.data as data_inv','ora.ora_in as ora_in_inv', 'ora.ora_out as ora_out_inv', 
                    'ora.id_cliente', 'ora.id_maestro', 'ora.id_cc as sede_id', 'ora.disciplina')
            ->join("invoices","invoices.id","items.invoice_id")
            ->join("ora","ora.id","items.ora_id")
            ->where('invoices.aperta', 1)
            ->where('invoices.contact_id', $client_id)
            ->where('ora.id_cliente', 'LIKE', 'C%')
			->groupBy('ora.id_cliente')		//invoices.contact_id
            ->orderBy('data_inv', 'ASC')
            ->get();   
            
            foreach ($items as $item) {
                $item->cliente = null;
                $item->cliente_id = null;

                list($tipo, $id_cliente) = explode('_', $item->id_cliente);
                $item->tipo = $tipo;
                $item->cliente_id = $id_cliente;

                $collett_id = null;
                if($tipo == 'C'){
                    $coll = Collettivo::find($id_cliente);
                    if($coll != null){
                        $item->collettivo =$coll->nome;
                        $collett_id=$coll->id;
                    }
                        
                }
                
                $dates = CollettivoAllievi::where('id_collettivo', $collett_id)->where('partecipante', $client_id);		//Collettivo::find($collett_id)->getDate();	//  $items->distinct('data_inv')->pluck('data_inv')->toArray();
				
				$acconti = CollettivoAcconti::where('id_collettivo', $collett_id)->where('id_cliente', $client_id)->first();
				
				if($acconti){
					$item->importo = $acconti->importo;
					$item->acc_1 = $acconti->acconto1;
					$item->acc_2 = $acconti->acconto2;
					$item->saldo = $acconti->importo - $acconti->acconto1 - $acconti->acconto2;
				}				
                $item->collett_id = $collett_id;
                $item->sede_lbl = '';
                $sede = Sede::find($item->sede_id);
                if($sede != null)
                    $item->sede_lbl = $sede->nome;

                $item->disciplina_desc = "";
                
                $item->date = $dates;

                switch ($item->disciplina) {
                    case 1:
                        $item->disciplina_desc = "Discesa";
                        break;
                    case 2:
                    case 3:
                        $item->disciplina_desc = "Fondo";
                        break;
                    case 4:
                        $item->disciplina_desc = "Snowboard";
                }

                $item->maestri = $coll->listMastersInTable();

				// Add hours
				$coll_all = CollettivoAllievi::where('id_collettivo', substr($item->id_cliente, 2))->where('partecipante', $client_id)->get();
				$lista_ore = array();
				foreach($coll_all as $ca){
					if(Ora::where('id_cliente', $item->id_cliente)->where('id_maestro', $ca->id_maestro)->where('data', $ca->giorno)->exists()){
						$lista_ore[] = Ora::where('id_cliente', $item->id_cliente)->where('id_maestro', $ca->id_maestro)->where('data', $ca->giorno)->first()->id;
					}					
				}
				$item->hours = implode('-', $lista_ore);
				
				// oppure semplicemente $item->hours = implode('-', Item::where(['invoice_id' => $item->id])->pluck('ora_id')->toArray()); ??
            }

          return $items;

    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Contacts\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact)
    {
        $items = $this->getOreAperteCliente($contact->id);
        $products = [''=>'']+Product::orderby('nome', 'ASC')->pluck('nome', 'id')->toArray();
        $coll = $this->getCollettiviApertiCliente($contact->id);
        return view('areaseb::core.contacts.show', compact('contact','items','products','coll'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Contacts\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function edit(Contact $contact)
    {
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $companies[''] = '';
        if($contact->contact_type_id == 3){
        	$companies += Company::where('client_id', 4)->pluck('rag_soc', 'id')->toArray();
        } elseif($contact->contact_type_id == 4){
        	$companies += Company::pluck('rag_soc', 'id')->toArray();
        } else {
        	$companies += Company::where('client_id', '!=', 4)->pluck('rag_soc', 'id')->toArray();
        }        
        $users[''] = '';
        $users += User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        $lists = NewsletterList::pluck('nome', 'id')->toArray();

        $pos = ['' => '']+Contact::uniquePos();
        $origins = ['' => '']+Contact::uniqueOrigin();
        $testimonials = $this->testimonialsArray();
        $agents = $this->agentsArray();
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        $contact_type = [''=>'']+TypeUser::where('id','!=', 3)->pluck('descrizione', 'id')->toArray();

        $disabile_sedute = [''=>'']+DisabileSedute::pluck('nome', 'id')->toArray();
        $disabile_tipo = [''=>'']+DisabileTipo::pluck('nome', 'id')->toArray();
        $disabile_attrezzi = [''=>'']+DisabileAttrezzi::pluck('nome', 'id')->toArray();
        $specializzazioni = [''=>'']+Specialization::pluck('nome', 'id')->toArray();
        
        
        return view('areaseb::core.contacts.edit', compact('provinces','specializzazioni','contact_type', 'disabile_sedute', 'disabile_tipo','disabile_attrezzi','branches', 'countries', 'companies', 'users', 'contact', 'lists', 'pos', 'origins', 'testimonials', 'agents'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Contacts\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contact $contact)
    {
        if(!is_null($request->list_id))
        {
            if(count($request->list_id) > 0)
            {
                $contact->lists()->sync($request->list_id);
            }
        }

        $this->validate(request(), [
            'nome' => 'required',
            'cognome' => 'required',
            'email' => "required|email"
        ]);

        //dd(request()->input());
        Contact::createOrUpdate($contact, request()->input());

        if(isset($request->testimonial_id))
        {
            \Areaseb\Referrals\Models\Testimonial::find($request->testimonial_id)->contacts()->syncWithoutDetaching($contact->id);
        }
        elseif(\Illuminate\Support\Facades\Schema::hasTable('testimonial_contact'))
        {
            $contact->testimonial()->detach();
        }

        if(isset($request->agent_id))
        {
            \Areaseb\Agents\Models\Agent::find($request->agent_id)->contacts()->syncWithoutDetaching($contact->id);
        }
        elseif(\Illuminate\Support\Facades\Schema::hasTable('agent_contact'))
        {
            $contact->agent()->detach();
        }

        if(isset($request->prev))
        {
            return redirect($request->prev)->with('message', 'Contatto Aggiornato');
        }

        return redirect(route('contacts.index'))->with('message', 'Contatto Aggiornato');
    }


    public function updateNote(Request $request)
    {
        $contact = Contact::find($request->contact_id);
        if(isset($request->note)){
        	$contact->note = $request->note;
        }
        if(isset($request->note_segreteria)){
        	$contact->note_segreteria = $request->note_segreteria;
        }        
        $contact->update();  
        return redirect()->back();
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Contacts\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        if(!is_null($contact->user_id) && ($contact->user_id != auth()->user()->id))
        {
            $user = User::findOrFail($contact->user_id);

            foreach($user->events as $event)
            {
                $event->delete();
            }

            foreach($user->calendars as $calendar)
            {
                $calendar->delete();
            }

            foreach($user->roles as $role)
            {

                if($role->name == 'testimonial')
                {
                    $testimonial = \Areaseb\Referrals\Models\Testimonial::where('user_id', $user->id)->first();
                    if($testimonial)
                    {
                        $testimonial->companies()->detach();
                        $testimonial->contacts()->detach();
                        $testimonial->delete();
                    }
                }

                if($role->name == 'agent')
                {
                    $agent = \Areaseb\Agents\Models\Agent::where('user_id', $user->id)->first();
                    if($agent)
                    {
                        $agent->companies()->detach();
                        $agent->contacts()->detach();
                        $agent->delete();
                    }
                }

                $user->removeRole($role->name);
            }

            foreach($user->permissions as $permission)
            {
                $user->revokePermissionTo($permission->name);
            }

            $user->delete();

            Contact::cleanDelete($contact);

            return 'done';
        }
        elseif($contact->user_id == auth()->user()->id)
        {
            return "Questo contatto è collegato all'utente loggato in questa sessione, non si può eliminare";
        }
        elseif(!is_null($contact->user_id))
        {
            return "Questo contatto è collegato ad un'utente.";
        }

        Contact::cleanDelete($contact);
        return 'done';
    }

//contacts-validate-file
    public function validateFile(Request $request)
    {
        $this->validate(request(), [
            'file' => 'mimes:csv'
        ]);
    }

//contacts/-comapny
    public function Company(Request $request)
    {
        $prospect = Client::Prospect();
        $contact = Contact::find($request->id);

        $company = new Company;
            $company->rag_soc = $contact->fullname;
            $company->address = $contact->indirizzo;
            $company->zip = $contact->cap;
            $company->city = $contact->citta;
            $company->province = $contact->provincia;
            $company->city_id = $contact->city_id;
            $company->nation = $contact->nazione;
            $company->lang = $contact->lingua;
            $company->email = $contact->email;
            $company->client_id = $prospect->id;
        $company->save();

        $contact->company_id = $company->id;
        $contact->save();

        return redirect( route('contacts.edit', $contact->id ) )->with('message', 'Azienda da contatto creata! Assicurati di compilare i campi mancanti');
    }


//contacts/make-comapny
    public function makeCompany(Request $request)
    {
        $prospect = Client::Prospect();
        $contact = Contact::find($request->id);
        $company = new Company;
            $company->rag_soc = $contact->fullname;
            $company->address = $contact->indirizzo;
            $company->zip = $contact->cap;
            $company->city = $contact->citta;
            $company->province = $contact->provincia;
            $company->city_id = $contact->city_id;
            $company->nation = $contact->nazione;
            $company->lang = $contact->lingua;
            $company->email = $contact->email;
            $company->client_id = $prospect->id;
        $company->save();

        $contact->company_id = $company->id;
        $contact->save();

        return redirect('companies/'.$company->id.'/edit')->with('message', 'Azienda da contatto creata! Assicurati di compilare i campi mancanti');
    }

//contacts/make-user
    public function makeUser(Request $request)
    {
        $contact = Contact::find($request->id);
        if(is_null($contact->email))
        {
            return redirect(route('contacts.index'))->with('error', "Questo contatto non ha un'email. Impossibile creare l'utente");
        }

        $rs = str_random(8);

        $user = User::create([
            'email' => $contact->email,
            'password' => bcrypt($rs)
        ]);

        $contact->user_id = $user->id;
        $contact->save();

        return redirect(route('contacts.index'))->with('message', "Utente creato ". $rs .". Potrà chiedere una nuova password usando l'email: ".$contact->email);
    }

	//api/contacts/{contact}/discount-exemption - GET
    public function discountExemption(Contact $contact)
    {
        return $contact->company;
    }

    //api/ta/contacts
    public function taindex()
    {
        $contacts = [];$count = 0;
		
        foreach(Contact::query()->where('attivo', 1)->get() as $contact)
        {
            $contacts[$count]['id'] = $contact->id;
            $contacts[$count]['name'] = $contact->nome . ' ' . $contact->cognome;
            $count++;
        }

        return $contacts;
    }

    //api/ta/contacts_master
    public function listMasters()
    {
        $contacts = [];$count = 0;

        foreach(Contact::select('nome', 'cognome', 'id')->where('contact_type_id', 3)->get() as $contact)
        {
            $contacts[$count]['id'] = $contact->id;
            $contacts[$count]['name'] = $contact->nome . ' ' . $contact->cognome;
            $count++;
        }

        return $contacts;
    }

    private function testimonialsArray()
    {
        if(\Illuminate\Support\Facades\Schema::hasTable('testimonial_contact'))
        {
            return \Areaseb\Referrals\Models\Testimonial::testimonialsArray();
        }
        return [''=>''];
    }

    private function agentsArray()
    {
        if(\Illuminate\Support\Facades\Schema::hasTable('agent_contact'))
        {
            return \Areaseb\Agents\Models\Agent::agentsArray();
        }
        return [''=>''];
    }

    

}
