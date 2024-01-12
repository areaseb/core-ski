<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{City, Client, Company, Contact, Country, Exemption, Sector, Sede, TypeUser};
use App\User;
use \Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;

class MasterController extends Controller
{

    
    public function index()
    {
        if(request()->input())
        {
            $masters = Company::filter(request())->where('client_id','!=', 4)->orderBy('rag_soc', 'ASC')->get();
        }
        else{
            $masters = Company::where('client_id', '!=', 4)->with('client', 'sector')->orderBy('rag_soc', 'ASC')->paginate(30);
        }

        
        foreach ($masters as $key => $value) {
            if($value->contacts()->first()->contact_type_id == null || $value->contacts()->first()->contact_type_id != 3)
                $masters->forget($key);
        }


        return view('areaseb::core.masters.index', compact('masters'));
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

    public function create()
    {
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $clients = Client::company()->pluck('nome', 'id')->toArray();;
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

        $contact_type = [''=>'']+TypeUser::pluck('descrizione', 'id')->toArray();
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();

        return view('areaseb::core.masters.create', compact('branches','contact_type','provinces', 'countries', 'clients', 'referenti', 'sectors', 'exemptions', 'origins','testimonials', 'agents'));
    }


    public function store(Request $request)
    {

        if(isset($request->createContact))
        {
            $this->validate(request(),[
                'nome' => "required",
                'cognome' => "required",
                'rag_soc' => "required|unique:companies,rag_soc",
                'piva' => "required_if:privato,0",
                'pec' => "nullable|unique:companies,pec",
                's1' => "nullable|numeric|between:0.00,99.99"
            ]);
        }
        else
        {
            $this->validate(request(),[
                'rag_soc' => "required|unique:companies,rag_soc",
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

        $company = new Company;
            $company->rag_soc = $request->rag_soc;
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
            $company->phone = $request->phone;
            $company->mobile = $request->mobile;
            $company->private = $request->private;
            $company->sdi = $request->sdi;
            $company->pec = $request->pec;
            $company->piva = $request->piva;
            $company->cf = $request->cf;
            $company->supplier = $request->supplier;
            $company->partner = $request->partner;
            $company->active = $request->active;
            $company->parent_id = $request->parent_id;
            $company->sector_id = $sector_id;
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

            $company->save();


            foreach ($request->branch_id as $value) {
                \DB::table('company_branch')->insert(
                    ['company_id' => $company->id, 'branch_id' => $value]
                );
            }
            
        if(isset($request->createContact))
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
                $contact->cellulare = $request->mobile;
                $contact->city_id = $company->city_id;
                $contact->origin = $request->origin;
                $contact->company_id = $company->id;
                $contact->attivo = true;
                $contact->contact_type_id = 3;
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

        if($request->previous)
        {
            return view('areaseb::core.companies.show', compact('company'))->with('message', 'Azienda Creata');
            //return redirect($request->previous)->with('message', 'Azienda Creata');
        }

        return view('areaseb::core.companies.show', compact('company'))->with('message', 'Azienda Creata');
        //return redirect(route('companies.index'))->with('message', 'Azienda Creata');
    }


    public function show(Company $master)
    {
        $company = $master;
        return view('areaseb::core.masters.show', compact('company'));
    }


    public function edit(Company $master)
    {
        $company = $master;
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
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        return view('areaseb::core.masters.edit', compact('branches','contact_type','provinces', 'countries', 'company', 'clients', 'referenti', 'sectors', 'exemptions', 'origins', 'testimonials', 'agents'));
    }


    public function update(Request $request, Company $master)
    {
        $company = $master;
        $this->validate(request(),[
            'rag_soc' => "required|unique:companies,rag_soc,".$company->id.",id",
            'piva' => "required_if:privato,0",
            'pec' => "nullable|unique:companies,pec,".$company->id.",id",
            's1' => "nullable|min:1|max:99"
        ]);

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

            $company->rag_soc = $request->rag_soc;
            $company->nation = $request->nation;
            $company->lang = $lang;
            $company->address = $request->address;
            $company->city = $request->city;
            $company->zip = $request->zip;
            $company->province = $request->province;
            $company->email = $request->email;
            $company->email_ordini = $request->email_ordini;
            $company->email_fatture = $request->email_fatture;
            $company->phone = $request->phone;
            $company->mobile = $request->mobile;
            $company->website = $request->website;
            $company->private = $request->private;
            $company->sdi = $request->sdi;
            $company->pec = $request->pec;
            $company->piva = $request->piva;
            $company->cf = $request->cf;
            $company->settore = $request->settore;
            $company->supplier = $request->supplier;
            $company->partner = $request->partner;
            $company->active = $request->active;
            $company->parent_id = $request->parent_id;
            $company->sector_id = $sector_id;
            $company->exemption_id = $request->exemption_id;
            $company->pagamento = $request->pagamento;
            $company->city_id = City::getCityIdFromData($request->province, $request->nation, $request->city);
            $company->s1 = $request->s1;
            $company->s2 = $request->s2;
            $company->s3 = $request->s3;
            $company->client_id = $request->client_id;
            $company->origin = $request->origin;
            $company->lat = $request->lat;
            $company->lng = $request->lng;
        $company->save();


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
            return redirect($request->previous)->with('message', 'Maestro Aggiornato');
        }

        return redirect(route('masters.index'))->with('message', 'Maestro Aggiornato');
    }


}
