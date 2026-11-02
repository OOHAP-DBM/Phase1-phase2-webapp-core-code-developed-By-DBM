<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Notifications\Notification;

class VendorEnquiryReceivedNotification extends Notification
{
    public function __construct(
        private readonly int $enquiryId,
        private readonly string $customerName,
        private readonly string $hoardingType,
        private readonly string $city
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $location = $this->city !== '' ? " in {$this->city}" : '';

        return [
            'type' => 'vendor_direct_enquiry',
            'title' => 'New Hoarding Enquiry Received',
            'message' => "New {$this->hoardingType} enquiry from {$this->customerName}{$location}",
            'enquiry_id' => $this->enquiryId,
            'customer_name' => $this->customerName,
            'hoarding_type' => $this->hoardingType,
            'city' => $this->city,
            'source' => 'mobile_app',
        ];
    }
}
