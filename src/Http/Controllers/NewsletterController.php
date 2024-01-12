<?php

namespace Areaseb\Core\Http\Controllers;

use Areaseb\Core\Models\{Newsletter, NewsletterList, Contact, Setting, Template};
use Illuminate\Http\Request;
use Areaseb\Core\Jobs\{SendNewsletter, SendTestNewsletter, SendNewsletterCompleted};
use Carbon\Carbon;
use \Illuminate\Support\Facades\Artisan;
use Areaseb\Core\Mail\TestNewsletter;
use Illuminate\Support\Facades\Mail;


class NewsletterController extends Controller
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
            $newsletters = Newsletter::query()->where('owner_id', auth()->user()->id)->orderBy('created_at', 'DESC')->get();
        }
        else
        {
            $newsletters = Newsletter::latest()->get();
        }

        return view('areaseb::core.newsletters.index', compact('newsletters'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(auth()->user()->hasRole(['testimonial', 'agent']))
        {
            $lists = NewsletterList::where('owner_id', auth()->user()->id)->pluck('nome', 'id')->toArray();
            $templates = Template::where('owner_id', auth()->user()->id)->pluck('nome', 'id')->toArray();
            $smtps = ['0' => 'Custom ' . auth()->user()->contact->fullname];
        }
        else
        {
            $lists = [1 => 'Tutti i Contatti']+NewsletterList::pluck('nome', 'id')->toArray();
            $templates = Template::pluck('nome', 'id')->toArray();
            $smtps = \Areaseb\Core\Models\Setting::smtpPluck();
        }

        return view('areaseb::core.newsletters.create', compact('lists', 'templates', 'smtps'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $default = Template::Default();
        $this->validate(request(),[
            'descrizione' => 'required_if:template_id,==,'.$default->id
        ]);

        if($request->template_id == $default->id)
        {
            $newsletter = new Newsletter;
                $newsletter->owner_id = auth()->user()->id;
                $newsletter->nome = $request->nome;
                $newsletter->oggetto = $request->oggetto;
                $newsletter->descrizione = $request->descrizione;
                $newsletter->template_id = $request->template_id;
                $newsletter->smtp_id = $request->smtp_id;
            $newsletter->save();

            Template::makeDefault();
        }
        else
        {
            $newsletter = new Newsletter;
            $newsletter->owner_id = auth()->user()->id;
                $newsletter->nome = $request->nome;
                $newsletter->oggetto = $request->oggetto;
                $newsletter->descrizione = $request->descrizione;
                $newsletter->template_id = $request->template_id;
                $newsletter->smtp_id = $request->smtp_id;
            $newsletter->save();
        }
        $newsletter->lists()->attach($request->list_id);

        return redirect('newsletters')->with('message', 'Newsletter Creata');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function edit(Newsletter $newsletter)
    {
        if(auth()->user()->hasRole(['testimonial', 'agent']))
        {
            $lists = NewsletterList::where('owner_id', auth()->user()->id)->pluck('nome', 'id')->toArray();
            $templates = Template::where('owner_id', auth()->user()->id)->pluck('nome', 'id')->toArray();
            $smtps = ['0' => 'Custom ' . auth()->user()->contact->fullname];
        }
        else
        {
            $lists = [1 => 'Tutti i Contatti']+NewsletterList::pluck('nome', 'id')->toArray();
            $templates = Template::pluck('nome', 'id')->toArray();
            $smtps = \Areaseb\Core\Models\Setting::smtpPluck();
        }

        return view('areaseb::core.newsletters.edit', compact('lists', 'templates', 'newsletter', 'smtps'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Newsletter $newsletter)
    {
        if($request->template_id == Template::Default()->id)
        {
            $newsletter->nome = $request->nome;
            $newsletter->oggetto = $request->oggetto;
            $newsletter->descrizione = $request->descrizione;
            $newsletter->template_id = $request->template_id;
            $newsletter->smtp_id = $request->smtp_id;
            $newsletter->save();

            Template::makeDefault();
        }
        else
        {
            $newsletter->nome = $request->nome;
            $newsletter->oggetto = $request->oggetto;
            $newsletter->descrizione = $request->descrizione;
            $newsletter->template_id = $request->template_id;
            $newsletter->smtp_id = $request->smtp_id;
            $newsletter->save();
        }

        $newsletter->lists()->sync($request->list_id);

        return redirect(route('newsletters.index'))->with('message', 'Newsletter Aggiornata');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Classes\Contacts\Newsletter  $newsletter
     * @return \Illuminate\Http\Response
     */
    public function destroy(Newsletter $newsletter)
    {
        $newsletter->lists()->detach();

        if($newsletter->notifications()->exists())
        {
            $newsletter->notifications()->delete();
        }

        $newsletter->delete();
        return 'done';
    }

//newsletter/{id}/send-test - GET
    public function test(Newsletter $newsletter)
    {
        return view('areaseb::core.newsletters.send-test', compact('newsletter'));
    }

//newsletter/{id}/send-test - POST
    public function sendTest(Newsletter $newsletter)
    {
        foreach(Setting::defaultTestEmail(request('email')) as $recipient)
        {
            $content = is_null($newsletter->template->contenuto) ? $newsletter->template->contenuto_html : $newsletter->template->contenuto;
            //$mailer = app()->makeWith('custom.mailer', Setting::smtp( $newsletter->smtp_id ));
            $settingSMTP = Setting::smtp( $newsletter->smtp_id );
            try
            {
                $dsn = 'smtp://'.$settingSMTP['MAIL_USERNAME'].':'.$settingSMTP['MAIL_PASSWORD'].'@'.$settingSMTP['MAIL_HOST'].':'.$settingSMTP['MAIL_PORT'];
                $data = array(
                    'from' => $settingSMTP['MAIL_FROM_ADDRESS'],
                    'content' => $content,
                    'email' => request('email') 
                );

                //DEFINISCO IL MAILER IN BASE ALLA CONFIGURAZIONE SMTP SCELTA
                Mail::mailer($dsn);
                Mail::send('areaseb::emails.contacts.newsletters.test-newsletter',$data, function ($message) use ($data)
                {
                    $message->to($data['email'])
                        ->subject('Newsletter da'.config('app.name'));
                    $message->from($data['from']);
                });

                //$mailer->to($recipient)->send(new TestNewsletter($newsletter->oggetto, $content));
            }
            catch(\Exception $e)
            {
                return response()->view('areaseb::components.ex', ['message' => $e->getMessage()], 200);
            }

            // $args = [
            //     'sender' => Setting::defaultSendFrom(),
            //     'recipient' => $recipient,
            //     'subject' => $newsletter->oggetto,
            //     'content' => is_null($newsletter->template->contenuto) ? $newsletter->template->contenuto_html : $newsletter->template->contenuto,
            //     'newsletter' => $newsletter
            // ];
            // dispatch( (new SendTestNewsletter($args) )->delay( Carbon::now()->addSeconds(5) ) );

        }
        return redirect(route('newsletters.index'))->with('message', 'Test email spedita');
    }

//newsletter/{id}/send - GET
    public function send(Newsletter $newsletter)
    {
        if(auth()->user()->hasRole(['testimonial', 'agent']))
        {
            $sender['MAIL_FROM_ADDRESS'] = auth()->user()->testimonial->smtp_send_as_email;
            $sender['MAIL_FROM_NAME'] = auth()->user()->testimonial->smtp_send_as_name;
        }
        else
        {
            $sender = Setting::smtp($newsletter->smtp_id);
        }

        return view('areaseb::core.newsletters.send', compact('newsletter', 'sender'));
    }

//newsletter/{id}/send - POST
    public function sendOfficial(Newsletter $newsletter)
    {
        $sender = Setting::defaultSendFrom();
        $delay = Carbon::now()->addSeconds(5);


        $newsletter->contenuto = $newsletter->template->contenuto;
        $newsletter->save();


        if($newsletter->lists()->where('list_id', 1)->exists())
        {
            Contact::subscribed()->chunk(4, function($contacts) use(&$delay, $newsletter) {
                dispatch( (new SendNewsletter($contacts, $newsletter))->delay($delay->addSeconds(60)) );
            });
        }
        else
        {
            foreach($newsletter->lists as $list)
            {
                $list->contacts()->chunk(4, function($contacts) use(&$delay, $newsletter) {
                    dispatch( (new SendNewsletter($contacts, $newsletter))->delay($delay->addSeconds(60)) );
                });
            }
        }


        dispatch( (new SendNewsletterCompleted('Campagna spedita', $newsletter, auth()->user()))->delay($delay->addSeconds(15)) );

        return back()->with('message', 'Processo iniziato. Vi notificheremo quando sarà consluso.');
    }


    public function duplicate(Request $request, Newsletter $newsletter)
    {
        $new = $newsletter->replicate()->fill([
                'inviata' => 0
            ]);
        $new->save();

        $new->lists()->attach($newsletter->lists()->pluck('id')->toArray());

        return back()->with('message', 'Newsletter duplicata');
    }


}
