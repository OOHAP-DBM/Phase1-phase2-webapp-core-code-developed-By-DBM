{{-- resources/views/emails/offer-rejected-by-vendor.blade.php --}}
@include('emails.partials.header')
<p style="padding:0 40px;"><strong>{{ $offer->vendor->name }}</strong> has withdrawn offer <strong>#{{ $offer->offer_number }}</strong>.</p>
@if($reason)<p style="padding:0 40px;"><strong>Reason:</strong> {{ $reason }}</p>@endif
@include('emails.partials.footer')
