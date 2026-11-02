<?php
// app/Mail/OfferModifiedByCustomerMail.php
namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferModifiedByCustomerMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Offer $offer) {}
    public function build()
    {
        return $this->subject("Customer Modified Offer #{$this->offer->offer_number}")
            ->view('emails.offer-modified-by-customer')->with(['offer' => $this->offer]);
    }
}
