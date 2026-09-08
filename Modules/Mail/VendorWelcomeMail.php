<?php

namespace Modules\Mail;

use App\Models\User;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $password;

    public function __construct(
        User $user,
        string $password
    ) {
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        $email = app(EmailTemplateService::class)->render(
            'vendor_welcome',
            [
                'vendor_name' => $this->user->name,
                'vendor_email' => $this->user->email,
                'password' => $this->password,
                'login_url' => url('/'),
                'dashboard_url' => url('/vendor/dashboard'),
            ]
        );

        return $this
            ->subject($email['subject'])
            ->view('emails.dynamic')
            ->with([
                'emailSubject' => $email['subject'],
                'body' => $email['body'],
            ]);
    }
}
