<?php
// app/Notifications/Offers/OfferRejectedByCustomerNotification.php

namespace App\Notifications\Offers;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfferRejectedByCustomerNotification extends Notification
{
    use Queueable;

    protected Offer $offer;
    protected ?string $reason;

    public function __construct(Offer $offer, ?string $reason = null)
    {
        $this->offer = $offer;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'offer_id'     => $this->offer->id,
            'offer_number' => $this->offer->offer_number,
            'status'       => 'rejected',
            'message'      => "{$this->offer->customer->name} rejected offer #{$this->offer->offer_number}." . ($this->reason ? " Reason: {$this->reason}" : ''),
            'action_url'   => route('vendor.offers.show', $this->offer->id),
            'role'         => 'vendor',
        ];
    }
}