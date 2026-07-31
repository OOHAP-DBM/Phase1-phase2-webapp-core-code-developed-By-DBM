<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Offer $offer) {}

    public function build()
{
    return $this->subject("New Offer #{$this->offer->offer_number} — OOHAPP")
        ->view('emails.offer-sent')
        ->with(['offer' => $this->offer]);
}
}
