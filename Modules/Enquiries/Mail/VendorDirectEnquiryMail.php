<?php

namespace Modules\Enquiries\Mail;

use App\Models\User;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Enquiries\Models\DirectEnquiry;

class VendorDirectEnquiryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public DirectEnquiry $enquiry;
    public User $vendor;

    public function __construct(
        DirectEnquiry $enquiry,
        User $vendor
    ) {
        $this->enquiry = $enquiry;
        $this->vendor = $vendor;
    }

    public function build()
    {
        $email = app(EmailTemplateService::class)->render(
            'vendor_direct_enquiry',
            [
                'vendor_name' => $this->vendor->name,

                'client_name' => $this->enquiry->name,
                'client_phone' => $this->enquiry->formatted_phone,
                'client_email' => $this->enquiry->email,

                'city' => $this->enquiry->location_city,

                'hoarding_type' => $this->enquiry->hoarding_type,

                'preferred_locations' => $this->enquiry->preferred_locations,

                'preferred_contact_modes' => $this->enquiry->preferred_modes,

                'client_message' => $this->enquiry->remarks,

                'action_url' => url('/vendor/notifications'),
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