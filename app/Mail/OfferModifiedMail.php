<?php
// app/Mail/OfferModifiedMail.php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferModifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Offer $offer) {}

    public function build()
    {
        $this->offer->loadMissing(['currentVersion.items.hoarding.doohScreen', 'customer', 'vendor']);
        return $this->subject("Offer #{$this->offer->offer_number} Updated — OOHAPP")
            ->view('emails.offer-modified')
            ->with(['offer' => $this->offer]);
    }
}
