<?php

namespace Areaseb\Core\Http\Controllers;

use App\User;
use Areaseb\Core\Models\{Calendar, City, Client, Company, Contact, Country, Setting, TypeUser};
use Areaseb\Core\Mail\NewAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

use Illuminate\Support\Facades\Mail;


class UserController extends Controller
{
    public function index()
    {
        if(request()->input())
        {
            $users = Role::find(request('role'))->users;
        }
        else
        {
            $users = User::where('id', '!=', 1)->get();
        }
        $roles = Role::all();
        return view('areaseb::core.users.index', compact('roles', 'users'));
    }

    public function create()
    {
        $roles = Role::pluck('name', 'id');
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $companies[''] = '';
        $companies += Company::pluck('rag_soc', 'id')->toArray();
        $contacts[''] = '';
        $Listcontacts = Contact::select('nome','cognome', 'id')->get();
        foreach($Listcontacts as $item)
         {
            $contacts[$item->id] = $item->nome.' '.$item->cognome;
         }

        return view('areaseb::core.users.create', compact('roles', 'provinces', 'countries', 'companies','contacts'));
    }

    public function store()
    {

        //$mailer = app()->makeWith('custom.mailer', Setting::smtp(0));
       
        $this->validate(request(),[
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'nome' => 'required',
            'cognome' => 'required',
            'role_id' => 'required',
            'company_id' => 'required'
        ]);

        $user = new User;
        $user->name = request('nome') . ' ' . request('cognome');
        $user->email = request('email');
        $user->password = bcrypt(request('password'));
        $user->save();


        $user->assignRole(Role::find(request('role_id')));

        $data = request()->input();
        $data['_method'] = 'POST';
        Contact::where('id', $data['company_id'])->update(['user_id' => $user->id]);
        //$contact = Contact::createOrUpdate(new Contact, $data, $user->id);

        Calendar::create(['user_id' => $user->id, 'token' => \Str::random(33)]);

        if(Schema::hasTable('testimonials'))
        {
            if(in_array(Role::where('name', 'testimonial')->first()->id, request('role_id')))
            {
                \Areaseb\Referrals\Models\Testimonial::create([
                    'user_id' => $user->id,
                    'contact_id' => $user->contact->id,
                    'company_id' => request('company_id')
                ]);
            }
        }

        if(Schema::hasTable('agents'))
        {
            if(in_array(Role::where('name', 'agent')->first()->id, request('role_id')))
            {
                \Areaseb\Agents\Models\Agent::create([
                    'user_id' => $user->id,
                    'contact_id' => $user->contact->id,
                    'company_id' => request('company_id')
                ]);
            }
        }

        if(request('sendEmail'))
        {
            if(!Setting::validSmtp(0))
            {
                return redirect(route('users.index'))->with('message', 'Utente Creato e ma non abbiamo spedito nessuna email perché non hai settato il server di posta');
            }
            else
            {
                $dsn = 'smtp://'.Setting::smtp(0)['MAIL_USERNAME'].':'.Setting::smtp(0)['MAIL_PASSWORD'].'@'.Setting::smtp(0)['MAIL_HOST'].':'.Setting::smtp(0)['MAIL_PORT'];
                $data = array(
                    'name' => request('nome'),
                    'surname' => request('cognome'),
                    'email' => request('email'),
                    "pw" => request('password')               
                );

                //DEFINISCO IL MAILER IN BASE ALLA CONFIGURAZIONE SMTP SCELTA
                Mail::mailer($dsn);
                Mail::send('areaseb::emails.users.new-account-mail',$data, function ($message) use ($data)
                {
                    $message->to($data['email'])
                        ->subject('Nuovo account '.config('app.name'));
                    $message->from(Setting::smtp(0)['MAIL_FROM_ADDRESS']);
                });

                //$mailer = app()->makeWith('custom.mailer', Setting::smtp(0));
                //$mailer->to($user->email)->send(new NewAccount($user, request('password')));
                return redirect(route('users.index'))->with('message', 'Utente Creato e Password inviata a '.$user->email);
            }
        }

        return redirect(route('users.index'))->with('message', 'Utente Creato');
    }

    public function edit($id)
    {
        $roles = Role::pluck('name', 'id');
        $provinces = City::uniqueProvinces();
        $countries = Country::listCountries();
        $companies[''] = '';
        $companies += Company::pluck('rag_soc', 'id')->toArray();
        $element = User::findOrFail($id);

        $contacts[''] = '';
        $all_contacts = Contact::all();
        foreach($all_contacts as $contact){
        	$contacts += [$contact->id => $contact->fullname];
        }
        $user_contact = $element->contact;

        return view('areaseb::core.users.edit', compact('roles', 'provinces', 'countries', 'companies', 'element','contacts', 'user_contact'));
    }

    public function update($id)
    {
        $user = User::findOrFail($id);

        $this->validate(request(),[
            'email' => 'required|string|email|unique:users,email,'.$user->id.',id',
            'nome' => 'required',
            'cognome' => 'required',
            'role_id' => 'required'
        ]);

        if( request()->has('password') )
        {
            $user->password = bcrypt(request('password'));
        }

        $user->email = request('email');
        $user->save();

        $roles = Role::whereIn('id', request('role_id'))->pluck('name')->toArray();
        $user->syncRoles($roles);

        $contact = $user->contact;
        if(is_null($contact))
        {
        	$contact = Contact::where('id', request('company_id'))->first();
        } 
        if(is_null($contact)) 
        {
            $contact = new Contact;
        }

        $data = request()->input();
        $data['_method'] = 'PATCH';
        Contact::where('id', $data['company_id'])->update(['user_id' => $user->id]);
        //Contact::createOrUpdate($contact, $data, $user->id);

        if(Schema::hasTable('testimonials'))
        {
            if(in_array(Role::where('name', 'testimonial')->first()->id, request('role_id')))
            {
                $testimonial = \Areaseb\Referrals\Models\Testimonial::where('user_id', $user->id)
                                ->where('contact_id', $user->contact->id)
                                ->where('company_id', request('company_id'))
                                ->first();

                if(is_null($testimonial))
                {
                    \Areaseb\Referrals\Models\Testimonial::create([
                        'user_id' => $user->id,
                        'contact_id' => $user->contact->id,
                        'company_id' => request('company_id')
                    ]);
                }
            }
        }
        if(Schema::hasTable('agents'))
        {
            if(in_array(Role::where('name', 'agent')->first()->id, request('role_id')))
            {
                $agent = \Areaseb\Agents\Models\Agent::where('user_id', $user->id)
                                ->where('contact_id', $user->contact->id)
                                ->where('company_id', request('company_id'))
                                ->first();

                if(is_null($agent))
                {
                    \Areaseb\Agents\Models\Agent::create([
                        'user_id' => $user->id,
                        'contact_id' => $user->contact->id,
                        'company_id' => request('company_id')
                    ]);
                }
            }
        }

        return redirect(route('users.index'))->with('message', 'Utente Modificato');
    }

    public function permissions($id)
    {
        $allPermissions = Permission::all();
        $permissions = [];
        foreach ($allPermissions as $permission)
        {
            $arr = explode('.',$permission->name);
            $permissions[$arr[0]][] = [
                'id' => $permission->id,
                'action' => $arr[1]
            ];
        }
        $utente = User::find($id);
        $role = $utente->roles()->first();


        return view('areaseb::core.users.permissions', compact('utente', 'role', 'permissions') );
    }

//api/direct-permissions/{user_id}
     public function permissionUpdate($id)
     {
         $user = User::find($id);
         $permission = Permission::find(request('id'));

         if(request('add') == 'true')
         {
             $user->givePermissionTo($permission->name);
             $azione = 'aggiunto';
         }
         else
         {
             $user->revokePermissionTo($permission->name);
             $azione = 'revocato';
         }

         $arr = explode('.',$permission->name);


         return 'Permesso '.trans('permissions.'.$arr[0]).' '.trans('permissions.'.$arr[1]).' '.$azione.' a '.$user->contact->fullname;
     }

     public function destroy($id)
     {
         $user = User::findOrFail($id);

         $contact = $user->contact;
         if($contact)
         {
             $contact->update(['user_id' => null]);
         }

         foreach($user->events as $event)
         {
             $event->delete();
         }

		 if($user->notes){
		 	foreach($user->notes as $note)
	         {
	             $note->delete();
	         }
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
         return 'done';
     }

     public function editPassword(User $user)
     {
         $userToC = $user;
         return view('areaseb::core.users.password', compact('userToC') );
     }

     public function updatePassword(Request $request, User $user)
     {
         $user->update(['password' => bcrypt($request->password)]);
         return redirect($request->origin)->with('message', 'Password Aggiornata');
     }

}
