<?php
// app/Notifications/Offers/OfferModifiedByCustomerNotification.php

namespace App\Notifications\Offers;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfferModifiedByCustomerNotification extends Notification
{
    use Queueable;

    protected Offer $offer;

    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
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
            'version'      => $this->offer->version,
            'amount'       => (float) $this->offer->price,
            'status'       => 'modified',
            'message'      => "{$this->offer->customer->name} modified offer #{$this->offer->offer_number} (version {$this->offer->version}). Review and respond.",
            'action_url'   => route('vendor.offers.show', $this->offer->id),
            'role'         => 'vendor',
        ];
    }
}