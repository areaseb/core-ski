<?php

namespace Areaseb\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Areaseb\Core\Mail\TestNewsletter;
use Areaseb\Core\Models\Setting;


class SendTestNewsletter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $args;
    protected $configuration;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($args)
    {
        $this->args = $args;
        $this->configuration = Setting::smtp( $args['newsletter']->smtp_id );
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mailer = app()->makeWith('custom.mailer', $this->configuration);
        $mailer->to($this->args['recipient'])->send(new TestNewsletter($this->args['subject'],$this->args['content']));
    }
}
