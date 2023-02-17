<?php

namespace Areaseb\Core\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfficialNewsletter extends Mailable
{
    use SerializesModels;

    public $content;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $content)
    {
        $this->subject = $subject;
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
            ->view('areaseb::emails.contacts.newsletters.newsletter')
            ->text('areaseb::emails.contacts.newsletters.newsletter_plain');
    }
}
