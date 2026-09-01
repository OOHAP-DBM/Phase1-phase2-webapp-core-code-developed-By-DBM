<?php

namespace Modules\Mail;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Modules\Enquiries\Models\Enquiry;

class CustomerEnquiryConfirmationMail extends Mailable
{
    public $enquiry;
    public $customer;

    public function __construct(Enquiry $enquiry, User $customer)
    {
        $this->enquiry = $enquiry;
        $this->customer = $customer;
    }

    public function build()
    {
        $template = EmailTemplate::where(
            'key',
            'customer_enquiry_confirmation'
        )
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return $this
                ->subject('Enquiry Confirmation - Your Campaign Details')
                ->view('emails.customer-enquiry-confirmation');
        }

        $variables = [
            '{{customer_name}}'  => $this->customer->name ?? '',
            '{{customer_email}}' => $this->customer->email ?? '',
            '{{enquiry_number}}' => $this->enquiry->enquiry_number
                ?? $this->enquiry->id
                ?? '',
            '{{app_name}}'       => config('app.name', 'OOHAPP'),
            '{{login_url}}'      => url('/login'),
            '{{support_email}}'  => config('mail.from.address', ''),
        ];

        $subject = str_replace(
            array_keys($variables),
            array_values($variables),
            $template->subject
        );

        $body = str_replace(
            array_keys($variables),
            array_values($variables),
            $template->body
        );

        return $this
            ->subject($subject)
            ->html($body);
    }
}
