<?php
namespace App\Mail;
use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferModificationRequestedMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Offer $offer, public string $notes) {}
    public function build()
    {
        return $this->subject("Customer Requested Changes — Offer #{$this->offer->offer_number}")
            ->view('emails.offer-modification-requested')->with(['offer' => $this->offer, 'notes' => $this->notes]);
    }
}
