{{-- resources/views/emails/offer-sent.blade.php --}}
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Offer Received - OOHAPP</title></head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

@include('emails.partials.header')

<tr>
<td align="center" style="padding:20px 40px 0 40px;">
    <h2 style="margin:0; color:#16a34a; font-weight:600;">You've Received a New Offer 🎯</h2>
    <p style="margin-top:8px; color:#666; font-size:14px;">
        Hi {{ $offer->customer->name ?? 'Customer' }}, {{ $offer->vendor->name ?? 'your vendor' }} has sent you an offer for your enquiry.
    </p>
</td>
</tr>

<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>Please review the details below and respond at your convenience.</p>
</td>
</tr>

<!-- OFFER SUMMARY -->
<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
<tr style="background:#f3f4f6;">
    <th align="left" style="padding:10px; font-size:13px;">Information</th>
    <th align="left" style="padding:10px; font-size:13px;">Details</th>
</tr>
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Offer ID</strong></td>
    <td style="padding:10px; font-size:13px;">#{{ $offer->offer_number }}</td>
</tr>
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Status</strong></td>
    <td style="padding:10px; font-size:13px;">
        <span style="background:#dbeafe; color:#1e40af; padding:4px 8px; font-weight:bold;">
            {{ ucfirst($offer->status) }}
        </span>
    </td>
</tr>
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Number of Hoardings</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $offer->currentVersion?->items?->count() ?? 0 }}</td>
</tr>
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Total Offer Price</strong></td>
    <td style="padding:10px; font-size:13px;"><strong>₹{{ number_format((float) $offer->price, 2) }}</strong></td>
</tr>
@if($offer->valid_until)
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Valid Until</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $offer->valid_until->format('d M Y') }}</td>
</tr>
@endif
</table>
</td>
</tr>

<!-- VENDOR DETAILS -->
@if($offer->vendor)
<tr>
<td style="padding:20px 40px 0 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
<tr style="background:#f3f4f6;"><th align="left" style="padding:10px; font-size:13px;" colspan="2">Vendor Details</th></tr>
<tr style="background:#ecfdf5;"><td colspan="2" style="padding:10px; font-size:14px; color:#065f46;"><strong>{{ $offer->vendor->name ?? '-' }}</strong></td></tr>
<tr>
    <td style="padding:8px 10px; font-size:13px; width:35%;"><strong>Business Name</strong></td>
    <td style="padding:8px 10px; font-size:13px;">{{ $offer->vendor->vendorProfile->company_name ?? $offer->vendor->company_name ?? '-' }}</td>
</tr>
<tr>
    <td style="padding:8px 10px; font-size:13px;"><strong>Mobile</strong></td>
    <td style="padding:8px 10px; font-size:13px;">{{ $offer->vendor->phone ?? '-' }}</td>
</tr>
<tr>
    <td style="padding:8px 10px; font-size:13px;"><strong>Email</strong></td>
    <td style="padding:8px 10px; font-size:13px;">
        <a href="mailto:{{ $offer->vendor->email }}" style="color:#16a34a; text-decoration:none;">{{ $offer->vendor->email ?? '-' }}</a>
    </td>
</tr>
</table>
</td>
</tr>
@endif

<!-- HOARDING LIST -->
<tr>
<td style="padding:20px 40px 0 40px;">
    <p style="font-size:14px; color:#444;"><strong>Offered Hoardings</strong></p>
</td>
</tr>

@foreach($offer->currentVersion?->items ?? [] as $item)
@php $h = $item->hoarding; @endphp
<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb; margin-bottom:12px;">

<tr style="background:#ecfdf5;">
<td style="padding:12px; font-size:14px; color:#065f46;">
    <strong>{{ $h->title ?? 'Hoarding #' . $item->hoarding_id }}</strong>
</td>
</tr>

<tr>
<td style="padding:10px; font-size:13px;">
    📍 {{ $h->display_location ?? $h->address ?? 'Location not specified' }}
</td>
</tr>

<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Type:</strong> {{ strtoupper($item->hoarding_type) }}
</td>
</tr>

<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Booking Period:</strong> {{ optional($item->start_date)->format('d M Y') }} to {{ optional($item->end_date)->format('d M Y') }}
    ({{ $item->duration_months }} month{{ $item->duration_months !== 1 ? 's' : '' }})
</td>
</tr>

@if($item->hoarding_type === 'dooh' && $h?->doohScreen)
<tr>
<td style="padding:10px; font-size:13px; background:#f0fdf4;">
    <strong>DOOH Specifications:</strong><br>
    Slots/Day: {{ $h->doohScreen->total_slots_per_day ?? 300 }}
</td>
</tr>
@endif

<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Rental:</strong> ₹{{ number_format((float) $item->unit_price / max(1, $item->duration_months), 2) }}/month
</td>
</tr>

@if((float) $item->discount_amount > 0)
<tr>
<td style="padding:10px; font-size:13px; color:#16a34a;">
    <strong>Discount:</strong> -₹{{ number_format((float) $item->discount_amount, 2) }}
</td>
</tr>
@endif

<tr>
<td style="padding:10px; font-size:14px; font-weight:bold; background:#f9fafb;">
    Final Price: ₹{{ number_format((float) $item->final_price, 2) }}
</td>
</tr>

</table>
</td>
</tr>
@endforeach

<!-- GRAND TOTAL -->
<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" style="border:2px solid #16a34a;">
<tr style="background:#ecfdf5;">
    <td style="padding:14px; font-size:16px; font-weight:bold; color:#065f46;">Total Offer Amount</td>
    <td style="padding:14px; font-size:18px; font-weight:bold; color:#16a34a; text-align:right;">₹{{ number_format((float) $offer->price, 2) }}</td>
</tr>
</table>
</td>
</tr>

@if($offer->description)
<tr>
<td style="padding:15px 40px; font-size:13px; color:#444;">
    <strong>Notes from vendor:</strong>
    <p>{{ $offer->description }}</p>
</td>
</tr>
@endif

<!-- CTA -->
<tr>
<td align="center" style="padding:20px 40px;">
    <a href="{{ route('customer.offers.show', $offer->id) }}"
       style="background:#16a34a; color:#ffffff; padding:12px 26px; font-size:14px; text-decoration:none; border-radius:6px; display:inline-block;">
        View &amp; Respond to Offer
    </a>
</td>
</tr>

<tr>
<td style="padding:0 20px 20px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>If you have any questions, contact us at <strong>support@oohapp.com</strong></p>
    <p>Thank you for choosing OOHAPP.<br><strong>Team OOHAPP</strong></p>
</td>
</tr>

<tr>
<td style="padding:10px 40px 0 40px;">
    <div style="margin-top:25px; padding-top:12px; border-top:1px dashed #ddd; font-size:9px; color:#777; line-height:1.5;">
        <strong style="color:#555;">Disclaimer:</strong>
        OOHAPP connects advertisers with media owners. Pricing, availability and execution
        are managed directly by the vendor. OOHAPP acts only as a facilitating platform.
    </div>
</td>
</tr>

@include('emails.partials.footer')
</table>
</td></tr>
</table>
</body>
</html>
