<?php

namespace Modules\Mail;

use App\Models\EmailTemplate;
use Illuminate\Mail\Mailable;

class CustomerEnquiryConfirmationMail extends Mailable
{
    public $enquiry;

    public function __construct($enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function build()
    {
        $template = EmailTemplate::where('key', 'customer_enquiry_confirmation')
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return $this
                ->subject('Enquiry Confirmation - Your Campaign Details')
                ->view('emails.layout', [
                    'subject' => 'Enquiry Confirmation - Your Campaign Details',
                    'body' => '<p>Your enquiry has been successfully submitted.</p>',
                ]);
        }

        $preferredLocations = $this->enquiry->preferred_locations ?? '';

        if (is_array($preferredLocations)) {
            $preferredLocations = implode(', ', $preferredLocations);
        }

        $preferredModes = $this->enquiry->preferred_modes ?? '';

        if (is_array($preferredModes)) {
            $preferredModes = implode(', ', $preferredModes);
        }

        $variables = [
            'customer_name' => $this->enquiry->name ?? '',
            'customer_email' => $this->enquiry->email ?? '',
            'customer_phone' => $this->enquiry->phone ?? '',
            'enquiry_number' => $this->enquiry->id ?? '',
            'hoarding_type' => $this->enquiry->hoarding_type ?? '',
            'city' => $this->enquiry->location_city ?? '',
            'preferred_locations' => $preferredLocations,
            'preferred_modes' => $preferredModes,
            'remarks' => $this->enquiry->remarks ?? '',
            'preferred_start_date' => $this->enquiry->preferred_start_date ?? '',
            'app_name' => config('app.name', 'OOHAPP'),
            'login_url' => url('/customer/dashboard'),
            'support_email' => config('mail.from.address', ''),
        ];

        $subject = $template->subject;
        $body = $template->body;

        foreach ($variables as $key => $value) {
            $value = is_null($value) ? '' : (string) $value;

            $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/';

            $subject = preg_replace(
                $pattern,
                e($value),
                $subject
            );

            $body = preg_replace(
                $pattern,
                e($value),
                $body
            );
        }

        return $this
            ->subject($subject)
            ->view('emails.layout', [
                'subject' => $subject,
                'body' => $body,
            ]);
    }
}

