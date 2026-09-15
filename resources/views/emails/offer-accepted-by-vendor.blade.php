{{-- resources/views/emails/offer-accepted-by-vendor.blade.php --}}
@include('emails.partials.header')
<p style="padding:0 40px;">Good news — <strong>{{ $offer->vendor->name }}</strong> accepted your modified offer <strong>#{{ $offer->offer_number }}</strong>.</p>
<p style="padding:0 40px;">Total: <strong>₹{{ number_format((float) $offer->price, 2) }}</strong></p>
<p style="padding:0 40px 20px;">Your vendor will follow up with next steps.</p>
@include('emails.partials.footer')
