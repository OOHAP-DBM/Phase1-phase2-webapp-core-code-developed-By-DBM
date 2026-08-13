<?php
// app/Notifications/Offers/OfferAcceptedByCustomerNotification.php

namespace App\Notifications\Offers;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfferAcceptedByCustomerNotification extends Notification
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
            'message'      => "{$this->offer->customer->name} accepted offer #{$this->offer->offer_number}! Total: ₹" . number_format((float) $this->offer->price, 2),
            'action_url'   => route('vendor.offers.show', $this->offer->id),
            'role'         => 'vendor',
        ];
    }
}