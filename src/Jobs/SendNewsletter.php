<?php

namespace Areaseb\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Areaseb\Core\Models\{Setting, Newsletter, Report};
use Areaseb\Core\Mail\OfficialNewsletter;

class SendNewsletter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 7200;

    protected $contacts;
    protected $newsletter;
    protected $configuration;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($contacts, Newsletter $newsletter)
    {
        $this->contacts = $contacts;
        $this->newsletter = $newsletter;
        $this->configuration = Setting::smtp( $newsletter->smtp_id );
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->newsletter->owner->hasRole(['testimonial', 'agent']))
        {
            if($this->newsletter->owner->hasRole('testimonial'))
            {
                $testimonial = $this->newsletter->owner->testimonial;
            }
            else
            {
                $testimonial = $this->newsletter->owner->agent;
            }

            $mailer_config = [
                "MAIL_DRIVER" => 'smtp',
                "MAIL_HOST" => $testimonial->smtp_server,
                "MAIL_PORT" => $testimonial->smtp_port,
                "MAIL_USERNAME" => $testimonial->smtp_user,
                "MAIL_PASSWORD" => $testimonial->smtp_pw,
                "MAIL_ENCRYPTION" => $testimonial->smtp_encryption,
                "MAIL_FROM_ADDRESS" => $testimonial->smtp_send_as_email,
                "MAIL_FROM_NAME" => $testimonial->smtp_send_as_name
            ];
            $mailer = app()->makeWith('custom.mailer', $mailer_config);
        }
        else
        {
            $mailer = app()->makeWith('custom.mailer', $this->configuration);
        }


        foreach ($this->contacts as $contact)
        {
            if($contact->subscribed)
            {

                if( !Report::where('newsletter_id', $this->newsletter->id)->where('contact_id', $contact->id)->exists() )
                {
                    $report = Report::create([
                        'newsletter_id' => $this->newsletter->id,
                        'contact_id' => $contact->id,
                        'identifier' => str_random(16)
                    ]);

                    try
                    {
                        $mailer->to(trim($contact->email))->send( new OfficialNewsletter( $this->newsletter->oggetto, $this->newsletter->addTrackingAndPersonalize($report->identifier, $contact)) );
                    }
                    catch(\Exception $e)
                    {
                        $report->update([
                            'delivered' => 0,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                elseif (Report::where('newsletter_id', $this->newsletter->id)->where('contact_id', $contact->id)->where('delivered', 0)->exists())
                {
                    $report = Report::where('newsletter_id', $this->newsletter->id)->where('contact_id', $contact->id)->where('delivered', 0)->first();

                    try
                    {
                        $mailer->to(trim($contact->email))->send( new OfficialNewsletter( $this->newsletter->oggetto, $this->newsletter->addTrackingAndPersonalize($report->identifier, $contact)) );
                        $report->update(['delivered' => 1]);
                    }
                    catch(\Exception $e)
                    {
                        $report->update([
                            'delivered' => 0,
                            'error' => $e->getMessage()
                        ]);
                    }

                }
            }
        }

    }
}
