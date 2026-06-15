<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Utility\SendSMSUtility;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class SendSmsJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    protected $to;
    protected $from;
    protected $text;
    protected $template_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $from, $text, $template_id = null)
    {
        $this->onQueue('sms');
        $this->afterCommit = true;
        
        $this->to = $to;
        $this->from = $from;
        $this->text = $text;
        $this->template_id = $template_id;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        // Limit to 100 SMS per minute
        return [new RateLimited('sms_sender')];
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        // Deduplicate exact same message to same number
        return hash_hmac('sha256', $this->to . '_' . $this->text, config('app.key'));
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['sms', 'to_hash:'.hash_hmac('sha256', $this->to, config('app.key'))];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            SendSMSUtility::sendSMS($this->to, $this->from, $this->text, $this->template_id);
        } catch (\Exception $e) {
            \Log::error("SMS Sending Failed for {$this->to}: " . $e->getMessage());
            throw $e;
        }
    }
}
