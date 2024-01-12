<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Sede, Contact, City, Country, Company, Exemption};
use App\User;
use \Carbon\Carbon;


class OfficeController extends Controller
{

    public function index()
    {
        if(request()->input())
        {
            $offices = Company::filter(request())->where('client_id', 4)->orderBy('rag_soc', 'ASC')->paginate(30);
        }
        else{
            $offices = Company::where('client_id', 4)->with('client', 'sector')->orderBy('rag_soc', 'ASC')->paginate(30);
        }
        return view('areaseb::core.offices.index', compact('offices'));
    }


    public function create()
    {
        $companies = Company::pluck('rag_soc','id' )->toArray();
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $exemptions = ['' => '']+Exemption::esenzioni();
        return view('areaseb::core.offices.create',compact('provinces', 'countries', 'companies','exemptions'));
    }

    public function store(Request $request)
    {

        $this->validate(request(),[
            'rag_soc' => "required",	//|unique:companies,rag_soc
            'piva' => "required_if:privato,0",
            'pec' => "nullable|unique:companies,pec",
            's1' => "nullable|numeric|between:0.00,99.99"
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
        $company->supplier = 0;
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
        $company->client_id = 4;
        $company->origin = $request->origin;
        $company->lat = $request->lat;
        $company->lng = $request->lng;
        $company->save();
        
        $office = new Sede;
        $office->nome = request('rag_soc');
        $office->paese = request('nation');
        $office->indirizzo = request('address');
        $office->citta = request('city');
        $office->cap = request('zip');
        $office->provincia = request('province');
        $office->company_id = $company->id;
        $office->telefono = request('mobile');
        $office->save();

        return back()->with('message', 'Sede Aggiunta');
    }


    public function show(Company $office)
    {
        $company = $office;
        return view('areaseb::core.offices.show', compact('company'));
    }



    public function edit(Company $office)
    {
        $companies = Company::pluck('rag_soc','id' )->toArray();

       // $office = Company::where('id', $office->company_id)->first();
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $exemptions = ['' => '']+Exemption::esenzioni();
        return view('areaseb::core.offices.edit', compact('provinces', 'countries', 'office','companies','exemptions'));
    }

    public function update(Request $request, Company $office)
    {

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

            $office->rag_soc = $request->rag_soc;
            $office->nation = $request->nation;
            $office->lang = $lang;
            $office->address = $request->address;
            $office->city = $request->city;
            $office->zip = $request->zip;
            $office->province = $request->province;
            $office->email = $request->email;
            $office->email_ordini = $request->email_ordini;
            $office->email_fatture = $request->email_fatture;
            $office->phone = $request->phone;
            $office->mobile = $request->mobile;
            $office->website = $request->website;
            $office->private = $request->private;
            $office->sdi = $request->sdi;
            $office->pec = $request->pec;
            $office->piva = $request->piva;
            $office->cf = $request->cf;
            $office->settore = $request->settore;
            $office->partner = $request->partner;
            $office->active = $request->active;
            $office->parent_id = $request->parent_id;
            $office->sector_id = $sector_id;
            $office->exemption_id = $request->exemption_id;
            $office->pagamento = $request->pagamento;
            $office->city_id = City::getCityIdFromData($request->province, $request->nation, $request->city);
            $office->s1 = $request->s1;
            $office->s2 = $request->s2;
            $office->s3 = $request->s3;
            $office->origin = $request->origin;
            $office->lat = $request->lat;
            $office->lng = $request->lng;
            $office->save();


            $office = Sede::where('company_id', $office->id)->first();

            $office->nome = $request->rag_soc;
            $office->indirizzo = $request->address;
            $office->cap = $request->zip;
            $office->citta = $request->city;
            $office->provincia = $request->province;
            $office->paese = $request->nation;
            $office->telefono = $request->phone;
            $office->save();

        return back()->with('message', 'Sede Aggiornata');
    }

    public function destroy(Company $office)
    {
        $sede = Sede::where('company_id', $office->id)->first();
        $sede->contacts()->delete();

        $sede->delete();
        $office->delete();        
        return 'done';
    }

    public function destroyAjax(Sede $office)
    {
        Sede::where('company_id', $office->company_id)->delete();
        $office->delete();  
        return 'done';
    }


    public function getNumOrder(Request $request)
    {
        $count_max = Contact::select('contact_branch.*')
                                ->Join('contact_branch', 'contact_branch.contact_id', '=', 'contacts.id')
                                ->where('contacts.contact_type_id', 3)
                                ->where('contact_branch.branch_id',$request->branch_id)
                                ->count() + 1;  

        return ['ordine' => $count_max];
    }
}
