<?php
// app/Notifications/Offers/OfferModifiedByVendorNotification.php

namespace App\Notifications\Offers;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfferModifiedByVendorNotification extends Notification
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
            'message'      => "Offer #{$this->offer->offer_number} was updated by the vendor (version {$this->offer->version}). Total: ₹" . number_format((float) $this->offer->price, 2),
            'action_url'   => route('customer.offers.show', $this->offer->id),
            'role'         => 'customer',
        ];
    }
}