<?php
// app/Mail/OfferAcceptedByVendorMail.php
namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferAcceptedByVendorMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Offer $offer) {}
    public function build()
    {
        return $this->subject("Your Offer #{$this->offer->offer_number} Was Accepted!")
            ->view('emails.offer-accepted-by-vendor')->with(['offer' => $this->offer]);
    }
}
