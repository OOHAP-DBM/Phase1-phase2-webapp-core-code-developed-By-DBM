<?php
namespace App\Mail;
use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Offer $offer) {}
    public function build()
    {
        return $this->subject("Offer #{$this->offer->offer_number} Accepted!")
            ->view('emails.offer-accepted')->with(['offer' => $this->offer]);
    }
}
