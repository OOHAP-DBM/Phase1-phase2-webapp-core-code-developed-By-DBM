{{-- emails/offer-modification-requested.blade.php --}}
@include('emails.partials.header')
<p style="padding:0 40px;"><strong>{{ $offer->customer->name }}</strong> requested changes to offer <strong>#{{ $offer->offer_number }}</strong>:</p>
<p style="padding:0 40px; background:#fef9c3; padding:12px 40px;">{{ $notes }}</p>
<p style="padding:0 40px;">Visit Manage Offers → Modify Offer to update and resend.</p>
@include('emails.partials.footer')
