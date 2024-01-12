<?php

namespace Areaseb\Core\Http\Controllers;

use Areaseb\Core\Models\{City, Contact, Country, Client, Company, NewsletterList, Sede, TypeUser, DisabileSedute, DisabileTipo, DisabileAttrezzi, Specialization};
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use \Spatie\Permission\Models\Role;

class ContactOfficeController extends Controller
{
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

        return view('areaseb::core.contacts_office.index', compact('contacts'));
    }


    public function create()
    {
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $companies[''] = '';
        $companies += Company::where('client_id', 4)->pluck('rag_soc', 'id')->toArray();
        $users[''] = '';
        $users += User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        $lists = NewsletterList::pluck('nome', 'id')->toArray();
        $pos = ['' => '']+Contact::uniquePos();
        $origins = ['' => '']+Contact::uniqueOrigin();

        $testimonials = $this->testimonialsArray();
        $agents = $this->agentsArray();
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        $contact_type = [''=>'']+TypeUser::whereIn('id', [3,4])->pluck('descrizione', 'id')->toArray();

        $disabile_sedute = [''=>'']+DisabileSedute::pluck('nome', 'id')->toArray();
        $disabile_tipo = [''=>'']+DisabileTipo::pluck('nome', 'id')->toArray();
        $disabile_attrezzi = [''=>'']+DisabileAttrezzi::pluck('nome', 'id')->toArray();
        $specializzazioni = [''=>'']+Specialization::pluck('nome', 'id')->toArray();

        return view('areaseb::core.contacts_office.create', compact('provinces', 'specializzazioni', 'contact_type', 'disabile_sedute', 'disabile_tipo','disabile_attrezzi','branches','countries', 'companies', 'users', 'lists', 'pos', 'origins', 'testimonials', 'agents'));
    }


    public function store(Request $request)
    {
        
        $branch_id = explode(' ', Sede::where('company_id', $request->company_id)->first()->id);
       // dd($branch_id);
        $data = request()->input();
        $data['branch_id'] = $branch_id;
        $data['cod_fiscale'] = '0000000000000000';
        $data['luogo_nascita'] = '';
        $data['data_nascita'] = '';
        $data['sesso'] = '';        
        $data['nickname'] = strtoupper(substr($data['nome'], 0, 1).substr($data['cognome'], 0, 1));        
        $data['livello'] = null;  
        $data['note_contact'] = '';  
        $data['note_segreteria'] = '';  
        $data['_method'] = 'POST';
        $this->validate(request(), [
            'nome' => 'required',
            'cognome' => 'required',
            'email' => 'required|email'
        ]);

        //dd($data);
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

        return redirect(route('contacts_office.index'))->with('message', 'Contatto Creato');
    }



    public function show(Contact $contactsOffice)
    {
        $contact = $contactsOffice;
        return view('areaseb::core.contacts_office.show', compact('contact'));
    }


    public function edit(Contact $contactsOffice)
    {
        $contact = $contactsOffice;
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $companies[''] = '';
        $companies += Company::where('client_id', 4)->pluck('rag_soc', 'id')->toArray();
        $users[''] = '';
        $users += User::with('contact')->get()->pluck('contact.fullname', 'id')->toArray();
        $lists = NewsletterList::pluck('nome', 'id')->toArray();

        $pos = ['' => '']+Contact::uniquePos();
        $origins = ['' => '']+Contact::uniqueOrigin();
        $testimonials = $this->testimonialsArray();
        $agents = $this->agentsArray();
        $branches = [''=>'']+Sede::pluck('nome', 'id')->toArray();
        $contact_type = [''=>'']+TypeUser::whereIn('id', [3,4])->pluck('descrizione', 'id')->toArray();

        $disabile_sedute = DisabileSedute::pluck('nome', 'id')->toArray();
        $disabile_tipo = DisabileTipo::pluck('nome', 'id')->toArray();
        $disabile_attrezzi = DisabileAttrezzi::pluck('nome', 'id')->toArray();

        $specializzazioni = Specialization::pluck('nome', 'id')->toArray();
        
        
        return view('areaseb::core.contacts_office.edit', compact('provinces','specializzazioni','contact_type', 'disabile_sedute', 'disabile_tipo','disabile_attrezzi','branches', 'countries', 'companies', 'users', 'contact', 'lists', 'pos', 'origins', 'testimonials', 'agents'));
    }

    
    public function update(Request $request, Contact $contactsOffice)
    {
        $contact = $contactsOffice;
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

        $data = request()->input();

        Contact::createOrUpdate($contact, $data);

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
