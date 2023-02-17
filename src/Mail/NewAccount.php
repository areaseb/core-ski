<?php

namespace Areaseb\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\User;

class NewAccount extends Mailable
{
    use Queueable, SerializesModels;

    public $recipient;
    public $pw;
    public $from;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $recipient, $pw, $from)
    {
        $this->recipient = $recipient;
        $this->pw = $pw;
        $this->from = $from;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //dd($this->from);
        return $this->subject('Nuovo account '.config('app.name'))
                ->from($this->from)
                ->markdown('areaseb::emails.users.new-account');
    }
}
