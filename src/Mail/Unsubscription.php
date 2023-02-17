<?php

namespace Areaseb\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Areaseb\Core\Models\{Contact, Setting};

class Unsubscription extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Nuova disiscrizione')
            ->markdown('areaseb::emails.contacts.unsubscription')
            ->text('areaseb::emails.contacts.unsubscription_plain');
    }
}
