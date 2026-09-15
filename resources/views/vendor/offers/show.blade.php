{{-- @extends('layouts.vendor')

@section('title', 'Offer #' . $offer->offer_number)
@section('content')
<div class="px-6 py-6 bg-gray-50">

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-gray-800">Offer #{{ $offer->offer_number }}</h2>
                    <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-bold">v{{ $offer->version }}</span>
                    @php
                        $statusMap = [
                            'draft'    => ['label' => 'Draft', 'class' => 'bg-gray-100 text-gray-600'],
                            'sent'     => ['label' => 'Sent', 'class' => 'bg-blue-50 text-blue-600'],
                            'accepted' => ['label' => 'Accepted', 'class' => 'bg-emerald-50 text-emerald-600'],
                            'rejected' => ['label' => 'Rejected', 'class' => 'bg-red-50 text-red-600'],
                            'expired'  => ['label' => 'Expired', 'class' => 'bg-orange-50 text-orange-600'],
                        ];
                        $st = $statusMap[$offer->status] ?? ['label' => ucfirst($offer->status), 'class' => 'bg-gray-100 text-gray-600'];
                    @endphp
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $st['class'] }}">{{ $st['label'] }}</span>
                    @if($offer->status === 'sent' && $offer->wasLastModifiedByCustomer())
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-amber-50 text-amber-700">Awaiting your response</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-1">Created {{ $offer->created_at->format('d M Y, h:i A') }} · Customer: {{ $offer->customer->name ?? 'N/A' }}</p>
            </div>

            <div class="flex items-center gap-2">
                @if(in_array($offer->status, ['draft', 'sent']))
                @if($offer->isNegotiable())
                    <button type="button" id="vendor-reject-btn"
                        class="px-4 py-2 text-xs font-bold text-red-600 border border-red-200 bg-red-50 rounded hover:bg-red-100">
                        Reject Offer
                    </button>
                    @endif
                @endif
                <a href="{{ route('vendor.offers.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                    Back to Manage Offers
                </a>
            </div>
        </div>

        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div>
                <p class="font-semibold text-gray-700 mb-1">Customer</p>
                <p>{{ $offer->customer->name ?? 'N/A' }}</p>
                <p class="text-gray-400">{{ $offer->customer->email ?? '' }}</p>
                <p class="text-gray-400">{{ $offer->customer->phone ?? '' }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700 mb-1">Offer Summary</p>
                <p>{{ $offer->currentVersion?->items?->count() ?? 0 }} hoarding(s)</p>
                <p class="font-bold text-emerald-700 text-sm">₹{{ number_format((float) $offer->price, 2) }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700 mb-1">Validity</p>
                <p>{{ $offer->valid_until ? $offer->valid_until->format('d M Y') : 'No expiry' }}</p>
                <p class="text-gray-400">{{ $offer->getExpiryLabel() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800 text-sm">Final Offer — Current Hoarding List</h3>
            <p class="text-xs text-gray-400 mt-0.5">This is what's on the table right now, sorted by start date.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[760px] w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-500 border-b">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Hoarding</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Start Date</th>
                        <th class="px-4 py-3 font-semibold">End Date</th>
                        <th class="px-4 py-3 font-semibold">Duration</th>
                        <th class="px-4 py-3 font-semibold">Final Price</th>
                        <th class="px-4 py-3 font-semibold">Source</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($offer->currentVersion?->items?->sortBy('start_date') ?? [] as $item)
                    @php $h = $item->hoarding; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">

<div class="w-14 h-14 bg-gray-200 rounded overflow-hidden flex-shrink-0">
                                                @php

                                                    $mediaItem = null;
                                                    if (($h->hoarding_type ?? '') === 'ooh') {
                                                        $mediaItem = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $h->id)
                                                            ->orderByDesc('is_primary')
                                                            ->orderBy('sort_order')
                                                            ->first();
                                                    } elseif (($h->hoarding_type ?? '') === 'dooh') {
                                                        $screen = \Modules\DOOH\Models\DOOHScreen::where('hoarding_id', $item->id)->first();
                                                        if ($screen) {
                                                            $mediaItem = \Modules\DOOH\Models\DOOHScreenMedia::where('dooh_screen_id', $screen->id)
                                                                ->orderBy('sort_order')
                                                                ->first();
                                                        }
                                                    }
                                                @endphp
                                                @if($mediaItem)
                                                    <x-media-preview :media="$mediaItem" :alt="$item->hoarding->title ?? 'Hoarding'" />
                                                @else
                                                    <div class="w-full h-full bg-gray-300 flex items-center justify-center text-[9px] text-gray-500">No Image</div>
                                                @endif
                                            </div>
                            <p class="font-semibold text-gray-800">{{ $h->title ?? 'Hoarding #' . $item->hoarding_id }}</p>
                            <p class="text-[10px] text-gray-400">{{ $h->city ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $item->hoarding_type === 'dooh' ? 'bg-purple-50 text-purple-600' : 'bg-blue-50 text-blue-600' }}">
                                {{ strtoupper($item->hoarding_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ optional($item->start_date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ optional($item->end_date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $item->duration_months }} month{{ $item->duration_months !== 1 ? 's' : '' }}</td>
                        <td class="px-4 py-3 font-bold text-emerald-700">₹{{ number_format((float) $item->final_price, 2) }}</td>
                        <td class="px-4 py-3">
                            @if(($item->meta['source'] ?? null) === 'enquiry')
                                <span class="text-[9px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-bold">From Enquiry</span>
                            @else
                                <span class="text-[9px] bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded font-bold">Added</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400 text-xs italic">No hoardings on this offer</td></tr>
                    @endforelse
                </tbody>
                @if(($offer->currentVersion?->items?->count() ?? 0) > 0)
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-200">
                        <td colspan="5" class="px-4 py-3 font-bold text-gray-700 text-right">Total</td>
                        <td colspan="2" class="px-4 py-3 font-bold text-emerald-700">₹{{ number_format((float) $offer->price, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>


<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <div>
            <h3 class="font-bold text-gray-800 text-sm">Negotiation History</h3>
            <p class="text-xs text-gray-400 mt-0.5">Every change, and who made it.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="expandAllVersions()" class="text-[11px] font-semibold text-gray-500 hover:text-gray-700">Expand All</button>
            <span class="text-gray-300">|</span>
            <button type="button" onclick="collapseAllVersions()" class="text-[11px] font-semibold text-gray-500 hover:text-gray-700">Collapse All</button>
        </div>
    </div>

    <div class="divide-y divide-gray-100">
        @foreach($versionDiffs as $i => $diff)
        @php
            $isVendorRole = isset($isVendorView) && $isVendorView; // set this bool in each controller's show() view data — see below

            $actorLabel = match($diff['actor_type']) {
                'vendor'   => $isVendorRole ? 'You (Vendor)' : ($offer->vendor->name ?? 'Vendor'),
                'customer' => $isVendorRole ? ($offer->customer->name ?? 'Customer') : 'You',
                'admin'    => 'Admin',
                default    => 'System',
            };
            $actorClass = match($diff['actor_type']) {
                'vendor'   => $isVendorRole ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700',
                'customer' => $isVendorRole ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700',
                default    => 'bg-gray-100 text-gray-600',
            };

            $isLatest = $i === count($versionDiffs) - 1;
        @endphp
        <div class="version-block">
            <button type="button"
                class="version-toggle w-full flex items-center gap-2 px-6 py-4 text-left hover:bg-gray-50"
                data-target="version-body-{{ $diff['version']->id }}">
                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform {{ $isLatest ? '' : '-rotate-90' }}" data-chevron
                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $actorClass }}">{{ $actorLabel }}</span>
                <span class="text-xs font-bold text-gray-700">Version {{ $diff['version']->version_number }}</span>
                <span class="text-[10px] text-gray-400">{{ $diff['version']->created_at->format('d M Y, h:i A') }}</span>

                @if(!$diff['is_initial'])
                    @if($diff['has_any_change'])
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">
                            {{ count($diff['added']) }} added · {{ count($diff['removed']) }} removed · {{ count($diff['changed']) }} changed
                        </span>
                    @else
                        <span class="text-[10px] text-gray-400 italic">No hoarding changes</span>
                    @endif
                @endif

                <span class="ml-auto text-xs font-bold text-gray-700">₹{{ number_format((float) $diff['total_amount'], 2) }}</span>
            </button>

            <div id="version-body-{{ $diff['version']->id }}" class="version-body px-6 pb-5 {{ $isLatest ? '' : 'hidden' }}">
                @if($diff['is_initial'])
                    <p class="text-xs text-gray-500 mb-2">Initial offer created with {{ $diff['item_count'] }} hoarding(s):</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($diff['added'] as $item)
                            <span class="text-[10px] bg-gray-50 border border-gray-200 text-gray-700 px-2 py-1 rounded">
                                {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                                <span class="text-gray-400">({{ optional($item->start_date)->format('d M') }} – {{ optional($item->end_date)->format('d M Y') }})</span>
                            </span>
                        @endforeach
                    </div>
                @else
                    @if(!empty($diff['added']))
                    <div class="mb-3">
                        <p class="text-[10px] font-bold text-emerald-600 mb-1">＋ Added</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($diff['added'] as $item)
                                <span class="text-[10px] bg-emerald-50 border border-emerald-200 text-emerald-700 px-2 py-1 rounded">
                                    {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                                    <span class="text-emerald-500">({{ optional($item->start_date)->format('d M') }} – {{ optional($item->end_date)->format('d M Y') }} · ₹{{ number_format((float) $item->final_price, 2) }})</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(!empty($diff['removed']))
                    <div class="mb-3">
                        <p class="text-[10px] font-bold text-red-600 mb-1">－ Removed</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($diff['removed'] as $item)
                                <span class="text-[10px] bg-red-50 border border-red-200 text-red-700 px-2 py-1 rounded line-through">
                                    {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(!empty($diff['changed']))
                    <div class="mb-3">
                        <p class="text-[10px] font-bold text-amber-600 mb-1">↻ Changed</p>
                        <div class="space-y-1">
                            @foreach($diff['changed'] as $change)
                                @php $cur = $change['current']; $prev = $change['previous']; @endphp
                                <div class="text-[10px] bg-amber-50 border border-amber-200 text-amber-800 px-2 py-1.5 rounded">
                                    <span class="font-bold">{{ $cur->hoarding->title ?? 'Hoarding #' . $cur->hoarding_id }}</span> —
                                    @if(optional($prev->start_date)->format('Y-m-d') !== optional($cur->start_date)->format('Y-m-d') || optional($prev->end_date)->format('Y-m-d') !== optional($cur->end_date)->format('Y-m-d'))
                                        dates {{ optional($prev->start_date)->format('d M') }}–{{ optional($prev->end_date)->format('d M Y') }} → {{ optional($cur->start_date)->format('d M') }}–{{ optional($cur->end_date)->format('d M Y') }}
                                    @endif
                                    @if(round((float) $prev->final_price, 2) !== round((float) $cur->final_price, 2))
                                        · price ₹{{ number_format((float) $prev->final_price, 2) }} → ₹{{ number_format((float) $cur->final_price, 2) }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(!empty($diff['unchanged']))
                    <details class="mt-1">
                        <summary class="text-[10px] font-semibold text-gray-400 cursor-pointer select-none">
                            {{ count($diff['unchanged']) }} hoarding(s) carried over unchanged
                        </summary>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach($diff['unchanged'] as $item)
                                <span class="text-[10px] bg-gray-50 border border-gray-200 text-gray-500 px-2 py-1 rounded">
                                    {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                                </span>
                            @endforeach
                        </div>
                    </details>
                    @endif

                    @if(!$diff['has_any_change'] && empty($diff['unchanged']))
                        <p class="text-[10px] text-gray-400 italic">No hoardings on this version.</p>
                    @endif
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
document.querySelectorAll('.version-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const body = document.getElementById(btn.dataset.target);
        const chevron = btn.querySelector('[data-chevron]');
        body.classList.toggle('hidden');
        chevron.classList.toggle('-rotate-90');
    });
});

function expandAllVersions() {
    document.querySelectorAll('.version-body').forEach(el => el.classList.remove('hidden'));
    document.querySelectorAll('[data-chevron]').forEach(el => el.classList.remove('-rotate-90'));
}
function collapseAllVersions() {
    document.querySelectorAll('.version-body').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('[data-chevron]').forEach(el => el.classList.add('-rotate-90'));
}
</script>

</div>

<div id="vendorRejectModal" class="fixed inset-0 z-[90] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeVendorRejectModal()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-[92vw] max-w-md p-6">
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 mb-4">
            <p class="text-sm font-bold text-red-700">⚠ Are you sure you want to reject this offer?</p>
        </div>
        <p class="text-xs text-gray-500 mb-3">This ends the negotiation. The customer will be notified and won't be able to accept or modify this offer any further.</p>
        <textarea id="vendor-reject-reason" rows="3" class="w-full border rounded p-2 text-sm mb-4" placeholder="Optional: reason for rejecting"></textarea>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeVendorRejectModal()" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-md">Cancel</button>
            <button type="button" id="vendor-reject-confirm-btn" class="px-6 py-2 bg-red-600 text-white text-sm font-bold rounded-md hover:bg-red-700">Reject Offer</button>
        </div>
    </div>
</div>

<script>
window.CSRF_TOKEN = '{{ csrf_token() }}';
window.VENDOR_REJECT_URL = '{{ route('vendor.offers.vendor-reject', $offer->id) }}';
window.OFFERS_INDEX_URL = '{{ route('vendor.offers.index') }}';

document.getElementById('vendor-reject-btn')?.addEventListener('click', () => {
    document.getElementById('vendorRejectModal').classList.remove('hidden');
});
function closeVendorRejectModal() { document.getElementById('vendorRejectModal').classList.add('hidden'); }

let isRejecting = false;
document.getElementById('vendor-reject-confirm-btn')?.addEventListener('click', async () => {
    if (isRejecting) return;
    isRejecting = true;

    const btn = document.getElementById('vendor-reject-confirm-btn');
    btn.disabled = true; btn.innerText = 'Rejecting…';

    try {
        const reason = document.getElementById('vendor-reject-reason').value.trim();
        const res = await fetch(window.VENDOR_REJECT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ reason }),
        }).then(r => r.json());

        alert(res.message);
        if (res.success) window.location.href = window.OFFERS_INDEX_URL;
    } finally {
        btn.disabled = false; btn.innerText = 'Reject Offer';
        isRejecting = false;
    }
});
</script>
@endsection --}}
@extends('layouts.vendor')

@section('title', 'Offer #' . $offer->offer_number)
@section('content')
<div class="px-6 py-6 bg-gray-50">

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-gray-800">Offer #{{ $offer->offer_number }}</h2>
                    <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-bold">v{{ $offer->version }}</span>
                    @php $statusMap = [ 'draft' => [ 'label' => 'Draft', 'class' => 'bg-gray-100 text-gray-600' ], 'sent' => [ 'label' => 'Sent', 'class' => 'bg-blue-50 text-blue-600' ], 'accepted' => [ 'label' => 'Accepted', 'class' => 'bg-emerald-50 text-emerald-600' ], 'rejected' => [ 'label' => 'Rejected', 'class' => 'bg-red-50 text-red-600' ], 'expired' => [ 'label' => 'Expired', 'class' => 'bg-orange-50 text-orange-600' ], ]; $st = $statusMap[$offer->status] ?? [ 'label' => ucfirst($offer->status), 'class' => 'bg-gray-100 text-gray-600' ]; @endphp
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $st['class'] }}"> {{ $st['label'] }} </span>
                    @if($offer->status === 'sent' && $offer->wasLastModifiedByCustomer())
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-amber-50 text-amber-700">Awaiting your response</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-1">Created {{ $offer->created_at->format('d M Y, h:i A') }} · Customer: {{ $offer->customer->name ?? 'N/A' }}</p>
            </div>

@php
    /*
     * Vendor tabhi Accept/Reject karega jab:
     * 1. Offer accept/reject nahi hua ho
     * 2. Last modification customer ne ki ho
     */
    $vendorTurn = $offer->canAccept() && $offer->wasLastModifiedByCustomer();
@endphp

<div class="flex items-center gap-2">

    {{-- =====================================================
         CUSTOMER MODIFICATION
         Vendor ka turn
    ====================================================== --}}
    @if($vendorTurn)

        {{-- Accept Offer --}}
        <button
            type="button"
            id="vendor-accept-btn"
            data-offer-id="{{ $offer->id }}"
            data-url="{{ route('vendor.offers.accept-customer-modification', ['offer' => $offer->id]) }}"
            class="px-4 py-2 text-xs font-bold text-white bg-[#2E5B42] rounded hover:bg-[#123824]"
        >
            Accept Offer
        </button>


        {{-- Update Offer --}}
        <a
            href="{{ route('vendor.offers.create', ['enquiry_id' => $offer->enquiry_id]) }}"
            class="px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded hover:bg-blue-700"
        >
            Update Offer
        </a>


        {{-- Reject Offer --}}
        <button
            type="button"
            id="vendor-reject-btn"
            data-offer-id="{{ $offer->id }}"
            data-url="{{ route('vendor.vendor-reject', ['offer' => $offer->id]) }}"
            class="px-4 py-2 text-xs font-bold text-red-600 border border-red-200 bg-red-50 rounded hover:bg-red-100"
        >
            Reject Offer
        </button>


    {{-- =====================================================
         DRAFT / SENT
         Vendor can update/reject
    ====================================================== --}}
    @elseif(in_array($offer->status, ['draft', 'sent']))

        {{-- Update Offer --}}
        <a
            href="{{ route('vendor.offers.create', ['enquiry_id' => $offer->enquiry_id]) }}"
            class="px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded hover:bg-blue-700"
        >
            Update Offer
        </a>


        {{-- Reject Offer --}}
        <button
            type="button"
            id="vendor-reject-btn"
            data-offer-id="{{ $offer->id }}"
            data-url="{{ route('vendor.vendor-reject', ['offer' => $offer->id]) }}"
            class="px-4 py-2 text-xs font-bold text-red-600 border border-red-200 bg-red-50 rounded hover:bg-red-100"
        >
            Reject Offer
        </button>


    {{-- =====================================================
         ACCEPTED
    ====================================================== --}}
    @elseif($offer->status === 'accepted')

        <span
            class="px-4 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded"
        >
            ✓ Offer Accepted
        </span>


    {{-- =====================================================
         REJECTED
    ====================================================== --}}
    @elseif($offer->status === 'rejected')

        <span
            class="px-4 py-2 text-xs font-bold text-red-700 bg-red-50 border border-red-200 rounded"
        >
            Offer Rejected
        </span>


    {{-- =====================================================
         EXPIRED
    ====================================================== --}}
    @elseif($offer->status === 'expired')

        <span
            class="px-4 py-2 text-xs font-bold text-orange-700 bg-orange-50 border border-orange-200 rounded"
        >
            Offer Expired
        </span>

    @endif


    {{-- =====================================================
         BACK
    ====================================================== --}}
    <a
        href="{{ route('vendor.offers.index') }}"
        class="px-4 py-2 text-xs font-semibold text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
    >
        Back to Manage Offers
    </a>

</div>
</div>

        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div>
                <p class="font-semibold text-gray-700 mb-1">Customer</p>
                <p>{{ $offer->customer->name ?? 'N/A' }}</p>
                <p class="text-gray-400">{{ $offer->customer->email ?? '' }}</p>
                <p class="text-gray-400">{{ $offer->customer->phone ?? '' }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700 mb-1">Offer Summary</p>
                <p>{{ $offer->currentVersion?->items?->count() ?? 0 }} hoarding(s)</p>
                <p class="font-bold text-emerald-700 text-sm">₹{{ number_format((float) $offer->price, 2) }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-700 mb-1">Validity</p>
                <p>{{ $offer->valid_until ? $offer->valid_until->format('d M Y') : 'No expiry' }}</p>
                <p class="text-gray-400">{{ $offer->getExpiryLabel() }}</p>
            </div>
        </div>
    </div>

    {{-- FINAL OFFER — current version, date-wise --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <div>
            <h3 class="font-bold text-gray-800 text-sm">
    Final Offer — Current Hoarding List
</h3>

<p class="text-xs text-gray-400 mt-0.5">
    This is what's on the table right now, sorted by start date.

    @if($offer->currentVersion?->created_at)
        <span class="mx-1">·</span>
        Latest negotiation:
        <span class="font-semibold text-gray-500">
            {{ $offer->currentVersion->created_at->format('d M Y, h:i A') }}
        </span>
    @endif
</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            {{-- Search --}}
            <div class="relative">
                <input
                    type="text"
                    id="final-offer-search"
                    placeholder="Search hoarding..."
                    class="w-full sm:w-56 pl-8 pr-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                >

                <svg
                    class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                    />
                </svg>
            </div>

            {{-- Type Filter --}}
            <select
                id="final-offer-type-filter"
                class="px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            >
                <option value="all">All Types</option>
                <option value="ooh">OOH</option>
                <option value="dooh">DOOH</option>
            </select>
        </div>
    </div>
</div>
        <div class="overflow-x-auto">
            <table class="min-w-[760px] w-full text-xs text-left">
                <thead class="bg-gray-50 text-gray-500 border-b">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Hoarding</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Start Date</th>
                        <th class="px-4 py-3 font-semibold">End Date</th>
                        <th class="px-4 py-3 font-semibold">Duration</th>
                        <th class="px-4 py-3 font-semibold">Final Price</th>
                        <th class="px-4 py-3 font-semibold">Source</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($offer->currentVersion?->items?->sortBy('start_date') ?? [] as $item)
                    @php $h = $item->hoarding; @endphp
                    <tr
                        class="final-offer-row hover:bg-gray-50"
                        data-search="{{ strtolower(($h->title ?? '') . ' ' . ($h->city ?? '') . ' ' . ($item->hoarding_type ?? '')) }}"
                        data-type="{{ strtolower($item->hoarding_type ?? '') }}"
                    >
                        <td class="px-4 py-3">
                            @php
                                $hoardingImage = $h->heroImage();
                            @endphp

                            <div class="flex items-center gap-3">

                                {{-- Hoarding Image --}}
                                <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                                    @if($hoardingImage)
                                        <img
                                            src="{{ $hoardingImage }}"
                                            alt="{{ $h->title ?? 'Hoarding' }}"
                                            class="w-full h-full object-cover"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i class="fas fa-image text-lg"></i>
                                        </div>
                                    @endif
                                </div>

                                {{-- Hoarding Name + City --}}
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-800 truncate">
                                        {{ $h->title ?? 'Hoarding #' . $item->hoarding_id }}
                                    </p>

                                    <p class="text-[10px] text-gray-400">
                                        {{ $h->city ?? '' }}
                                    </p>
                                </div>

                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $item->hoarding_type === 'dooh' ? 'bg-purple-50 text-purple-600' : 'bg-blue-50 text-blue-600' }}">
                                {{ strtoupper($item->hoarding_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ optional($item->start_date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ optional($item->end_date)->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $item->duration_months }} month{{ $item->duration_months !== 1 ? 's' : '' }}</td>
                        <td class="px-4 py-3 font-bold text-emerald-700">₹{{ number_format((float) $item->final_price, 2) }}</td>
                        <td class="px-4 py-3">
                            @if(($item->meta['source'] ?? null) === 'enquiry')
                                <span class="text-[9px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-bold">From Enquiry</span>
                            @else
                                <span class="text-[9px] bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded font-bold">Added</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="final-offer-no-results" class="hidden">
    <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-xs italic">
        No hoardings match your search/filter.
    </td>
</tr>
                    @endforelse
                </tbody>
                @if(($offer->currentVersion?->items?->count() ?? 0) > 0)
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-200">
                        <td colspan="5" class="px-4 py-3 font-bold text-gray-700 text-right">Total</td>
                        <td colspan="2" class="px-4 py-3 font-bold text-emerald-700">₹{{ number_format((float) $offer->price, 2) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- VERSION HISTORY — who added/removed what, at each step --}}
    {{-- <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800 text-sm">Negotiation History</h3>
            <p class="text-xs text-gray-400 mt-0.5">Every change, and who made it.</p>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($versionDiffs as $diff)
            @php
                $actorLabel = match($diff['actor_type']) {
                    'vendor'   => 'You (Vendor)',
                    'customer' => $offer->customer->name ?? 'Customer',
                    'admin'    => 'Admin',
                    default    => 'System',
                };
                $actorClass = match($diff['actor_type']) {
                    'vendor'   => 'bg-emerald-50 text-emerald-700',
                    'customer' => 'bg-blue-50 text-blue-700',
                    default    => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <div class="px-6 py-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $actorClass }}">{{ $actorLabel }}</span>
                    <span class="text-xs font-bold text-gray-700">Version {{ $diff['version']->version_number }}</span>
                    <span class="text-[10px] text-gray-400">{{ $diff['version']->created_at->format('d M Y, h:i A') }}</span>
                    <span class="ml-auto text-xs font-bold text-gray-700">₹{{ number_format((float) $diff['total_amount'], 2) }}</span>
                </div>

                @if($diff['is_initial'])
                    <p class="text-xs text-gray-500 mb-2">Initial offer created with {{ $diff['added']->count() }} hoarding(s):</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($diff['added'] as $item)
                            <span class="text-[10px] bg-gray-50 border border-gray-200 text-gray-700 px-2 py-1 rounded">
                                {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                            </span>
                        @endforeach
                    </div>
                @else
                    @if($diff['added']->isNotEmpty())
                    <div class="mb-2">
                        <p class="text-[10px] font-bold text-emerald-600 mb-1">＋ Added</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($diff['added'] as $item)
                                <span class="text-[10px] bg-emerald-50 border border-emerald-200 text-emerald-700 px-2 py-1 rounded">
                                    {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                                    <span class="text-emerald-500">({{ optional($item->start_date)->format('d M') }} – {{ optional($item->end_date)->format('d M Y') }})</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($diff['removed']->isNotEmpty())
                    <div class="mb-2">
                        <p class="text-[10px] font-bold text-red-600 mb-1">－ Removed</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($diff['removed'] as $item)
                                <span class="text-[10px] bg-red-50 border border-red-200 text-red-700 px-2 py-1 rounded line-through">
                                    {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($diff['changed']->isNotEmpty())
                    <div class="mb-2">
                        <p class="text-[10px] font-bold text-amber-600 mb-1">↻ Changed</p>
                        <div class="space-y-1">
                            @foreach($diff['changed'] as $change)
                                @php $cur = $change['current']; $prev = $change['previous']; @endphp
                                <div class="text-[10px] bg-amber-50 border border-amber-200 text-amber-800 px-2 py-1.5 rounded">
                                    <span class="font-bold">{{ $cur->hoarding->title ?? 'Hoarding #' . $cur->hoarding_id }}</span> —
                                    @if(optional($prev->start_date)->format('Y-m-d') !== optional($cur->start_date)->format('Y-m-d') || optional($prev->end_date)->format('Y-m-d') !== optional($cur->end_date)->format('Y-m-d'))
                                        dates {{ optional($prev->start_date)->format('d M') }}–{{ optional($prev->end_date)->format('d M Y') }} → {{ optional($cur->start_date)->format('d M') }}–{{ optional($cur->end_date)->format('d M Y') }}
                                    @endif
                                    @if((float) $prev->final_price !== (float) $cur->final_price)
                                        · price ₹{{ number_format((float) $prev->final_price, 2) }} → ₹{{ number_format((float) $cur->final_price, 2) }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($diff['added']->isEmpty() && $diff['removed']->isEmpty() && $diff['changed']->isEmpty())
                        <p class="text-[10px] text-gray-400 italic">No hoarding changes in this version.</p>
                    @endif
                @endif
            </div>
            @endforeach
        </div>
    </div> --}}


    {{-- VERSION HISTORY — who added/removed/changed what, at each step --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">

        <div>
            <h3 class="font-bold text-gray-800 text-sm">
                Negotiation History
            </h3>

            <p class="text-xs text-gray-400 mt-0.5">
                Every change, and who made it.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">

            {{-- Search --}}
            <div class="relative">
                <input
                    type="text"
                    id="history-search"
                    placeholder="Search hoarding..."
                    class="w-full sm:w-52 pl-8 pr-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                >

                <svg
                    class="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                    />
                </svg>
            </div>

            {{-- Actor Filter --}}
            <select
                id="history-actor-filter"
                class="px-3 py-2 text-xs border border-gray-300 rounded-md"
            >
                <option value="all">All Actors</option>
                <option value="vendor">Vendor</option>
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
            </select>

            {{-- Version Filter --}}
            <select
                id="history-version-filter"
                class="px-3 py-2 text-xs border border-gray-300 rounded-md"
            >
                <option value="all">All Versions</option>

                @foreach($versionDiffs as $diff)
                    <option value="{{ $diff['version']->version_number }}">
                        Version {{ $diff['version']->version_number }}
                    </option>
                @endforeach
            </select>

            {{-- Expand / Collapse --}}
            <div class="flex items-center gap-2 whitespace-nowrap">
                <button
                    type="button"
                    onclick="expandAllVersions()"
                    class="text-[11px] font-semibold text-gray-500 hover:text-gray-700"
                >
                    Expand All
                </button>

                <span class="text-gray-300">|</span>

                <button
                    type="button"
                    onclick="collapseAllVersions()"
                    class="text-[11px] font-semibold text-gray-500 hover:text-gray-700"
                >
                    Collapse All
                </button>
            </div>

        </div>
    </div>
</div>

    <div class="divide-y divide-gray-100">

    @foreach($versionDiffs as $i => $diff)

        @php
            $isVendorRole = isset($isVendorView) && $isVendorView;

            $actorLabel = match($diff['actor_type']) {
                'vendor'   => $isVendorRole ? 'You (Vendor)' : ($offer->vendor->name ?? 'Vendor'),
                'customer' => $isVendorRole ? ($offer->customer->name ?? 'Customer') : 'You',
                'admin'    => 'Admin',
                default    => 'System',
            };

            $actorClass = match($diff['actor_type']) {
                'vendor'   => $isVendorRole
                    ? 'bg-emerald-50 text-emerald-700'
                    : 'bg-blue-50 text-blue-700',

                'customer' => $isVendorRole
                    ? 'bg-blue-50 text-blue-700'
                    : 'bg-emerald-50 text-emerald-700',

                default => 'bg-gray-100 text-gray-600',
            };

            $isLatest = $i === count($versionDiffs) - 1;
        @endphp


        {{-- =========================================================
             VERSION BLOCK
        ========================================================== --}}
        <div
            class="version-block"
            data-actor="{{ $diff['actor_type'] }}"
            data-version="{{ $diff['version']->version_number }}"
            data-search="{{ strtolower(
                collect($diff['added'])
                    ->merge($diff['removed'])
                    ->merge($diff['unchanged'])
                    ->map(function($item) {
                        return $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id;
                    })
                    ->implode(' ')
            ) }}"
        >

            {{-- VERSION HEADER --}}
            <button
                type="button"
                class="version-toggle w-full flex items-center gap-2 px-6 py-4 text-left hover:bg-gray-50"
                data-target="version-body-{{ $diff['version']->id }}"
            >

                <svg
                    class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform {{ $isLatest ? '' : '-rotate-90' }}"
                    data-chevron
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>

                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $actorClass }}">
                    {{ $actorLabel }}
                </span>

                <span class="text-xs font-bold text-gray-700">
                    Version {{ $diff['version']->version_number }}
                </span>

                <span class="text-[10px] text-gray-400">
                    {{ $diff['version']->created_at->format('d M Y, h:i A') }}
                </span>


                @if(!$diff['is_initial'])

                    @if($diff['has_any_change'])

                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">
                            {{ count($diff['added']) }} added ·
                            {{ count($diff['removed']) }} removed ·
                            {{ count($diff['changed']) }} changed
                        </span>

                    @else

                        <span class="text-[10px] text-gray-400 italic">
                            No hoarding changes
                        </span>

                    @endif

                @endif


                <span class="ml-auto text-xs font-bold text-gray-700">
                    ₹{{ number_format((float) $diff['total_amount'], 2) }}
                </span>

            </button>


            {{-- VERSION BODY --}}
            <div
                id="version-body-{{ $diff['version']->id }}"
                class="version-body px-6 pb-5 {{ $isLatest ? '' : 'hidden' }}"
            >

                {{-- =====================================================
                     INITIAL VERSION
                ====================================================== --}}
                @if($diff['is_initial'])

                    <p class="text-xs text-gray-500 mb-3">
                        Initial offer created with
                        {{ $diff['item_count'] }} hoarding(s):
                    </p>


                    <div class="overflow-x-auto border border-gray-200 rounded-lg">

                        <table class="min-w-[850px] w-full text-xs text-left">

                            <thead class="bg-gray-50 text-gray-500 border-b">

                                <tr>
                                    <th class="px-4 py-3 font-semibold">
                                        Hoarding
                                    </th>

                                    <th class="px-4 py-3 font-semibold">
                                        Type
                                    </th>

                                    <th class="px-4 py-3 font-semibold">
                                        Start Date
                                    </th>

                                    <th class="px-4 py-3 font-semibold">
                                        End Date
                                    </th>

                                    <th class="px-4 py-3 font-semibold">
                                        Duration
                                    </th>

                                    <th class="px-4 py-3 font-semibold">
                                        Final Price
                                    </th>

                                    <th class="px-4 py-3 font-semibold">
                                        Status
                                    </th>
                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-50">

                                @foreach($diff['added'] as $item)

                                    @php
                                        $historyHoarding = $item->hoarding;
                                        $historyImage = $historyHoarding?->heroImage();
                                    @endphp

                                    <tr class="hover:bg-gray-50">

                                        {{-- HOARDING --}}
                                        <td class="px-4 py-3">

                                            <div class="flex items-center gap-3">

                                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">

                                                    @if($historyImage)

                                                        <img
                                                            src="{{ $historyImage }}"
                                                            alt="{{ $historyHoarding->title ?? 'Hoarding' }}"
                                                            class="w-full h-full object-cover"
                                                        >

                                                    @else

                                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                            <i class="fas fa-image text-sm"></i>
                                                        </div>

                                                    @endif

                                                </div>


                                                <div class="min-w-0">

                                                    <p class="font-semibold text-gray-800 truncate">
                                                        {{ $historyHoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                                                    </p>

                                                    <p class="text-[10px] text-gray-400">
                                                        {{ $historyHoarding->city ?? '' }}
                                                    </p>

                                                </div>

                                            </div>

                                        </td>


                                        {{-- TYPE --}}
                                        <td class="px-4 py-3">

                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded
                                                {{ $item->hoarding_type === 'dooh'
                                                    ? 'bg-purple-50 text-purple-600'
                                                    : 'bg-blue-50 text-blue-600' }}">

                                                {{ strtoupper($item->hoarding_type ?? 'OOH') }}

                                            </span>

                                        </td>


                                        {{-- START DATE --}}
                                        <td class="px-4 py-3">
                                            {{ optional($item->start_date)->format('d M Y') }}
                                        </td>


                                        {{-- END DATE --}}
                                        <td class="px-4 py-3">
                                            {{ optional($item->end_date)->format('d M Y') }}
                                        </td>


                                        {{-- DURATION --}}
                                        <td class="px-4 py-3">
                                            {{ $item->duration_months }}
                                            month{{ $item->duration_months !== 1 ? 's' : '' }}
                                        </td>


                                        {{-- PRICE --}}
                                        <td class="px-4 py-3 font-bold text-emerald-700">
                                            ₹{{ number_format((float) $item->final_price, 2) }}
                                        </td>


                                        {{-- STATUS --}}
                                        <td class="px-4 py-3">

                                            <span class="text-[9px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-bold">
                                                Initial
                                            </span>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>


                @else


                    {{-- =================================================
                         ADDED
                    ================================================== --}}
                    @if(!empty($diff['added']))

                        <div class="mb-5">

                            <p class="text-[10px] font-bold text-emerald-600 mb-2">
                                ＋ Added
                            </p>


                            <div class="overflow-x-auto border border-emerald-100 rounded-lg">

                                <table class="min-w-[850px] w-full text-xs text-left">

                                    <thead class="bg-emerald-50 text-emerald-700 border-b border-emerald-100">

                                        <tr>

                                            <th class="px-4 py-3 font-semibold">
                                                Hoarding
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Type
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Start Date
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                End Date
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Duration
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Final Price
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Status
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody class="divide-y divide-emerald-50">

                                        @foreach($diff['added'] as $item)

                                            @php
                                                $historyHoarding = $item->hoarding;
                                                $historyImage = $historyHoarding?->heroImage();
                                            @endphp

                                            <tr class="hover:bg-emerald-50/30">

                                                <td class="px-4 py-3">

                                                    <div class="flex items-center gap-3">

                                                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">

                                                            @if($historyImage)

                                                                <img
                                                                    src="{{ $historyImage }}"
                                                                    alt="{{ $historyHoarding->title ?? 'Hoarding' }}"
                                                                    class="w-full h-full object-cover"
                                                                >

                                                            @else

                                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                                    <i class="fas fa-image text-sm"></i>
                                                                </div>

                                                            @endif

                                                        </div>


                                                        <div class="min-w-0">

                                                            <p class="font-semibold text-gray-800 truncate">
                                                                {{ $historyHoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                                                            </p>

                                                            <p class="text-[10px] text-gray-400">
                                                                {{ $historyHoarding->city ?? '' }}
                                                            </p>

                                                        </div>

                                                    </div>

                                                </td>


                                                <td class="px-4 py-3">

                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded
                                                        {{ $item->hoarding_type === 'dooh'
                                                            ? 'bg-purple-50 text-purple-600'
                                                            : 'bg-blue-50 text-blue-600' }}">

                                                        {{ strtoupper($item->hoarding_type ?? 'OOH') }}

                                                    </span>

                                                </td>


                                                <td class="px-4 py-3">
                                                    {{ optional($item->start_date)->format('d M Y') }}
                                                </td>

                                                <td class="px-4 py-3">
                                                    {{ optional($item->end_date)->format('d M Y') }}
                                                </td>

                                                <td class="px-4 py-3">
                                                    {{ $item->duration_months }}
                                                    month{{ $item->duration_months !== 1 ? 's' : '' }}
                                                </td>

                                                <td class="px-4 py-3 font-bold text-emerald-700">
                                                    ₹{{ number_format((float) $item->final_price, 2) }}
                                                </td>

                                                <td class="px-4 py-3">

                                                    <span class="text-[9px] bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded font-bold">
                                                        Added
                                                    </span>

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    @endif



                    {{-- =================================================
                         REMOVED
                    ================================================== --}}
                    @if(!empty($diff['removed']))

                        <div class="mb-5">

                            <p class="text-[10px] font-bold text-red-600 mb-2">
                                － Removed
                            </p>


                            <div class="overflow-x-auto border border-red-100 rounded-lg">

                                <table class="min-w-[850px] w-full text-xs text-left">

                                    <thead class="bg-red-50 text-red-700 border-b border-red-100">

                                        <tr>

                                            <th class="px-4 py-3 font-semibold">
                                                Hoarding
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Type
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Start Date
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                End Date
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Duration
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Price
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Status
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody class="divide-y divide-red-50">

                                        @foreach($diff['removed'] as $item)

                                            @php
                                                $historyHoarding = $item->hoarding;
                                                $historyImage = $historyHoarding?->heroImage();
                                            @endphp

                                            <tr class="hover:bg-red-50/30">

                                                <td class="px-4 py-3">

                                                    <div class="flex items-center gap-3">

                                                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">

                                                            @if($historyImage)

                                                                <img
                                                                    src="{{ $historyImage }}"
                                                                    alt="{{ $historyHoarding->title ?? 'Hoarding' }}"
                                                                    class="w-full h-full object-cover"
                                                                >

                                                            @else

                                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                                    <i class="fas fa-image text-sm"></i>
                                                                </div>

                                                            @endif

                                                        </div>


                                                        <div class="min-w-0">

                                                            <p class="font-semibold text-gray-800 truncate line-through">
                                                                {{ $historyHoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                                                            </p>

                                                            <p class="text-[10px] text-gray-400">
                                                                {{ $historyHoarding->city ?? '' }}
                                                            </p>

                                                        </div>

                                                    </div>

                                                </td>


                                                <td class="px-4 py-3">

                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">

                                                        {{ strtoupper($item->hoarding_type ?? 'OOH') }}

                                                    </span>

                                                </td>


                                                <td class="px-4 py-3">
                                                    {{ optional($item->start_date)->format('d M Y') }}
                                                </td>

                                                <td class="px-4 py-3">
                                                    {{ optional($item->end_date)->format('d M Y') }}
                                                </td>

                                                <td class="px-4 py-3">
                                                    {{ $item->duration_months }}
                                                    month{{ $item->duration_months !== 1 ? 's' : '' }}
                                                </td>

                                                <td class="px-4 py-3 font-bold text-red-600">
                                                    ₹{{ number_format((float) $item->final_price, 2) }}
                                                </td>

                                                <td class="px-4 py-3">

                                                    <span class="text-[9px] bg-red-50 text-red-600 px-1.5 py-0.5 rounded font-bold">
                                                        Removed
                                                    </span>

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    @endif



                    {{-- =================================================
                         CHANGED
                    ================================================== --}}
                    @if(!empty($diff['changed']))

                        <div class="mb-5">

                            <p class="text-[10px] font-bold text-amber-600 mb-2">
                                ↻ Changed
                            </p>


                            <div class="overflow-x-auto border border-amber-100 rounded-lg">

                                <table class="min-w-[850px] w-full text-xs text-left">

                                    <thead class="bg-amber-50 text-amber-700 border-b border-amber-100">

                                        <tr>

                                            <th class="px-4 py-3 font-semibold">
                                                Hoarding
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Previous
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Current
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Date Change
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Price Change
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Status
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody class="divide-y divide-amber-50">

                                        @foreach($diff['changed'] as $change)

                                            @php
                                                $cur = $change['current'];
                                                $prev = $change['previous'];

                                                $historyHoarding = $cur->hoarding;
                                                $historyImage = $historyHoarding?->heroImage();

                                                $dateChanged =
                                                    optional($prev->start_date)->format('Y-m-d') !== optional($cur->start_date)->format('Y-m-d')
                                                    ||
                                                    optional($prev->end_date)->format('Y-m-d') !== optional($cur->end_date)->format('Y-m-d');

                                                $priceChanged =
                                                    round((float) $prev->final_price, 2) !==
                                                    round((float) $cur->final_price, 2);
                                            @endphp


                                            <tr class="hover:bg-amber-50/30">

                                                {{-- HOARDING --}}
                                                <td class="px-4 py-3">

                                                    <div class="flex items-center gap-3">

                                                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">

                                                            @if($historyImage)

                                                                <img
                                                                    src="{{ $historyImage }}"
                                                                    alt="{{ $historyHoarding->title ?? 'Hoarding' }}"
                                                                    class="w-full h-full object-cover"
                                                                >

                                                            @else

                                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                                    <i class="fas fa-image text-sm"></i>
                                                                </div>

                                                            @endif

                                                        </div>


                                                        <div class="min-w-0">

                                                            <p class="font-semibold text-gray-800 truncate">
                                                                {{ $historyHoarding->title ?? 'Hoarding #' . $cur->hoarding_id }}
                                                            </p>

                                                            <p class="text-[10px] text-gray-400">
                                                                {{ $historyHoarding->city ?? '' }}
                                                            </p>

                                                        </div>

                                                    </div>

                                                </td>


                                                {{-- PREVIOUS --}}
                                                <td class="px-4 py-3">

                                                    <div class="text-[10px] text-gray-500">

                                                        <div>
                                                            {{ optional($prev->start_date)->format('d M Y') }}
                                                            →
                                                            {{ optional($prev->end_date)->format('d M Y') }}
                                                        </div>

                                                        <div class="font-semibold text-gray-600 mt-1">
                                                            ₹{{ number_format((float) $prev->final_price, 2) }}
                                                        </div>

                                                    </div>

                                                </td>


                                                {{-- CURRENT --}}
                                                <td class="px-4 py-3">

                                                    <div class="text-[10px] text-gray-700">

                                                        <div>
                                                            {{ optional($cur->start_date)->format('d M Y') }}
                                                            →
                                                            {{ optional($cur->end_date)->format('d M Y') }}
                                                        </div>

                                                        <div class="font-bold text-emerald-700 mt-1">
                                                            ₹{{ number_format((float) $cur->final_price, 2) }}
                                                        </div>

                                                    </div>

                                                </td>


                                                {{-- DATE CHANGE --}}
                                                <td class="px-4 py-3">

                                                    @if($dateChanged)

                                                        <span class="text-[9px] bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded font-bold">
                                                            Changed
                                                        </span>

                                                    @else

                                                        <span class="text-[9px] bg-gray-50 text-gray-400 px-1.5 py-0.5 rounded font-bold">
                                                            No Change
                                                        </span>

                                                    @endif

                                                </td>


                                                {{-- PRICE CHANGE --}}
                                                <td class="px-4 py-3">

                                                    @if($priceChanged)

                                                        <span class="text-[9px] bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded font-bold">
                                                            ₹{{ number_format((float) $prev->final_price, 2) }}
                                                            →
                                                            ₹{{ number_format((float) $cur->final_price, 2) }}
                                                        </span>

                                                    @else

                                                        <span class="text-[9px] bg-gray-50 text-gray-400 px-1.5 py-0.5 rounded font-bold">
                                                            No Change
                                                        </span>

                                                    @endif

                                                </td>


                                                {{-- STATUS --}}
                                                <td class="px-4 py-3">

                                                    <span class="text-[9px] bg-amber-50 text-amber-600 px-1.5 py-0.5 rounded font-bold">
                                                        Changed
                                                    </span>

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    @endif



                    {{-- =================================================
                         UNCHANGED
                    ================================================== --}}
                    @if(!empty($diff['unchanged']))

                        <details class="mt-1">

                            <summary class="text-[10px] font-semibold text-gray-400 cursor-pointer select-none mb-2">

                                {{ count($diff['unchanged']) }}
                                hoarding(s) carried over unchanged

                            </summary>


                            <div class="overflow-x-auto border border-gray-200 rounded-lg">

                                <table class="min-w-[850px] w-full text-xs text-left">

                                    <thead class="bg-gray-50 text-gray-500 border-b">

                                        <tr>

                                            <th class="px-4 py-3 font-semibold">
                                                Hoarding
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Type
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Start Date
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                End Date
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Duration
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Final Price
                                            </th>

                                            <th class="px-4 py-3 font-semibold">
                                                Status
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody class="divide-y divide-gray-50">

                                        @foreach($diff['unchanged'] as $item)

                                            @php
                                                $historyHoarding = $item->hoarding;
                                                $historyImage = $historyHoarding?->heroImage();
                                            @endphp


                                            <tr class="hover:bg-gray-50">

                                                <td class="px-4 py-3">

                                                    <div class="flex items-center gap-3">

                                                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">

                                                            @if($historyImage)

                                                                <img
                                                                    src="{{ $historyImage }}"
                                                                    alt="{{ $historyHoarding->title ?? 'Hoarding' }}"
                                                                    class="w-full h-full object-cover"
                                                                >

                                                            @else

                                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                                    <i class="fas fa-image text-sm"></i>
                                                                </div>

                                                            @endif

                                                        </div>


                                                        <div class="min-w-0">

                                                            <p class="font-semibold text-gray-800 truncate">
                                                                {{ $historyHoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                                                            </p>

                                                            <p class="text-[10px] text-gray-400">
                                                                {{ $historyHoarding->city ?? '' }}
                                                            </p>

                                                        </div>

                                                    </div>

                                                </td>


                                                <td class="px-4 py-3">

                                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-600">
                                                        {{ strtoupper($item->hoarding_type ?? 'OOH') }}
                                                    </span>

                                                </td>


                                                <td class="px-4 py-3">
                                                    {{ optional($item->start_date)->format('d M Y') }}
                                                </td>


                                                <td class="px-4 py-3">
                                                    {{ optional($item->end_date)->format('d M Y') }}
                                                </td>


                                                <td class="px-4 py-3">
                                                    {{ $item->duration_months }}
                                                    month{{ $item->duration_months !== 1 ? 's' : '' }}
                                                </td>


                                                <td class="px-4 py-3 font-bold text-gray-600">
                                                    ₹{{ number_format((float) $item->final_price, 2) }}
                                                </td>


                                                <td class="px-4 py-3">

                                                    <span class="text-[9px] bg-gray-50 text-gray-500 px-1.5 py-0.5 rounded font-bold">
                                                        Unchanged
                                                    </span>

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </details>

                    @endif



                    @if(
                        !$diff['has_any_change']
                        &&
                        empty($diff['unchanged'])
                    )

                        <p class="text-[10px] text-gray-400 italic">
                            No hoardings on this version.
                        </p>

                    @endif

                @endif

            </div>

        </div>

    @endforeach

    </div>
</div>

<script>
document.querySelectorAll('.version-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const body = document.getElementById(btn.dataset.target);
        const chevron = btn.querySelector('[data-chevron]');
        body.classList.toggle('hidden');
        chevron.classList.toggle('-rotate-90');
    });
});

function expandAllVersions() {
    document.querySelectorAll('.version-body').forEach(el => el.classList.remove('hidden'));
    document.querySelectorAll('[data-chevron]').forEach(el => el.classList.remove('-rotate-90'));
}
function collapseAllVersions() {
    document.querySelectorAll('.version-body').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('[data-chevron]').forEach(el => el.classList.add('-rotate-90'));
}
document.addEventListener('DOMContentLoaded', function () {

    const acceptBtn = document.getElementById('vendor-accept-btn');
    const rejectBtn = document.getElementById('vendor-reject-btn');

    // =========================
    // ACCEPT CUSTOMER MODIFICATION
    // =========================
    if (acceptBtn) {

        acceptBtn.addEventListener('click', async function () {

            const offerId = this.dataset.offerId;

            if (!confirm('Are you sure you want to accept this offer?')) {
                return;
            }

            this.disabled = true;

            try {

                const response = await fetch(this.dataset.url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]'
                        ).getAttribute('content')
                    }
                });

                const data = await response.json();

                console.log('Accept response:', data);

                if (response.ok) {

                    alert(data.message || 'Offer accepted successfully.');

                    window.location.reload();

                } else {

                    alert(data.message || 'Unable to accept offer.');

                    this.disabled = false;
                }

            } catch (error) {

                console.error('Accept error:', error);

                alert('Something went wrong while accepting the offer.');

                this.disabled = false;
            }

        });
    }


    // =========================
    // VENDOR REJECT
    // =========================
    if (rejectBtn) {

        rejectBtn.addEventListener('click', async function () {

            const offerId = this.dataset.offerId;

            if (!confirm('Are you sure you want to reject this offer?')) {
                return;
            }

            this.disabled = true;

            try {

                const response = await fetch(
                    `/offers/${offerId}/vendor-reject`,
                    {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).getAttribute('content')
                        }
                    }
                );

                const data = await response.json();

                console.log('Reject response:', data);

                if (response.ok) {

                    alert(data.message || 'Offer rejected successfully.');

                    window.location.reload();

                } else {

                    alert(data.message || 'Unable to reject offer.');

                    this.disabled = false;
                }

            } catch (error) {

                console.error('Reject error:', error);

                alert('Something went wrong while rejecting the offer.');

                this.disabled = false;
            }

        });
    }

});

// ============================================================
// FINAL OFFER SEARCH + TYPE FILTER
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    const finalSearch = document.getElementById('final-offer-search');
    const finalType = document.getElementById('final-offer-type-filter');
    const finalRows = document.querySelectorAll('.final-offer-row');
    const finalNoResults = document.getElementById('final-offer-no-results');

    function filterFinalOffer() {

        if (!finalSearch || !finalType) {
            return;
        }

        const searchValue = finalSearch.value
            .trim()
            .toLowerCase();

        const typeValue = finalType.value;

        let visibleCount = 0;

        finalRows.forEach(row => {

            const rowSearch = row.dataset.search || '';
            const rowType = row.dataset.type || '';

            const matchesSearch =
                !searchValue ||
                rowSearch.includes(searchValue);

            const matchesType =
                typeValue === 'all' ||
                rowType === typeValue;

            if (matchesSearch && matchesType) {

                row.classList.remove('hidden');
                visibleCount++;

            } else {

                row.classList.add('hidden');

            }

        });

        if (finalNoResults) {

            if (visibleCount === 0 && finalRows.length > 0) {
                finalNoResults.classList.remove('hidden');
            } else {
                finalNoResults.classList.add('hidden');
            }

        }
    }

    if (finalSearch) {
        finalSearch.addEventListener('input', filterFinalOffer);
    }

    if (finalType) {
        finalType.addEventListener('change', filterFinalOffer);
    }


    // ============================================================
    // NEGOTIATION HISTORY SEARCH + FILTER
    // ============================================================

    const historySearch =
        document.getElementById('history-search');

    const historyActor =
        document.getElementById('history-actor-filter');

    const historyVersion =
        document.getElementById('history-version-filter');

    const versionBlocks =
        document.querySelectorAll('.version-block');


    function filterNegotiationHistory() {

        if (!historySearch || !historyActor || !historyVersion) {
            return;
        }

        const searchValue = historySearch.value
            .trim()
            .toLowerCase();

        const actorValue = historyActor.value;
        const versionValue = historyVersion.value;

        versionBlocks.forEach(block => {

            const blockSearch =
                block.dataset.search || '';

            const blockActor =
                block.dataset.actor || '';

            const blockVersion =
                block.dataset.version || '';

            const matchesSearch =
                !searchValue ||
                blockSearch.includes(searchValue);

            const matchesActor =
                actorValue === 'all' ||
                blockActor === actorValue;

            const matchesVersion =
                versionValue === 'all' ||
                blockVersion === versionValue;

            if (
                matchesSearch &&
                matchesActor &&
                matchesVersion
            ) {

                block.classList.remove('hidden');

            } else {

                block.classList.add('hidden');

            }

        });
    }


    if (historySearch) {
        historySearch.addEventListener(
            'input',
            filterNegotiationHistory
        );
    }

    if (historyActor) {
        historyActor.addEventListener(
            'change',
            filterNegotiationHistory
        );
    }

    if (historyVersion) {
        historyVersion.addEventListener(
            'change',
            filterNegotiationHistory
        );

    }

});

</script>

</div>

{{-- Reject confirm modal --}}
<div id="vendorRejectModal" class="fixed inset-0 z-[90] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeVendorRejectModal()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-[92vw] max-w-md p-6">
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 mb-4">
            <p class="text-sm font-bold text-red-700">⚠ Are you sure you want to reject this offer?</p>
        </div>
        <p class="text-xs text-gray-500 mb-3">This ends the negotiation. The customer will be notified and won't be able to accept or modify this offer any further.</p>
        <textarea id="vendor-reject-reason" rows="3" class="w-full border rounded p-2 text-sm mb-4" placeholder="Optional: reason for rejecting"></textarea>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeVendorRejectModal()" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-md">Cancel</button>
            <button type="button" id="vendor-reject-confirm-btn" class="px-6 py-2 bg-red-600 text-white text-sm font-bold rounded-md hover:bg-red-700">Reject Offer</button>
        </div>
    </div>
</div>

<script>
window.CSRF_TOKEN = '{{ csrf_token() }}';
window.VENDOR_REJECT_URL = '{{ route('vendor.offers.vendor-reject', $offer->id) }}';
window.OFFERS_INDEX_URL = '{{ route('vendor.offers.index') }}';

document.getElementById('vendor-reject-btn')?.addEventListener('click', () => {
    document.getElementById('vendorRejectModal').classList.remove('hidden');
});
function closeVendorRejectModal() { document.getElementById('vendorRejectModal').classList.add('hidden'); }

let isRejecting = false;
document.getElementById('vendor-reject-confirm-btn')?.addEventListener('click', async () => {
    if (isRejecting) return;
    isRejecting = true;

    const btn = document.getElementById('vendor-reject-confirm-btn');
    btn.disabled = true; btn.innerText = 'Rejecting…';

    try {
        const reason = document.getElementById('vendor-reject-reason').value.trim();
        const res = await fetch(window.VENDOR_REJECT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ reason }),
        }).then(r => r.json());

        alert(res.message);
        if (res.success) window.location.href = window.OFFERS_INDEX_URL;
    } finally {
        btn.disabled = false; btn.innerText = 'Reject Offer';
        isRejecting = false;
    }
});
</script>
@endsection
