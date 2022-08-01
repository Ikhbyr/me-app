<?php

namespace App\Jobs;

use App\Services\MailService;

class InquiryEmailNotificationJob extends Job
{
    protected $email;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email)
    {
        //
        $this->email =$email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (isset($this->email['body'] )) {
            MailService::send($this->email["to"], $this->email["subject"], $this->email["body"]);
        } else {
            MailService::sendMail($this->email["to"], $this->email["template"], $this->email["data"], $this->email["subject"]);
        }
    }
}
