<?php

namespace Modules\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    public function __construct(User $user, string $password)
    {
        $this->user = $user;
         $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Welcome to OOHApp - Let\'s Get Your Media Booked')
                    ->view('emails.vendor-welcome');
    }
}
