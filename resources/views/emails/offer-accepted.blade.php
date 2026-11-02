{{-- emails/offer-accepted.blade.php --}}
@include('emails.partials.header')
<p style="padding:0 40px;">Great news — <strong>{{ $offer->customer->name }}</strong> has accepted offer <strong>#{{ $offer->offer_number }}</strong> (₹{{ number_format((float) $offer->price, 2) }}).</p>
<p style="padding:0 40px;">You can now proceed to create a quotation for this booking.</p>
@include('emails.partials.footer')
