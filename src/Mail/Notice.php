<?php

namespace Areaseb\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Areaseb\Core\Models\Invoice;

class Notice extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $invoice;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, Invoice $invoice)
    {
        $this->file = storage_path('app/public/fe/pdf/inviate/'.$name);
        $this->name = $name;
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->invoice->company->invoice_email)
                    ->subject('Sollecito di pagamento '.config('app.name'))
                    ->markdown('areaseb::emails.invoices.notice')
                    ->attach($this->file, [
                        'as' => $this->name,
                        'mime' => 'application/pdf',
                    ]);

    }
}
