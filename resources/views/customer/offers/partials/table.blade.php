{{-- resources/views/customer/offers/partials/table.blade.php --}}
@forelse($offers as $index => $offer)
@php
    $hoardingCount = $offer->hoardingCount();
    $locationCount = $offer->locationCount();
    $cities = $offer->locationCities();
    $daysLeft = $offer->getDaysRemaining();

    // It's the customer's turn to act only when the offer is open AND the
    // most recent version was submitted by the vendor. If the customer already
    // sent their own modification, it's now waiting on the vendor — no action
    // buttons for the customer until the vendor responds again.
    $customerTurn = $offer->canAccept() && $offer->wasLastModifiedByVendor();
    $awaitingVendor = $offer->canAccept() && $offer->wasLastModifiedByCustomer();

    $statusMap = [
        'draft'    => ['label' => 'Draft', 'class' => 'text-gray-500'],
        'sent'     => ['label' => $awaitingVendor ? 'Awaiting Vendor Response' : 'Awaiting Your Response', 'class' => 'text-blue-600'],
        'accepted' => ['label' => 'Accepted', 'class' => 'text-emerald-600'],
        'rejected' => ['label' => 'Rejected', 'class' => 'text-red-600'],
        'expired'  => ['label' => 'Expired', 'class' => 'text-orange-500'],
    ];
    $statusInfo = $statusMap[$offer->status] ?? ['label' => ucfirst($offer->status), 'class' => 'text-gray-500'];
@endphp
<tr class="border-b hover:bg-gray-50">
    <td class="px-3 py-3 text-gray-500">{{ $offers->firstItem() + $index }}</td>
    <td class="px-3 py-3">
        <a href="{{ route('customer.enquiries.show', $offer->enquiry->id) }}" class="font-bold text-gray-800 hover:underline">#{{ $offer->enquiry->id }}</a>
        <p class="text-[10px] text-gray-400">{{ $offer->enquiry->created_at->format('d M, y') }}</p>
    <td class="px-3 py-3">
        <a href="{{ route('customer.offers.show', $offer->id) }}" class="font-bold text-emerald-700 hover:underline">#{{ $offer->id }}</a>
        <p class="text-[10px] text-gray-400">{{ $offer->created_at->format('d M, y') }}</p>
        @if($offer->version > 1)
            <span class="text-[9px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-bold">v{{ $offer->version }}</span>
        @endif
    </td>
    <td class="px-3 py-3">
        <p class="font-semibold text-gray-800">{{ $offer->vendor->name ?? 'N/A' }}</p>
        <p class="text-[10px] text-gray-400">{{ $cities[0] ?? '' }}</p>
    </td>
    <td class="px-3 py-3">{{ $hoardingCount }}</td>
    <td class="px-3 py-3 relative group cursor-default">
        {{ $locationCount }}
        @if($locationCount > 0)
        <div class="hidden group-hover:block absolute z-20 left-0 top-full mt-1 bg-white border border-gray-200 rounded shadow-lg px-3 py-2 text-[11px] whitespace-nowrap">
            @foreach($cities as $i => $city)<p>{{ $i + 1 }}. {{ $city }}</p>@endforeach
        </div>
        @endif
    </td>
    <td class="px-3 py-3">
        {{ $offer->valid_until ? $offer->valid_until->format('d M, y') : '—' }}
        @if($daysLeft !== null)
            <p class="text-[10px] {{ $daysLeft <= 3 ? 'text-red-500' : 'text-gray-400' }}">{{ $daysLeft }} days left</p>
        @endif
    </td>
    <td class="px-3 py-3">
        <p class="font-semibold {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</p>
    </td>
    <td class="px-3 py-3">
        <div class="flex flex-wrap gap-1.5">
            <a href="{{ route('customer.offers.show', $offer->id) }}"
                class="px-2.5 py-1.5 rounded text-gray-600 text-[11px] font-semibold border border-gray-300 hover:bg-gray-50">
                View
            </a>

            @if($customerTurn)
                <button type="button"
                    class="customer-offer-accept-btn px-2.5 py-1.5 rounded text-white text-[11px] font-semibold bg-[#2E5B42] hover:bg-opacity-90"
                    data-offer-id="{{ $offer->id }}">
                    Accept
                </button>
                <a href="{{ route('customer.offers.modify', $offer->id) }}"
                    class="px-2.5 py-1.5 rounded text-white text-[11px] font-semibold bg-blue-600 hover:bg-blue-700">
                    Modify
                </a>
                <button type="button"
                    class="customer-offer-reject-btn px-2.5 py-1.5 rounded text-white text-[11px] font-semibold bg-red-600 hover:bg-red-700"
                    data-offer-id="{{ $offer->id }}">
                    Reject
                </button>
            @endif
        </div>
    </td>
</tr>
@empty
<tr><td colspan="7" class="px-4 py-10 text-center text-gray-400 text-xs">No offers received yet</td></tr>
@endforelse
