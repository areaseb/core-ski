<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Contact, NewsletterList};

class NewsletterListController extends Controller
{

//create-list - GET
    public function createList()
    {
        if(request()->input())
        {
            $query = Contact::filter(request());
        }
        else
        {
            $query = Contact::query();
        }

        $contacts = $query->paginate(100);

        return view('areaseb::core.lists.create-list', compact('contacts'));
    }

//create-list - POST
    public function createListPost(Request $request)
    {

        $region = 'region=';
        if(isset($request->region))
        {
            $region .= implode('|',$request->region);
        }
        $province = 'province=';
        if(isset($request->province))
        {
            $province .= implode('|',$request->province);
        }
        $tipo = 'tipo=';
        if(isset($request->tipo))
        {
            $tipo .= implode('|',$request->tipo);
        }
        $list = is_null($request->list) ? '&list='.$request->list : '&list=';
        $created_at = is_null($request->created_at) ? '&created_at='.$request->created_at : '&created_at=';
        $updated_at = is_null($request->updated_at) ? '&updated_at='.$request->updated_at : '&updated_at=';

        $single = $list.$created_at.$updated_at;

        $query = '?'.$region.'&'.$province.'&'.$tipo.$single;

        return redirect('create-list'.$query);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()->hasRole(['testimonial', 'agent']))
        {
            $lists = NewsletterList::where('owner_id', auth()->user()->id)->get();
        }
        else
        {
            $lists = NewsletterList::latest()->get();
        }

        return view('areaseb::core.lists.index', compact('lists'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('areaseb::core.lists.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $newsletterList = new NewsletterList;
            $newsletterList->nome = $request->nome;
            $newsletterList->owner_id = auth()->user()->id;
        $newsletterList->save();

        foreach(Contact::filter($request)->get() as $contact)
        {
            $contact->lists()->attach($newsletterList->id);
        }

        return back()->with('message', 'Lista Creata');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, NewsletterList $list)
    {
        if(auth()->user()->hasRole(['testimonial', 'agent']))
        {
            $lists = NewsletterList::where('owner_id', auth()->user()->id)->get();
        }
        else
        {
            $lists = NewsletterList::all();
        }

        if($request->get('sort'))
        {
            $arr = explode('|', $request->sort);
            $contacts = $list->contacts()->orderBy($arr[0], $arr[1])->get();
        }
        else
        {
            $contacts = $list->contacts;
        }

        $availables =[];
        foreach(Contact::with('company')->get() as $c)
        {
            if($c->company)
            {
                $availables[$c->id] = $c->company->rag_soc . ' | ' . $c->fullname;
            }
            else
            {
                $availables[$c->id] = $c->fullname;
            }

            if($c->subscribed == 0)
            {
                $availables[$c->id] .= ' NON ISCRITTO';
            }

        }

        $options = '';
        foreach($lists as $value)
        {
            if($value->id != $list->id)
            {
                $options .= '<option value="'.$value->id.'">'.$value->nome.'</option>';
            }
        }
        return view('areaseb::core.lists.show', compact('list', 'options', 'contacts', 'availables'));
    }


    public function updateContacts(Request $request, NewsletterList $list)
    {
        $contacts = Contact::whereIn('id', $request->contact_id)->get();
        if($request->action == 'remove')
        {
            foreach($contacts as $contact)
            {
                \DB::table('contact_list')->where('contact_id', $contact->id)->where('list_id', $list->id)->delete();
            }
            return 'done';
        }
        elseif($request->action == 'copy')
        {
            foreach($contacts as $contact)
            {
                $contact->lists()->syncWithoutDetaching($request->target_list_id);
            }
            return 'done';
        }
        elseif($request->action = 'move')
        {
            foreach($contacts as $contact)
            {
                $contact->lists()->detach($list->id);
                $contact->lists()->attach($request->target_list_id);
            }
            return 'done';
        }
        return null;
    }


    public function addContact(Request $request)
    {
        $contact = Contact::find($request->contact_id);
        $nl[] = $request->list_id;
        if($contact->lists()->exists())
        {
            $contact->lists()->syncWithoutDetaching($nl);
        }
        else
        {
            $contact->lists()->attach($nl);
        }

        return 'done';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function destroy(NewsletterList $list)
    {
        $list->delete();
        return 'done';
    }


//lists/{list}/contacts/{contact}
    public function removeContactFromList(NewsletterList $list, Contact $contact)
    {
        $contact->lists()->detach($list->id);
        return 'done';
    }

    public function duplicate(Request $request)
    {
        $list = NewsletterList::find($request->list_id);

        $new = $list->replicate();
        $new->save();
        $new->update(['nome' => $list->nome . '(2)']);

        foreach($list->contacts as $c)
        {
            $c->lists()->syncWithoutDetaching($new->id);
        }


        return back()->with('message', 'Lista duplicata');
    }

}
