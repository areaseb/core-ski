<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Company, Note};
use App\User;
use \Carbon\Carbon;


class NoteController extends Controller
{

    public function index()
    {
        $requests = Note::with('company.contacts')->whereNull('user_id')->latest()->paginate(50);
        return view('areaseb::core.notes.requests', compact('requests'));
    }

    public function create()
    {
        return view('areaseb::core.notes.create');
    }

    public function store(Request $request)
    {
        $note = Note::create($request->except('_token'));
        if($request->filename)
        {

            $path = $request->file('filename')->storeAs(
                'notes/'.$request->company_id,
                str_replace(' ', '-',$request->file('filename')->getClientOriginalName()),
                'public'
            );
            $note->update(['filename' => $path]);
        }
        return back()->with('message', 'Nota Aggiunta');
    }

    public function edit(Note $note)
    {
        return view('areaseb::core.notes.edit', compact('note'));
    }

    public function update(Request $request, Note $note)
    {
        $note->update($request->except('_token'));
        if($request->filename)
        {
            \Storage::disk('public')->delete($note->filename);

            $path = $request->file('filename')->storeAs(
                'notes/'.$request->company_id,
                str_replace(' ', '-',$request->file('filename')->getClientOriginalName()),
                'public'
            );
            $note->update(['filename' => $path]);
        }

        return back()->with('message', 'Nota Aggiornata');
    }

    public function destroy(Note $note)
    {
        if($note->filename)
        {
            \Storage::disk('public')->delete($note->filename);
        }
        $note->delete();
        return back()->with('message', 'Nota Eliminata');
    }

    public function destroyAjax(Note $note)
    {
        $note->delete();
        return 'done';
    }

}
