<?php
// app/Notifications/Offers/OfferAcceptedByVendorNotification.php

namespace App\Notifications\Offers;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfferAcceptedByVendorNotification extends Notification
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
            'amount'       => (float) $this->offer->price,
            'status'       => 'accepted',
            'message'      => "{$this->offer->vendor->name} accepted your changes to offer #{$this->offer->offer_number}!",
            'action_url'   => route('customer.offers.show', $this->offer->id),
            'role'         => 'customer',
        ];
    }
}