<?php

namespace Areaseb\Core\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\User;
use Areaseb\Core\Models\{Setting, Newsletter};

class NewsletterSent extends Mailable
{
    use SerializesModels;

    public $newsletter;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $to = Setting::Newsletter()->default_test_email;
        if(is_null($to))
        {
            $to = User::find(1);
        }


        return $this->to($to)
                    ->subject("Invio newsletter terminato")
                    ->markdown('areaseb::emails.contacts.newsletters.newsletter-sent');
    }
}
