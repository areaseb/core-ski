<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Areaseb\Core\Models\{NewsletterList, Setting, Template};

class TemplateController extends Controller
{
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
            $templates = Template::where('owner_id', auth()->user()->id)->orderBy('created_at', 'DESC')->get();
        }
        else
        {
            $templates = Template::latest()->get();
        }


        return view('areaseb::core.templates.index', compact('templates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $template = new Template;
            $template->nome = $request->nome;
            $template->contenuto_html = $request->contenuto_html;
            $template->contenuto = $request->contenuto_html;
            $template->owner_id = auth()->user()->id;
        $template->save();
        return $template->id;
    }

    public function destroy(Template $template)
    {
        $template->delete();
        return 'done';
    }


//templates/{$template}
    public function show(Template $template)
    {
        if($template->nome == 'Default')
        {
            $setting = Setting::base();
            return view('areaseb::core.templates.default.content', compact('template', 'setting'));
        }
        return view('areaseb::core.templates.show', compact('template'));
    }

//templates/html/{template}
    public function html(Template $template)
    {
        return view('areaseb::core.templates.html', compact('template'));
    }


    public function update(Request $request, Template $template)
    {
        $template->contenuto_html = $request->contenuto_html;
        $template->contenuto = $request->contenuto_html;
        $template->save();
        return 'done';
    }

//templates/{template}/duplicate - POST
    public function duplicate(Template $template)
    {
        $new = $template->replicate();
        $new->nome = $new->nome . '#2';
        $new->save();
        return redirect(route('templates.index'));
    }





}
