<?php
// app/Mail/OfferRejectedByVendorMail.php
namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferRejectedByVendorMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Offer $offer, public ?string $reason = null) {}
    public function build()
    {
        return $this->subject("Offer #{$this->offer->offer_number} Withdrawn by Vendor")
            ->view('emails.offer-rejected-by-vendor')->with(['offer' => $this->offer, 'reason' => $this->reason]);
    }
}
