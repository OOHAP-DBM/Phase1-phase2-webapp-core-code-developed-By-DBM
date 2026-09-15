{{-- emails/offer-rejected.blade.php --}}
@include('emails.partials.header')
<p style="padding:0 40px;"><strong>{{ $offer->customer->name }}</strong> has rejected offer <strong>#{{ $offer->offer_number }}</strong>.</p>
@if($reason)<p style="padding:0 40px;"><strong>Reason:</strong> {{ $reason }}</p>@endif
@include('emails.partials.footer')
