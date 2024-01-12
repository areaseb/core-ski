<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{City, Note, Ora, Client, Company, Specialization, Collettivo, CollettivoAcconti, CollettivoAllievi, Contact, Country, Exemption, Sector, Sede, TypeUser, Item, Invoice, Master, Product};
use App\User;
use \Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;

class CompanyController extends Controller
{
    public function index()
    {
        if(request()->input())
        {
            $companies = Company::filter(request())->where('client_id','!=', 4)->orderBy('rag_soc', 'ASC')->paginate(30);
        }
        else
        {
            if(auth()->user()->hasRole('testimonial'))
            {
                $query = auth()->user()->testimonial->companies();
            }
            elseif(auth()->user()->hasRole('agent'))
            {
                if(auth()->user()->hasRole('super'))
                {
                    $query = Company::query();
                }
                else
                {
                    $query = auth()->user()->agent->companies();
                }
            }
            else
            {
                $query = Company::query();
            }
            $companies = $query->where('client_id','!=', 4)->with('client', 'sector')->orderBy('rag_soc', 'ASC')->paginate(30);
        }


        foreach ($companies as $key => $value) {
            if($value->contacts()->first() != null && $value->contacts()->first()->contact_type_id == 3)
                $companies->forget($key);
        }

        return view('areaseb::core.companies.index', compact('companies'));
    }

    public function create()
    {
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $clients = Client::company()->pluck('nome', 'id')->toArray();
        $referenti = [''=>''];
        if(Client::Referente())
        {
            $referenti += Client::Referente()->companies()->pluck('rag_soc', 'id')->toArray();
        }
        $sectors = [''=>'']+Sector::pluck('nome', 'id')->toArray();
        $exemptions = ['' => '']+Exemption::esenzioni();
        $origins = ['' => '']+Company::uniqueOrigin();

        $testimonials = $this->testimonialsArray();
        $agents = $this->agentsArray();

        $contact_type = [''=>'']+TypeUser::whereIn('id', [1,2])->pluck('descrizione', 'id')->toArray();
        $branches = Sede::pluck('nome', 'id')->toArray();
        $byplanning = false;

        return view('areaseb::core.companies.create', compact('byplanning','branches','contact_type','provinces', 'countries', 'clients', 'referenti', 'sectors', 'exemptions', 'origins','testimonials', 'agents'));
    }

    public function createByPlanning($date)
    {	
    	$date = date('d-m-Y', strtotime($date));
    	
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $clients = Client::company()->pluck('nome', 'id')->toArray();
        $referenti = [''=>''];
        if(Client::Referente())
        {
            $referenti += Client::Referente()->companies()->pluck('rag_soc', 'id')->toArray();
        }
        $sectors = [''=>'']+Sector::pluck('nome', 'id')->toArray();
        $exemptions = ['' => '']+Exemption::esenzioni();
        $origins = ['' => '']+Company::uniqueOrigin();

        $testimonials = $this->testimonialsArray();
        $agents = $this->agentsArray();

        $contact_type = [''=>'']+TypeUser::whereIn('id', [1,2])->pluck('descrizione', 'id')->toArray();
        $branches = Sede::pluck('nome', 'id')->toArray();
        $byplanning = true;
        return view('areaseb::core.companies.create', compact('date','byplanning','branches','contact_type','provinces', 'countries', 'clients', 'referenti', 'sectors', 'exemptions', 'origins','testimonials', 'agents'));
    }


    public function store(Request $request)
    {
		if($request->branch_id == null){
			return back()->with('error', 'Selezionare almeno una sede di appartenenza');
		}
		
        if(isset($request->createContact))
        {
            $this->validate(request(),[
                'nome' => "required",
                'cognome' => "required",
                'rag_soc' => "required",	//|unique:companies,rag_soc
                'piva' => "required_if:privato,0",
                'pec' => "nullable|unique:companies,pec",
                's1' => "nullable|numeric|between:0.00,99.99"
            ]);
        }
        else
        {
            $this->validate(request(),[
                'rag_soc' => "required",	//|unique:companies,rag_soc
                'piva' => "required_if:privato,0",
                'pec' => "nullable|unique:companies,pec",
                's1' => "nullable|numeric|between:0.00,99.99"
            ]);
        }

        $lang = 'it';
        if($request->nation != 'IT')
        {
            $lang = 'en';
        }

        if(isset($request->lang))
        {
            $lang = $request->lang;
        }

        $sector_id = null;
        if(!is_null($request->sector_id))
        {
            if(is_numeric($request->sector_id))
            {
                $sector_id = $request->sector_id;
            }
            else
            {
                $sector_id = Sector::create(['nome' => $request->sector_id])->id;
            }
        }


        //controllo e genero il codice fiscale
        $cf = $request->cf;
        $sesso = $request->sesso;
       
        if($cf == "" && $sesso != ""){
        	
        	if(isset($request->data_nascita)){
		        list($a, $m, $g) = explode("-", $request->data_nascita);
				$data_nascita = "$g/$m/$a";
			} else {
				$data_nascita = '00/00/0000';
			}

	        //$data_nascita = $request->data_nascita;
	        $nome_url = urlencode($request->nome);
	        $cognome_url = urlencode($request->cognome);
	        $luogo_nascita_url = urlencode($request->luogo_nascita);
        
            $stuff = file("http://webservices.dotnethell.it/codicefiscale.asmx/CalcolaCodiceFiscale?Nome=$nome_url&Cognome=$cognome_url&ComuneNascita=$luogo_nascita_url&DataNascita=$data_nascita&Sesso=$sesso");
            //dd($cf,$sesso,$data_nascita,$nome_url,$cognome_url,$luogo_nascita_url, $stuff);
                      
            $l = 1;
            foreach ($stuff as $line) {
                $cf = $line;
                if($l == 2){
                    $cf = str_replace("<string xmlns=\"http://webservices.dotnethell.it/CodiceFiscale\">", "", $cf);
                    $cf = str_replace("</string>", "", $cf);
                }
                $l++;
            }
            
        }
        
        if(Company::where('cf', $cf)->exists() && $cf != 'Error'){
			//return back()->with('error', 'CF già presente');
			$cf = 'Error';
		}


            $company = new Company;
            $company->rag_soc = str_replace('  ', ' ', $request->rag_soc);
            $company->nation = $request->nation;
            $company->lang = $lang;
            $company->address = $request->address;
            $company->city = $request->city;
            $company->zip = $request->zip;
            $company->province = $request->province;
            $company->email = $request->email;
            $company->email_ordini = $request->email_ordini;
            $company->email_fatture = $request->email_fatture;
            $company->settore = $request->settore;
            $company->website = $request->website;
            $company->phone = (substr($request->phone, 0, 1) != '+' && $request->phone != '') ? '+'.Country::getCountryPhone($request->nation).$request->phone : $request->phone;
            $company->mobile = (substr($request->mobile, 0, 1) != '+' && $request->mobile != '') ? '+'.Country::getCountryPhone($request->nation).$request->mobile : $request->mobile;
            $company->private = $request->private;
            $company->sdi = $request->sdi;
            $company->pec = $request->pec;
            $company->piva = $request->piva;
            $company->cf = $cf;
            $company->supplier = $request->supplier;
            $company->partner = $request->partner;
            $company->active = $request->active;
            $company->parent_id = $request->parent_id;
            $company->sector_id = $sector_id;
            $company->exemption_id = $request->exemption_id;
            $company->pagamento = $request->pagamento;
            $company->city_id = City::getCityIdFromData($request->province, $request->nation, $request->city);
            $company->s1 = is_null($request->s1) ? 0.00 : $request->s1;
            $company->s2 = is_null($request->s2) ? 0.00 : $request->s2;
            $company->s3 = is_null($request->s3) ? 0.00 : $request->s3;
            $company->client_id = $request->client_id;
            $company->origin = $request->origin;
            $company->lat = $request->lat;
            $company->lng = $request->lng;



            $company->luogo_nascita = $request->luogo_nascita;
            $company->data_nascita = $request->data_nascita;
            $company->nickname = $request->nickname;
            $company->sesso = $request->sesso;


            $company->save();

            foreach ($request->branch_id as $value) {
                \DB::table('company_branch')->insert(
                    ['company_id' => $company->id, 'branch_id' => $value]
                );
            }
            
        if(isset($request->private) && $request->private == 1)
        {
            $contact = new Contact;
            $contact->nome = $request->nome;
            $contact->cognome = $request->cognome;
            $contact->nazione = $request->nation;
            $contact->lingua = $lang;
            $contact->indirizzo = $request->address;
            $contact->citta = $request->city;
            $contact->cap = $request->zip;
            $contact->provincia = $request->province;
            $contact->email = $request->email;
            $contact->cellulare = (substr($request->mobile, 0, 1) != '+' && $request->mobile != '') ? '+'.Country::getCountryPhone($request->nation).$request->mobile : $request->mobile;
            $contact->city_id = $company->city_id;
            $contact->origin = $request->origin;
            $contact->company_id = $company->id;
            $contact->attivo = true;
            $contact->contact_type_id = $request->contact_type_id;

            $contact->luogo_nascita = $request->luogo_nascita;
            $contact->data_nascita = $request->data_nascita;
            $contact->nickname = $request->nickname;
            $contact->sesso = $request->sesso;
            $contact->cod_fiscale = $cf;


            if(is_null($request->email))
            {
                $contact->subscribed = 0;
            }
            $contact->save();

            
            foreach ($request->branch_id as $value) {
                \DB::table('contact_branch')->insert(
                    ['contact_id' => $contact->id, 'branch_id' => $value]
                );
            }
            
        }

        if(isset($request->testimonial_id))
        {
            \Areaseb\Referrals\Models\Testimonial::find($request->testimonial_id)->companies()->attach($company->id);
        }

        if(isset($request->agent_id))
        {
            \Areaseb\Agents\Models\Agent::find($request->agent_id)->companies()->attach($company->id);
        }

        if($request->byplanning){
            return redirect('/planning?day='.$request['date']);
        }
        else{
        	
        	$products = [''=>'']+Product::orderby('nome', 'ASC')->pluck('nome', 'id')->toArray();
        	
            if($request->previous)
            {
                return redirect()->route('companies.show', [$company])->with('message', 'Azienda Creata');
                //return view('areaseb::core.companies.show', compact('company', 'products'))->with('message', 'Azienda Creata');
                //return redirect($request->previous)->with('message', 'Azienda Creata');
            }
    		
    		return redirect()->route('companies.show', [$company])->with('message', 'Azienda Creata');
            //return view('areaseb::core.companies.show', compact('company', 'products'))->with('message', 'Azienda Creata');
        }
        
        //return redirect(route('companies.index'))->with('message', 'Azienda Creata');
    }
	
	public static function getStoricoOreCliente($client_id)
    {
        // Ore
        $invoices = Invoice::where('company_id', $client_id)->where('aperta', 0)->pluck('id')->toArray();
//        $invoices_items = Item::whereIn('invoice_id', $invoices)->pluck('ora_id')->toArray();
//        $invoices_items = array_filter($invoices_items, fn ($item) => !is_null($item));  // pulisce dai valori null        
        $invoices_items = \DB::table('invoice_ora')->whereIn('invoice_id', $invoices)->pluck('ora_id')->toArray();
        
        $items = Ora::where('id_cliente', 'Y_'.$client_id)->whereIn('id', $invoices_items)->get();
        
        foreach ($items as $item) {

            $maestro = Master::find($item->id_maestro)->contact;
            if($maestro != null)
                $item->maestro = $maestro->nome.' '.$maestro->cognome;
            
            if(Item::where('ora_id', $item->id)->whereIn('invoice_id', $invoices)->exists())
            	$item->invoice_id = Item::where('ora_id', $item->id)->whereIn('invoice_id', $invoices)->first()->invoice_id;
            else
            	$item->invoice_id = null;
            
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
        $collettivi = CollettivoAllievi::where('id_cliente', $client_id)->get();
        
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
		            ->where('invoices.company_id', $client_id)
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
            
            $dates = CollettivoAllievi::where('id_collettivo', $collett_id)->where('id_cliente', $client_id);		//Collettivo::find($collett_id)->getDate();	//  $items->distinct('data_inv')->pluck('data_inv')->toArray();
						
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
	
	public function getOreAperteCliente($client_id)
    {
        //ore fatturate e chiuse
        $invoices = Invoice::where('company_id', $client_id)->where('aperta', 0)->pluck('id')->toArray();
        //$invoices_items = Item::whereIn('invoice_id', $invoices)->pluck('ora_id')->toArray();
        $invoices_items = \DB::table('invoice_ora')->whereIn('invoice_id', $invoices)->pluck('ora_id')->toArray();
        $invoices_items = array_filter($invoices_items, fn ($item) => !is_null($item));  // pulisce dai valori null
        
        $items = Ora::where('id_cliente', 'Y_'.$client_id)->whereNotIn('id', $invoices_items)->get();
        
        foreach ($items as $item) {

            $maestro = Master::find($item->id_maestro);
            if($maestro != null)
                $item->maestro = $maestro->contact->nome.' '.$maestro->contact->cognome;
            else
            	$item->maestro = 'NON TROVATO (id '.$item->id_maestro.')';
            
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
        
        
        
        /*$items = Item::select('items.id as item_id','items.importo','items.ora_id','invoices.*','ora.data as data_inv','ora.ora_in as ora_in_inv', 'ora.ora_out as ora_out_inv', 
                                'ora.id_cliente', 'ora.id_maestro', 'ora.id_cc as sede_id', 'ora.ritrovo', 'ora.pax', 'ora.note as note_lez', 'ora.disciplina')
                    ->join("invoices","invoices.id","items.invoice_id")
                    ->join("ora","ora.id","items.ora_id")
                    ->where('invoices.aperta', 1)
                    ->where('ora.id_cliente', 'Y_'.$client_id)
                    ->orderBy('data_inv', 'ASC')
                    ->get();

        foreach ($items as $item) {

            $maestro = Master::find($item->id_maestro)->contact;
            if($maestro != null)
                $item->maestro =$maestro->nome.' '.$maestro->cognome;
            
            $item->sede_lbl = '';
            $sede = Sede::find($item->sede_id);
            if($sede != null)
                $item->sede_lbl = $sede->nome;

            $item->disciplina_desc = "";

            switch ($item->disciplina) {
                case 1:
                    $item->disciplina_desc = "Discesa";
                    break;
                case 2:
                    $item->disciplina_desc = "Fondo - Classico";
                    break;
                case 3:
                    $item->disciplina_desc = "Fondo - Skating";
                    break;
                case 4:
                    $item->disciplina_desc = "Snowboard";
            }

            $time1 = strtotime($item->ora_in_inv);
            $time2 = strtotime($item->ora_out_inv);
            $item->datediff=  round(abs($time2 - $time1) / 3600,2);
        }*/

        return $items;
    }


    public function getCollettiviApertiCliente($client_id)
    {
		//ore fatturate e chiuse
        $invoices = Invoice::where('company_id', $client_id)->where('aperta', 0)->pluck('id')->toArray();
        $invoices_items = Item::whereIn('invoice_id', $invoices)->pluck('ora_id')->toArray();
        $invoices_items = array_filter($invoices_items, fn ($item) => !is_null($item));  // pulisce dai valori null
        
        $collettivi = CollettivoAllievi::where('id_cliente', $client_id)->get();
        
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
            ->where('invoices.company_id', $client_id)
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
	            $old_invoice->company_id = $client_id;
	            $old_invoice->contact_id = null;
	            
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
				
				$importo = Product::where('nome', 'like', '%collettivo%')->first()->prezzo;
	    		
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
            ->where('invoices.company_id', $client_id)
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
				
				$dates = CollettivoAllievi::where('id_collettivo', $collett_id)->where('id_cliente', $client_id);	//Collettivo::find($collett_id)->getDate();	//  $items->distinct('data_inv')->pluck('data_inv')->toArray();
				
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
				$item->hours = implode('-', Item::where(['invoice_id' => $item->id])->pluck('ora_id')->toArray());
            }

          return $items;

    }
    
    public function show(Company $company)
    {
        $ore = $this->getOreAperteCliente($company->id);
        //$coll = $this->getCollettiviApertiCliente($company->id);
        $products = [''=>'']+Product::orderby('nome', 'ASC')->pluck('nome', 'id')->toArray();
        
        return view('areaseb::core.companies.show', compact('company','ore','products'));
    }


    public function edit(Company $company)
    {
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $clients = Client::company()->pluck('nome', 'id')->toArray();
        $referenti = [''=>''];
        if(Client::Referente())
        {
            $referenti += Client::Referente()->companies()->pluck('rag_soc', 'id')->toArray();
        }
        $sectors = [''=>'']+Sector::pluck('nome', 'id')->toArray();
        $exemptions = ['' => '']+Exemption::esenzioni();

        $testimonials = $this->testimonialsArray();
        $agents = $this->agentsArray();
        $origins = ['' => '']+Company::uniqueOrigin();
        $contact_type = [''=>'']+TypeUser::pluck('descrizione', 'id')->toArray();
        $branches = Sede::pluck('nome', 'id')->toArray();
        $byplanning = false;
        return view('areaseb::core.companies.edit', compact('byplanning','branches','contact_type','provinces', 'countries', 'company', 'clients', 'referenti', 'sectors', 'exemptions', 'origins', 'testimonials', 'agents'));
    }


    public function update(Request $request, Company $company)
    {
    	if($request->branch_id == null){
			return back()->with('error', 'Selezionare almeno una sede di appartenenza');
		}
		
        $this->validate(request(),[
            'rag_soc' => "required",	//|unique:companies,rag_soc,".$company->id.",id
            'piva' => "required_if:privato,0",
            'pec' => "nullable|unique:companies,pec,".$company->id.",id",
            's1' => "nullable|min:1|max:99"
        ]);

        //dd($request, $company);

        $lang = 'it';
        if($request->nation != 'IT')
        {
            $lang = 'en';
        }

        if(isset($request->lang))
        {
            $lang = $request->lang;
        }

        $sector_id = null;
        if(!is_null($request->sector_id))
        {
            if(is_numeric($request->sector_id))
            {
                $sector_id = $request->sector_id;
            }
            else
            {
                $sector_id = Sector::create(['nome' => $request->sector_id])->id;
            }
        }
		
		$sesso = $request->sesso;
		$cf = $request->cf;
		
		if($cf == "" && $sesso != ""){
        	
        	if($request->data_nascita){
        		list($a, $m, $g) = explode("-", $request->data_nascita);
				$data_nascita = "$g/$m/$a";
        	} else {
        		$data_nascita = "00/00/0000";
        	}
	        

	        //$data_nascita = $request->data_nascita;
	        list($nome_url, $cognome_url) = explode(' ', $request->rag_soc);
	        $nome_url = urlencode($nome_url);
	        $cognome_url = urlencode($cognome_url);
	        $luogo_nascita_url = urlencode($request->luogo_nascita);
        
            $stuff = file("http://webservices.dotnethell.it/codicefiscale.asmx/CalcolaCodiceFiscale?Nome=$nome_url&Cognome=$cognome_url&ComuneNascita=$luogo_nascita_url&DataNascita=$data_nascita&Sesso=$sesso");
            //dd($cf,$sesso,$data_nascita,$nome_url,$cognome_url,$luogo_nascita_url, $stuff);
                      
            $l = 1;
            foreach ($stuff as $line) {
                $cf = $line;
                if($l == 2){
                    $cf = str_replace("<string xmlns=\"http://webservices.dotnethell.it/CodiceFiscale\">", "", $cf);
                    $cf = str_replace("</string>", "", $cf);
                }
                $l++;
            }
            
        }
        
            $company->private = $request->private;
            $company->rag_soc = str_replace('  ', ' ', $request->rag_soc);
            $company->nation = $request->nation;
            $company->lang = $lang;
            $company->address = $request->address;
            $company->city = $request->city;
            $company->zip = $request->zip;
            $company->province = $request->province;
            $company->luogo_nascita = $request->luogo_nascita;
            $company->data_nascita = $request->data_nascita;
            $company->nickname = $request->nickname;
            $company->sesso = $request->sesso;
            $company->email = $request->email;
            $company->email_ordini = $request->email_ordini;
            $company->email_fatture = $request->email_fatture;
            $company->settore = $request->settore;
            $company->active = $request->active;
            $company->phone = (substr($request->phone, 0, 1) != '+' && $request->phone != '') ? '+'.Country::getCountryPhone($request->nation).$request->phone : $request->phone;
            $company->mobile = (substr($request->mobile, 0, 1) != '+' && $request->mobile != '') ? '+'.Country::getCountryPhone($request->nation).$request->mobile : $request->mobile;
            $company->sdi = $request->sdi;
            $company->pec = $request->pec;
            if(isset($request->piva))
                $company->piva = $request->piva;
            $company->cf = $cf;
            $company->exemption_id = $request->exemption_id;
            $company->pagamento = $request->pagamento;
            $company->s1 = $request->s1;
            $company->s2 = $request->s2;
            $company->s3 = $request->s3;
            $company->client_id = $request->client_id;
            $company->supplier = $request->supplier;
            $company->partner = $request->partner;
            $company->parent_id = $request->parent_id;
            $company->origin = $request->origin;
            $company->sector_id = $sector_id;
            $company->website = $request->website;
      
            $company->city_id = City::getCityIdFromData($request->province, $request->nation, $request->city);
            $company->lat = $request->lat;
            $company->lng = $request->lng;

            
            $company->save();

        \DB::table('company_branch')->where('company_id', $company->id)->delete();
        foreach ($request->branch_id as $value) {
            
            if(\DB::table('company_branch')->where('company_id', $company->id)->where('branch_id', $value)->first() == null){
                \DB::table('company_branch')->insert(
                    ['company_id' => $company->id, 'branch_id' => $value]
                );
            }
            
        }


        if(isset($request->testimonial_id))
        {
            \Areaseb\Referrals\Models\Testimonial::find($request->testimonial_id)->companies()->syncWithoutDetaching($company->id);
        }
        elseif(\Illuminate\Support\Facades\Schema::hasTable('testimonial_company'))
        {
            $company->testimonial()->detach();
        }

        if(isset($request->agent_id))
        {
            $company->agent()->sync($request->agent_id);
        }
        elseif(\Illuminate\Support\Facades\Schema::hasTable('agent_company'))
        {
            $company->agent()->detach();
        }

        if($request->previous)
        {
            return redirect($request->previous)->with('message', 'Azienda Aggiornata');
        }

        return redirect(route('companies.index'))->with('message', 'Azienda Aggiornata');
    }


    public function destroy(Company $company)
    {
        try
        {
            foreach($company->notes as $note)
            {
                $note->delete();
            }

            foreach($company->contacts as $contact)
            {
                $contact->delete();
            }
            $company->delete();
        }
        catch(\Exception $e)
        {
           	return redirect('/companies')->with('error', 'Questo elemento è usato da un\'altro modulo');
            //return "Questo elemento è usato da un'altro modulo";
        }
        return 'done';
    }


    //api/companies/create-contacts - POST
        public function createContactsFromCompanies(Request $request)
        {
            $companies = Company::filter($request)->whereNotNull('email')->get();
            $count = 0;
            foreach($companies as $company)
            {
                $email = $company->email;
                if(strpos($company->email, ',') !== false)
                {
                    $arr = explode($company->email, ',');
                    $email = trim($arr[0]);
                }
                if(strpos($company->email, ';') !== false)
                {
                    $arr = explode($company->email, ';');
                    $email = trim($arr[0]);
                }

                if(!Contact::where('email', $email)->exists())
                {
                    $contact = new Contact;
                        $contact->email = $email;
                        $contact->nome = $company->rag_soc;
                        $contact->indirizzo = $company->address;
                        $contact->cap = $company->zip;
                        $contact->citta = $company->city;
                        $contact->provincia = $company->province;
                        $contact->nazione = $company->nation;
                        $contact->city_id = $company->city_id;
                        $contact->cellulare = (substr($company->mobile, 0, 1) != '+') ? '+'.Country::getCountryPhone($company->nation).$company->mobile : $company->mobile;
                        $contact->origin = $company->origin;
                        $contact->company_id = $company->id;
                        $contact->lingua = strtolower($company->lang);
                        $contact->subscribed = 1;
                    $contact->save();

                    $count++;
                }
            }
        return $count." Contatti creati";
    }


//api/companies/{id} - GET
    public function checkNation(Company $company)
    {
        return $company->nation;
    }


//api/ta/companies/ - GET
    public function taindex()
    {
        $searchCompanies = Cache::remember('searchCompanies', 60, function () {
            $results = [];$count = 0;
            foreach(Company::query()->with('contacts')->where('active', 1)->whereNotNull('piva')->get() as $company)
            {
                if(!$company->contacts->isEmpty())
                {
                    $results[$count]['name'] = $company->rag_soc . ' - ' .$company->contacts->first()->fullname . ' - ' . $company->email . ' - ' . $company->phone ?? $company->mobile;
                }
                else
                {
                    $results[$count]['name'] = $company->rag_soc . ' - ' . $company->email . ' - ' . $company->phone;
                }
                $results[$count]['id'] = $company->id;
                $count++;
            }
            return $results;
        });
        return $searchCompanies;
    }

//api/companies/{company}/discount-exemption - GET
    public function discountExemption(Company $company)
    {
        return $company;
    }

//api/companies/{company}/discount-exemption - GET
    public function payment(Company $company)
    {   if(is_null($company->pagamento))
        {
            return '';
        }
        return config('invoice.payment_types')[$company->pagamento];
    }

    public function getNote(Company $company)
    {
        return $company->note;
    }

    public function addNote(Request $request, Company $company)
    {
        $company->note = $request->obj;
        $company->save();
        return 'done';
    }


    public function addSede(Request $request)
    {
        $sede = new Sede;
        $sede->nome = $request->nome;
        $sede->indirizzo = $request->indirizzo;
        $sede->cap = $request->cap;
        $sede->citta = $request->citta;
        $sede->provincia = $request->provincia;
        $sede->paese = $request->paese;
        $sede->telefono = $request->telefono;
        $sede->company_id = $request->company_id;

        $sede->save();
        return redirect()->back()->with('success', 'Nuova Sede creata');   
    }


    public function updateSede(Request $request)
    {
        $sede = \Areaseb\Core\Models\Sede::find($request->id);
        $sede->nome = $request->nome;
        $sede->indirizzo = $request->indirizzo;
        $sede->cap = $request->cap;
        $sede->citta = $request->citta;
        $sede->provincia = $request->provincia;
        $sede->paese = $request->paese;
        $sede->telefono = $request->telefono;

        $sede->save();
        return redirect()->back()->with('success', 'Nuova Sede creata');   


       
    }

    public function deleteSede($id)
    {
        try
        {
            $sede = \Areaseb\Core\Models\Sede::find($id);
            $sede->delete();
        }
        catch(\Exception $e)
        {
            return "Questo elemento è usato da un'altro modulo";
        }
        return redirect()->back();   
    }

    public function checkVies(Request $request, Company $company)
    {
        if($company->is_eu)
        {
            if($company->piva && !$company->privato)
            {
                $url = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
                $client = new \SoapClient($url);
                $piva = $company->piva;
                if(strpos($company->piva, $company->nation) !== false)
                {
                    $piva = substr($company->piva, 2);
                }

                $soapmessage = [
                    'countryCode' => $company->nation,
                    'vatNumber' => $piva,
                ];

                $result = $client->checkVat($soapmessage);

                if($result->valid)
                {
                    if(is_null($request->exemption_id))
                    {
                        return ['status' => 'warning', 'result' => $result->valid, 'response' => 'Azienda presente in VIES, ma scegli esenzione'];
                    }
                    else
                    {
                        return ['status' => 'success', 'result' => $result->valid, 'response' => 'Azienda presente in VIES e esenzione correttamente impostata!'];
                    }
                }
                else
                {
                    if(is_null($request->exemption_id))
                    {
                        return ['status' => 'warning', 'result' => $result->valid, 'response' => "Azienda non presente in VIES, l'azienda dovrà pagare l'IVA per intero"];
                    }
                    else
                    {
                        return ['status' => 'error', 'result' => $result->valid, 'response' => "Azienda non presente in VIES, l'esenzione non è valida e l'azienda dovrà pagare l'IVA per intero"];
                    }
                }
            }
            else
            {
                return ['status' => 'success', 'response' => "Privato Europeo paga sempre iva!"];
            }
        }
        else
        {
            if(is_null($request->exemption_id))
            {
                return ['status' => 'warning', 'response' => 'Ricordati di selezionare un esenzione'];
            }
            else
            {
                return ['status' => 'success', 'response' => "Esenziona impostata per azienda extra-EU."];
            }
        }
    }


    public function firstContact(Contact $contact)
    {
        if($contact->exists())
        {
            return $contact;
        }
        return null;
    }

    private function testimonialsArray()
    {
        if(\Illuminate\Support\Facades\Schema::hasTable('testimonial_company'))
        {
            return \Areaseb\Referrals\Models\Testimonial::testimonialsArray();
        }
        return [''=>''];
    }

    private function agentsArray()
    {
        if(\Illuminate\Support\Facades\Schema::hasTable('agent_company'))
        {
            return \Areaseb\Agents\Models\Agent::agentsArray();
        }
        return [''=>''];
    }
    
    public function merge()
    {
        $companies = [''=>''] + Company::query()->where('supplier', 0)->pluck('rag_soc', 'id')->toArray();
        
        return view('areaseb::core.companies.merge', compact('companies'));
    }
    
    public function mergeDb()
    {
        
        
        $company1 = Company::find(request('company1'));
        $company2 = Company::find(request('company2'));
        
        //dd($company1->contacts->first()->id);
        
        if(request('company1_tipo') == 'T'){
        	
        	if(Contact::where('company_id', $company1->id)->exists()){
        		$contact = Contact::where('company_id', $company1->id)->first();
        		Contact::where('company_id', $company1->id)->update(['company_id' => $company2->id, 'contact_type_id' => request('company1_tipo_contatto')]);
        	} else {
	        	$rag_soc = explode(' ', $company1->rag_soc);
	        	$cognome = end($rag_soc);
	        	$nome = '';
	        	for($i = 0; $i <= count($rag_soc)-2; $i++){
	        		$nome .= $rag_soc[$i].' ';
	        	}
	        	
	        	$contact = new Contact;
	            $contact->nome = $nome;
	            $contact->cognome = $cognome;
	            $contact->nazione = $company1->nation;
	            $contact->lingua = $company1->lang;
	            $contact->indirizzo = $company1->address;
	            $contact->citta = $company1->city;
	            $contact->cap = $company1->zip;
	            $contact->provincia = $company1->province;
	            $contact->email = $company1->email;
	            $contact->cellulare = $company1->mobile;
	            $contact->city_id = $company1->city_id;
	            $contact->origin = 'Vecchio gestionale';
	            $contact->company_id = $company2->id;
	            $contact->attivo = true;
	            $contact->contact_type_id = request('company1_tipo_contatto');

	            $contact->luogo_nascita = $company1->luogo_nascita;
	            $contact->data_nascita = $company1->data_nascita;
	            $contact->nickname = $company1->nickname;
	            $contact->sesso = $company1->sesso;
	            $contact->cod_fiscale = $company1->cf;


	            if(is_null($company1->email))
	            {
	                $contact->subscribed = 0;
	            }
	            $contact->save();
	            
	        }        	
        }
        
        
        if(CollettivoAllievi::where('id_cliente', $company1->id)->exists()){
        	if(request('company1_tipo') == 'T'){
        		CollettivoAllievi::where('id_cliente', $company1->id)->update(['id_cliente' => $company2->id, 'partecipante' => $contact->id]);
        	} else {
        		CollettivoAllievi::where('id_cliente', $company1->id)->update(['id_cliente' => $company2->id]);
        	}        	
        }
        
        if(request('company1_tipo') == 'T'){
        	if(\DB::table('event_company')->where('company_id', $company1->id)->exists()){
	        	$events = \DB::table('event_company')->where('company_id', $company1->id)->get();
	        	foreach($events as $event){
	        		\DB::table('event_contact')->insert(
					    ['event_id' => $event->event_id, 'contact_id' => $contact->id]
					);
	        	}
	        	\DB::delete('delete from event_company where company_id = ?',[$company1->id]);
	        }
        } else {
        	if(\DB::table('event_company')->where('company_id', $company1->id)->exists()){
	        	\DB::table('event_company')->where('company_id', $company1->id)->update(['company_id' => $company2->id]);
	        }
        }

        
        if(Invoice::where('company_id', $company1->id)->exists()){
        	if(request('company1_tipo') == 'T'){
        		Invoice::where('company_id', $company1->id)->update(['contact_id' => $contact->id, 'company_id' => null]);
        	} else {
        		Invoice::where('company_id', $company1->id)->update(['company_id' => $company2->id]);
        	}        	
        }
        
        if(Note::where('company_id', $company1->id)->exists()){
        	Note::where('company_id', $company1->id)->update(['company_id' => $company2->id]);
        }
     
        if(Ora::where('id_cliente', 'Y_'.$company1->id)->exists()){
        	if(request('company1_tipo') == 'T'){
        		\Log::info('Sposto le ore dal cliente ' . $company1->id . ' al cliente contatto ' . $contact->id);   
        		Ora::where('id_cliente', 'Y_'.$company1->id)->update(['id_cliente' => 'T_'.$contact->id]);
        	} else {
        		\Log::info('Sposto le ore dal cliente ' . $company1->id . ' al cliente contatto ' . $company2->id);  
        		Ora::where('id_cliente', 'Y_'.$company1->id)->update(['id_cliente' => 'Y_'.$company2->id]);
        	}        	
        }
        
        \DB::table('company_branch')->where('company_id', $company1->id)->delete();
        Company::where('id', $company1->id)->delete();
        
        $companies = [''=>''] + Company::query()->where('supplier', 0)->pluck('rag_soc', 'id')->toArray();
        
        return view('areaseb::core.companies.merge', compact('companies'))->with('message', 'Clienti uniti con successo');
    }


}
