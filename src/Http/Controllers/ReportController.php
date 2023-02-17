<?php

namespace Areaseb\Core\Http\Controllers;

use Illuminate\Http\Request;
use Areaseb\Core\Models\{Contact, Newsletter, Report, Setting};
use Carbon\Carbon;
use Areaseb\Core\Mail\Unsubscription;

class ReportController extends Controller
{

    public function index(Newsletter $newsletter)
    {
        $stats = Report::stats($newsletter);
        $clicks = Report::clicks($newsletter);
        $reports = Report::where('newsletter_id', $newsletter->id)->get();

//dd($stats, $clicks, $reports);

        return view('areaseb::core.reports.index', compact('newsletter', 'reports', 'stats', 'clicks'));
    }

    public function show()
    {
        dd('report single user');
    }

//newsletters/{newsletter}/reports/aperte
    public function showOpen(Newsletter $newsletter)
    {
        $contactIds = Report::where('newsletter_id', $newsletter->id)->aperte()->pluck('contact_id');
        $contacts = Contact::whereIn('id', $contactIds)->get();
        return view('areaseb::core.reports.show-open', compact('contacts', 'newsletter'));
    }

//newsletters/{newsletter}/reports/errore
    public function showErrore(Newsletter $newsletter)
    {
        $contactIds = Report::where('newsletter_id', $newsletter->id)->errore()->pluck('contact_id');
        $contacts = Contact::whereIn('id', $contactIds)->get();
        return view('areaseb::core.reports.show-error', compact('contacts', 'newsletter'));
    }

//newsletters/{newsletter}/reports/unsubscribed
    public function showUnsubscribed(Newsletter $newsletter)
    {
        $contactIds = Report::where('newsletter_id', $newsletter->id)->unsubscribed()->pluck('contact_id');
        $contacts = Contact::whereIn('id', $contactIds)->get();
        return view('areaseb::core.reports.show-unsubscribed', compact('contacts', 'newsletter'));
    }

//newsletters/{newsletter}/reports/clicked
    public function showClicked(Newsletter $newsletter)
    {
        $contactIds = Report::where('newsletter_id', $newsletter->id)->whereNotNull('clicks')->pluck('contact_id');
        $contacts = Contact::whereIn('id', $contactIds)->get();
        return view('areaseb::core.reports.show-clicked', compact('contacts', 'newsletter'));
    }


//tracker{?r=}
    public function tracker()
    {
        if(request()->has('r'))
        {
            $report = Report::identify(request('r'));
            if($report)
            {
                if($report->opened == 0)
                {
                    $report->opened = 1;
                    $report->opened_at = Carbon::now();
                    $report->save();
                }
            }
        }
        return response()->file(public_path('img/image.png'));
    }


//track{?r=} - track clicks
    public function track()
    {
        if(request()->has('tracker'))
        {
            $report = Report::identify(request('tracker'));
            if($report)
            {
                $arr = [];
                $key = intval(request('link'));
                if(!is_null($report->clicks))
                {
                    $exists = 0;
                    $arr = $report->clicks;
                    foreach ($arr as $k => $value)
                    {
                        if($value['number'] === $key)
                        {
                            $exists++;
                        }
                    }
                    if($exists === 0)
                    {
                        $link = [
                            'number' => $key,
                            'url' => request('redirect')
                        ];
                        $arr[] = $link;
                        $report->clicks = $arr;
                        $report->save();
                    }
                }
                else
                {
                    $link = [
                        'number' => $key,
                        'url' => request('redirect')
                    ];
                    $arr[] = $link;
                    $report->clicks = $arr;
                    $report->save();
                }

            }
        }
        return redirect(request('redirect'));
    }



//unsubscribe{?r=}
    public function unsubscribe()
    {
        if(request()->has('r'))
        {
            $report = Report::identify(request('r'));
            if($report)
            {
                $report->contact()->update(['subscribed' => false]);
                $report->unsubscribed = true;
                $report->save();

                $to = \App\User::orderBy('id', 'asc')->first()->email;
                if(Setting::newsletter()->unsub_notification_email != "")
                {
                    $to = Setting::newsletter()->unsub_notification_email;
                }

                $mailer = app()->makeWith('custom.mailer', Setting::smtp(0));
                $mailer->to($to)->send(new Unsubscription($report->contact));

                $contact = $report->contact;

                return view('areaseb::core.newsletters.unsubscribed', compact('contact'));
            }
            return 'nothing';
        }
        return 'nothing';
    }


}
