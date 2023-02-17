<?php

namespace Areaseb\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Areaseb\Core\Models\Company;

class SendInvoice extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $company;
    public $content;
    public $title;
    public $subject;
    public $setting;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, Company $company, $content, $title, $subject, $setting)
    {
        $this->file = storage_path('app/public/fe/pdf/inviate/'.$name);
        $this->name = $name;
        $this->email = $company->invoice_email;
        $this->content = $content;
        $this->company = $company;
        $this->setting = $setting;
        $this->title = $title;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->email)
                    ->subject($this->subject)
                    ->view('areaseb::emails.invoices.content')
                    ->text('areaseb::emails.invoices.content_plain')
                    ->attach($this->file, [
                        'as' => $this->name,
                        'mime' => 'application/pdf',
                    ]);
    }
}
