<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{City, Client, Company, Contact, Country, Exemption, Sector, Sede};
use App\User;
use \Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;

class CompanyController extends Controller
{
    public function index()
    {
        if(request()->input())
        {
            $companies = Company::filter(request())->orderBy('rag_soc', 'ASC')->paginate(30);
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
            $companies = $query->with('client', 'sector')->orderBy('rag_soc', 'ASC')->paginate(30);
        }

        return view('areaseb::core.companies.index', compact('companies'));
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

        return view('areaseb::core.companies.create', compact('provinces', 'countries', 'clients', 'referenti', 'sectors', 'exemptions', 'origins','testimonials', 'agents'));
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
                if(is_null($request->email))
                {
                    $contact->subscribed = 0;
                }
            $contact->save();
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
            return redirect($request->previous)->with('message', 'Azienda Creata');
        }

        return redirect(route('companies.index'))->with('message', 'Azienda Creata');
    }


    public function show(Company $company)
    {
        return view('areaseb::core.companies.show', compact('company'));
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

        return view('areaseb::core.companies.edit', compact('provinces', 'countries', 'company', 'clients', 'referenti', 'sectors', 'exemptions', 'origins', 'testimonials', 'agents'));
    }


    public function update(Request $request, Company $company)
    {
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
            return "Questo elemento è usato da un'altro modulo";
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
                        $contact->cellulare = $company->mobile;
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
            foreach(Company::with('contacts')->get() as $company)
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


    public function firstContact(Company $company)
    {
        if($company->contacts()->exists())
        {
            return $company->contacts()->first();
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


}
