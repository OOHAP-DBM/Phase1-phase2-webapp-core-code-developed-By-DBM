<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Notifications\Notification;

class CustomerEnquirySubmittedNotification extends Notification
{
    public function __construct(private readonly int $enquiryId)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'enquiry_submitted',
            'title' => 'Enquiry Submitted',
            'message' => 'Your enquiry has been submitted successfully. We will notify you when there is an update on your enquiry.',
            'enquiry_id' => $this->enquiryId,
            'user_id' => $notifiable->id,
            'source' => 'mobile_app',
        ];
    }
}
