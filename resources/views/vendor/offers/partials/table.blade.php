@forelse($offers as $index => $offer)
@php
    $hoardingCount = $offer->hoardingCount();
    $locationCount = $offer->locationCount();
    $cities = $offer->locationCities();
    $daysLeft = $offer->getDaysRemaining();
    $hasModRequest = $offer->hasPendingModificationRequest();

    $statusMap = [
        'draft'    => ['label' => 'Draft', 'class' => 'text-gray-500'],
        'sent'     => ['label' => 'Offer Sent', 'sub' => 'Waiting for Customer Response', 'class' => 'text-blue-600'],
        'accepted' => ['label' => 'Offer Accepted by Customer', 'class' => 'text-emerald-600'],
        'rejected' => ['label' => 'Offer Rejected by Customer', 'class' => 'text-red-600'],
        'expired'  => ['label' => 'Offer Expired', 'class' => 'text-orange-500'],
    ];
    $statusInfo = $statusMap[$offer->status] ?? ['label' => ucfirst($offer->status), 'class' => 'text-gray-500'];

    // $actionMap = [
    //     'sent'     => ['label' => 'Send Reminder', 'class' => 'bg-gray-700 hover:bg-gray-800'],
    //     'accepted' => ['label' => 'Create Quotation', 'class' => 'bg-emerald-600 hover:bg-emerald-700'],
    //     'rejected' => ['label' => 'Send Counter Offer', 'class' => 'bg-emerald-700 hover:bg-emerald-800'],
    //     'expired'  => ['label' => 'Send Counter Offer', 'class' => 'bg-emerald-700 hover:bg-emerald-800'],
    // ];
    // $action = $actionMap[$offer->status] ?? null;
       $hasModRequest = $offer->hasPendingModificationRequest();
    $customerModified = $offer->status === 'sent' && $offer->wasLastModifiedByCustomer();

    $actionMap = [
        'sent'     => ['label' => 'Send Reminder', 'class' => 'bg-gray-700 hover:bg-gray-800'],
        'accepted' => ['label' => 'Create Quotation', 'class' => 'bg-emerald-600 hover:bg-emerald-700'],
        'rejected' => ['label' => 'Send Counter Offer', 'class' => 'bg-emerald-700 hover:bg-emerald-800'],
        'expired'  => ['label' => 'Send Counter Offer', 'class' => 'bg-emerald-700 hover:bg-emerald-800'],
    ];
    $action = $actionMap[$offer->status] ?? null;

    if ($customerModified) {
        $action = ['label' => "Accept Customer's Offer", 'class' => 'bg-amber-600 hover:bg-amber-700'];
    } elseif ($hasModRequest) {
        $action = ['label' => 'Modify Offer', 'class' => 'bg-amber-600 hover:bg-amber-700'];
    }

    // A pending customer modification request always wins over the default
    // per-status action — the vendor needs to see and act on the request
    // regardless of what status the offer is otherwise sitting in.
    if ($hasModRequest) {
        $action = ['label' => 'Modify Offer', 'class' => 'bg-amber-600 hover:bg-amber-700'];
    }
@endphp
<tr class="border-b hover:bg-gray-50">
    <td class="px-3 py-3"><input type="checkbox" class="offer-row-cb" value="{{ $offer->id }}"></td>
    <td class="px-3 py-3 text-gray-500">{{ $offers->firstItem() + $index }}</td>
    <td class="px-3 py-3">
        <a href="{{ route('vendor.offers.show', $offer->id) }}" class="font-bold text-emerald-700 hover:underline">#{{ $offer->id }}</a>
        <p class="text-[10px] text-gray-400">{{ $offer->created_at->format('d M, y') }}</p>
    </td>
    <td class="px-3 py-3">
        <p class="font-semibold text-gray-800">{{ $offer->customer->name ?? 'N/A' }}</p>
        <p class="text-[10px] text-gray-400">{{ $cities[0] ?? '' }}</p>
    </td>
    <td class="px-3 py-3">{{ $hoardingCount }}</td>
    <td class="px-3 py-3 relative group cursor-default">
        {{ $locationCount }}
        @if($locationCount > 0)
        <div class="hidden group-hover:block absolute z-20 left-0 top-full mt-1 bg-white border border-gray-200 rounded shadow-lg px-3 py-2 text-[11px] whitespace-nowrap">
            @foreach($cities as $i => $city)
                <p>{{ $i + 1 }}. {{ $city }}</p>
            @endforeach
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
        @if(!empty($statusInfo['sub']))<p class="text-[10px] text-gray-400">{{ $statusInfo['sub'] }}</p>@endif
        @if($hasModRequest)
            <p class="text-[10px] font-bold text-amber-600 mt-1">⚠ Customer requested changes</p>
        @endif
        <p class="text-[10px] text-gray-400">{{ $offer->updated_at->format('d M, y | h:i A') }}</p>
    </td>
    <td class="px-3 py-3">
        @if($action)
        {{-- <button type="button"
            class="offer-action-btn px-3 py-2 rounded text-white text-[11px] font-semibold {{ $action['class'] }}"
            data-offer-id="{{ $offer->id }}"
            data-status="{{ $offer->status }}"
            data-has-mod-request="{{ $hasModRequest ? 'true' : 'false' }}">
            {{ $action['label'] }}
        </button> --}}
        {{-- and the button itself gets one more data attribute --}}
<button type="button"
    class="offer-action-btn px-3 py-2 rounded text-white text-[11px] font-semibold {{ $action['class'] }}"
    data-offer-id="{{ $offer->id }}"
    data-status="{{ $offer->status }}"
    data-has-mod-request="{{ $hasModRequest ? 'true' : 'false' }}"
    data-customer-modified="{{ $customerModified ? 'true' : 'false' }}">
    {{ $action['label'] }}
</button>
        @endif
    </td>
    <td class="px-3 py-3 text-right">
        <button type="button" class="row-menu-trigger text-gray-400 hover:text-gray-700 px-2"
            data-offer-id="{{ $offer->id }}"
            data-view-url="{{ route('vendor.offers.show', $offer->id) }}"
            data-modify-url="{{ route('vendor.offers.create', ['offer_id' => $offer->id]) }}">⋮</button>
    </td>
</tr>
@empty
<tr><td colspan="9" class="px-4 py-10 text-center text-gray-400 text-xs">No offers found</td></tr>
@endforelse
