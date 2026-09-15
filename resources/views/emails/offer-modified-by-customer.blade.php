{{-- resources/views/emails/offer-modified-by-customer.blade.php --}}
@include('emails.partials.header')
<p style="padding:0 40px;"><strong>{{ $offer->customer->name }}</strong> has modified offer <strong>#{{ $offer->offer_number }}</strong> (now version {{ $offer->version }}).</p>
<p style="padding:0 40px;">New total: <strong>₹{{ number_format((float) $offer->price, 2) }}</strong></p>
<p style="padding:0 40px 20px;">Visit Manage Offers to review the changes and accept, or modify it further.</p>
@include('emails.partials.footer')
