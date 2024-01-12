<?php

namespace Areaseb\Core\Http\Controllers;

use Areaseb\Core\Models\{City, Contact, Country, Client, Branch, Company, NewsletterList, Sede, TypeUser, DisabileSedute, DisabileTipo, DisabileAttrezzi, Specialization, Availability, DownPayment };
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use \Spatie\Permission\Models\Role;

class ContactMasterController extends Controller
{
    public function index()
    {
        $branches = Sede::all();
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
        //$branches =[''=>'']+Sede::pluck('nome', 'id')->toArray();
        
        $contacts = $query->where('contact_type_id',3)->orderBy('attivo', 'DESC')->orderby('cognome', 'ASC')->paginate(100);

        return view('areaseb::core.contacts_master.index', compact('contacts','branches'));
    }


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

        return view('areaseb::core.contacts_master.create', compact('provinces', 'specializzazioni', 'contact_type', 'disabile_sedute', 'disabile_tipo','disabile_attrezzi','branches','countries', 'companies', 'users', 'lists', 'pos', 'origins', 'testimonials', 'agents'));
    }


    public function store(Request $request)
    {
        $data = request()->input();
        //$data['company_id'] = explode(" ", $data['branch_id']);
        $data['company_id'] = Branch::where('id', $data['branch_id'])->first()->company_id;
        $data['contact_type_id'] = 3;
        $data['branch_id'] = explode(" ", $data['branch_id']);
        $data['_method'] = 'PUT';
        //dd($data);
        //$data['company_id']
        //$data['branch_id']
        $this->validate(request(), [
            'nome' => 'required',
            'cognome' => 'required',
            'email' => 'required|email'
        ]);
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

        return redirect(route('contacts_master.index'))->with('message', 'Contatto Creato');
    }


    public function show(Contact $contactsMaster)
    {
        $contact = $contactsMaster;
        $records = DownPayment::where('contact_id',  $contact->id)->orderBy('created_at', 'DESC')->paginate(30);
        $payment_type_id = ['' => '','0' =>'Bonifico', '1' =>'Contanti', '2' =>'Assegno', ];

        $availabilities = Availability::where('contact_id',  $contact->id)->orderBy('data_start', 'DESC')->paginate(30);
        $branches = Sede::orderBy('nome')->pluck('nome','id')->toArray();
        foreach ($availabilities as $value) {
            $value->branch_desc = Sede::where('id',  $value->branch_id)->first()->nome;
        }

        return view('areaseb::core.contacts_master.show', compact('contact','records','payment_type_id', 'branches','availabilities'));
    }


    public function edit(Contact $contactsMaster)
    {
        $contact = $contactsMaster;
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
        $contact_type = [''=>'']+TypeUser::pluck('descrizione', 'id')->toArray();

        $disabile_sedute = DisabileSedute::pluck('nome', 'id')->toArray();
        $disabile_tipo = DisabileTipo::pluck('nome', 'id')->toArray();
        $disabile_attrezzi = DisabileAttrezzi::pluck('nome', 'id')->toArray();

        $specializzazioni = Specialization::pluck('nome', 'id')->toArray();
        
        return view('areaseb::core.contacts_master.edit', compact('provinces','specializzazioni','contact_type', 'disabile_sedute', 'disabile_tipo','disabile_attrezzi','branches', 'countries', 'companies', 'users', 'contact', 'lists', 'pos', 'origins', 'testimonials', 'agents'));
    }


    public function update(Request $request, Contact $contactsMaster)
    {

        $contact = $contactsMaster;
        $data = request()->input();
        $data['contact_type_id'] = 3;
        $data['company_id'] = Branch::where('id', $data['branch_id'])->first()->company_id;
        $data['branch_id'] = explode(" ", $data['branch_id']);
        $data['_method'] = 'PATCH';
        $data['contact_id'] = $contact->id;
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
    public function destroy(Contact $contactsMaster)
    {
        $contact = $contactsMaster;
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
        //return redirect(route('contacts-master.index'))->with('message', 'Maestro eliminato');
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
