<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;
    public string $body;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $subject,
        string $body
    ) {
        $this->emailSubject = $subject;
        $this->body = $body;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject($this->emailSubject)
            ->view('emails.dynamic');
    }
}