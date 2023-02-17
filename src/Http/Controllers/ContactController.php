<?php

namespace Areaseb\Core\Http\Controllers;

use Areaseb\Core\Models\{City, Contact, Country, Client, Company, NewsletterList};
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
        $companies += Company::pluck('rag_soc', 'id')->toArray();
        $users[''] = '';
        $users += User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        $lists = NewsletterList::pluck('nome', 'id')->toArray();
        $pos = ['' => '']+Contact::uniquePos();
        $origins = ['' => '']+Contact::uniqueOrigin();

        $testimonials = $this->testimonialsArray();
        $agents = $this->agentsArray();

        return view('areaseb::core.contacts.create', compact('provinces', 'countries', 'companies', 'users', 'lists', 'pos', 'origins', 'testimonials', 'agents'));
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
            'email' => 'required|email|unique:contacts'
        ]);
        $contact = Contact::createOrUpdate(new Contact, request()->input());

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

    /**
     * Display the specified resource.
     *
     * @param  \App\Contacts\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact)
    {
        return view('areaseb::core.contacts.show', compact('contact'));
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
        $companies += Company::pluck('rag_soc', 'id')->toArray();
        $users[''] = '';
        $users += User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        $lists = NewsletterList::pluck('nome', 'id')->toArray();

        $pos = ['' => '']+Contact::uniquePos();
        $origins = ['' => '']+Contact::uniqueOrigin();
        $testimonials = $this->testimonialsArray();
        $agents = $this->agentsArray();
        return view('areaseb::core.contacts.edit', compact('provinces', 'countries', 'companies', 'users', 'contact', 'lists', 'pos', 'origins', 'testimonials', 'agents'));
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
            'email' => "required|email|unique:contacts,email,".$contact->id.",id"
        ]);
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


//api/ta/contacts
    public function taindex()
    {
        $contacts = [];$count = 0;

        foreach(Contact::all('nome', 'cognome', 'id') as $contact)
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
