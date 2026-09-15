<?php
// app/Notifications/Offers/OfferCreatedNotification.php

namespace App\Notifications\Offers;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OfferCreatedNotification extends Notification
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
            'item_count'   => $this->offer->currentVersion?->items?->count() ?? 0,
            'amount'       => (float) $this->offer->price,
            'status'       => 'sent',
            'message'      => "You've received offer #{$this->offer->offer_number} for " . ($this->offer->currentVersion?->items?->count() ?? 0) . ' hoarding(s).',
            'action_url'   => route('customer.offers.show', $this->offer->id),
            'role'         => 'customer',
        ];
    }
}