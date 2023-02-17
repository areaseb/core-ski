<?php

namespace Areaseb\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Areaseb\Core\Models\{Newsletter, Notification, Setting};
use Areaseb\Core\Mail\NewsletterSent;
use App\User;

class SendNewsletterCompleted implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $utente;
    public $name;
    public $newsletter;
    protected $id;
    protected $configuration;

    /**
     * Create a new job instance.
     *
     * @return void
     */
     public function __construct($name, Newsletter $newsletter, User $utente)
     {
         $this->utente = $utente;
         $this->name = $name;
         $this->id = $newsletter->id;
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
        Notification::create([
            'user_id' => $this->utente->id,
            'name' => $this->name,
            'notificationable_id' => $this->id,
            'notificationable_type' => 'Areaseb\Core\Models\Newsletter'
        ]);

        $newsletter = Newsletter::find($this->id);
        $newsletter->update(['inviata' => 1]);

        $mailer = app()->makeWith('custom.mailer', $this->configuration);
        $mailer->send( new NewsletterSent($newsletter));
    }
}
