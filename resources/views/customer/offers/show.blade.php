{{-- @extends('layouts.customer')

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

                        // It's your turn to act only when the offer is still open AND
                        // the most recent version was submitted by the vendor. If you
                        // already sent your own changes, it's now waiting on the
                        // vendor — no action buttons until they respond again.
                        // $customerTurn = $offer->canAccept() && $offer->wasLastModifiedByVendor();
                            $customerTurn = $offer->isNegotiable() && $offer->wasLastModifiedByVendor();

                        $awaitingVendor = $offer->canAccept() && $offer->wasLastModifiedByCustomer();
                    @endphp
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $st['class'] }}">{{ $st['label'] }}</span>
                    @if($awaitingVendor)
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-amber-50 text-amber-700">Awaiting vendor's response</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-1">Created {{ $offer->created_at->format('d M Y, h:i A') }} · From: {{ $offer->vendor->name ?? 'N/A' }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($customerTurn)
                    <button type="button" id="customer-accept-btn"
                        class="px-4 py-2 text-xs font-bold text-white bg-[#2E5B42] rounded hover:bg-opacity-90">
                        Accept Offer
                    </button>
                    <a href="{{ route('customer.offers.modify', $offer->id) }}"
                        class="px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded hover:bg-blue-700">
                        Modify Offer
                    </a>
                    <button type="button" id="customer-reject-btn"
                        class="px-4 py-2 text-xs font-bold text-red-600 border border-red-200 bg-red-50 rounded hover:bg-red-100">
                        Reject Offer
                    </button>
                @endif
                <a href="{{ route('customer.offers.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                    Back to My Offers
                </a>
            </div>
        </div>

        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div>
                <p class="font-semibold text-gray-700 mb-1">Vendor</p>
                <p>{{ $offer->vendor->name ?? 'N/A' }}</p>
                <p class="text-gray-400">{{ $offer->vendor->email ?? '' }}</p>
                <p class="text-gray-400">{{ $offer->vendor->phone ?? '' }}</p>
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


<div id="customerRejectModal" class="fixed inset-0 z-[90] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeCustomerRejectModal()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-[92vw] max-w-md p-6">
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 mb-4">
            <p class="text-sm font-bold text-red-700">⚠ Are you sure you want to reject this offer?</p>
        </div>
        <p class="text-xs text-gray-500 mb-3">This ends the negotiation. The vendor will be notified.</p>
        <textarea id="customer-reject-reason" rows="3" class="w-full border rounded p-2 text-sm mb-4" placeholder="Optional: reason for rejecting"></textarea>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeCustomerRejectModal()" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-md">Cancel</button>
            <button type="button" id="customer-reject-confirm-btn" class="px-6 py-2 bg-red-600 text-white text-sm font-bold rounded-md hover:bg-red-700">Reject Offer</button>
        </div>
    </div>
</div>

<script>
window.CSRF_TOKEN = '{{ csrf_token() }}';
window.CUSTOMER_ACCEPT_URL = '{{ route('customer.offers.accept', $offer->id) }}';
window.CUSTOMER_REJECT_URL = '{{ route('customer.offers.reject', $offer->id) }}';
window.CUSTOMER_OFFERS_INDEX_URL = '{{ route('customer.offers.index') }}';

let isAccepting = false, isRejecting = false;

document.getElementById('customer-accept-btn')?.addEventListener('click', async () => {
    if (isAccepting) return;
    if (!confirm('Accept this offer? This will reject any other offers on this enquiry.')) return;
    isAccepting = true;

    const btn = document.getElementById('customer-accept-btn');
    btn.disabled = true; btn.innerText = 'Accepting…';

    try {
        const res = await fetch(window.CUSTOMER_ACCEPT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, Accept: 'application/json' },
        }).then(r => r.json());
        alert(res.message);
        if (res.success) window.location.reload();
    } finally {
        btn.disabled = false; btn.innerText = 'Accept Offer';
        isAccepting = false;
    }
});

document.getElementById('customer-reject-btn')?.addEventListener('click', () => {
    document.getElementById('customerRejectModal').classList.remove('hidden');
});
function closeCustomerRejectModal() { document.getElementById('customerRejectModal').classList.add('hidden'); }

document.getElementById('customer-reject-confirm-btn')?.addEventListener('click', async () => {
    if (isRejecting) return;
    isRejecting = true;

    const btn = document.getElementById('customer-reject-confirm-btn');
    btn.disabled = true; btn.innerText = 'Rejecting…';

    try {
        const reason = document.getElementById('customer-reject-reason').value.trim();
        const res = await fetch(window.CUSTOMER_REJECT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({ reason }),
        }).then(r => r.json());
        alert(res.message);
        if (res.success) window.location.href = window.CUSTOMER_OFFERS_INDEX_URL;
    } finally {
        btn.disabled = false; btn.innerText = 'Reject Offer';
        isRejecting = false;
    }
});
</script>
@endsection --}}
@extends('layouts.customer')

@section('title', 'Offer #' . $offer->offer_number)

@section('content')

<div class="px-6 py-6 bg-gray-50">

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">

        <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

            <div>

                <div class="flex items-center gap-2">

                    <h2 class="text-lg font-bold text-gray-800">
                        Offer #{{ $offer->offer_number }}
                    </h2>

                    <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-bold">
                        v{{ $offer->version }}
                    </span>

                    @php

                        $statusMap = [
                            'draft' => [
                                'label' => 'Draft',
                                'class' => 'bg-gray-100 text-gray-600'
                            ],

                            'sent' => [
                                'label' => 'Sent',
                                'class' => 'bg-blue-50 text-blue-600'
                            ],

                            'accepted' => [
                                'label' => 'Accepted',
                                'class' => 'bg-emerald-50 text-emerald-600'
                            ],

                            'rejected' => [
                                'label' => 'Rejected',
                                'class' => 'bg-red-50 text-red-600'
                            ],

                            'expired' => [
                                'label' => 'Expired',
                                'class' => 'bg-orange-50 text-orange-600'
                            ],
                        ];

                        $st = $statusMap[$offer->status]
                            ?? [
                                'label' => ucfirst($offer->status),
                                'class' => 'bg-gray-100 text-gray-600'
                            ];

                        $customerTurn =
                            $offer->canAccept()
                            && $offer->wasLastModifiedByVendor();

                        $awaitingVendor =
                            $offer->canAccept()
                            && $offer->wasLastModifiedByCustomer();

                    @endphp

                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $st['class'] }}">
                        {{ $st['label'] }}
                    </span>

                    @if($awaitingVendor)

                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-amber-50 text-amber-700">
                            Awaiting vendor's response
                        </span>

                    @endif

                </div>

                <p class="text-xs text-gray-400 mt-1">

                    Created
                    {{ $offer->created_at->format('d M Y, h:i A') }}

                    · From:
                    {{ $offer->vendor->name ?? 'N/A' }}

                </p>

            </div>


            {{-- ACTION BUTTONS --}}
            <div class="flex flex-wrap items-center gap-2">

                @if($customerTurn)

                    <button
                        type="button"
                        id="customer-accept-btn"
                        class="px-4 py-2 text-xs font-bold text-white bg-[#2E5B42] rounded hover:bg-opacity-90"
                    >
                        Accept Offer
                    </button>

                    <a
                        href="{{ route('customer.offers.modify', $offer->id) }}"
                        class="px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded hover:bg-blue-700"
                    >
                        Modify Offer
                    </a>

                    <button
                        type="button"
                        id="customer-reject-btn"
                        class="px-4 py-2 text-xs font-bold text-red-600 border border-red-200 bg-red-50 rounded hover:bg-red-100"
                    >
                        Reject Offer
                    </button>

                @endif


                <a
                    href="{{ route('customer.offers.index') }}"
                    class="px-4 py-2 text-xs font-semibold text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
                >
                    Back to My Offers
                </a>

            </div>

        </div>


        {{-- SUMMARY --}}
        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">

            <div>

                <p class="font-semibold text-gray-700 mb-1">
                    Vendor
                </p>

                <p>
                    {{ $offer->vendor->name ?? 'N/A' }}
                </p>

                <p class="text-gray-400">
                    {{ $offer->vendor->email ?? '' }}
                </p>

                <p class="text-gray-400">
                    {{ $offer->vendor->phone ?? '' }}
                </p>

            </div>


            <div>

                <p class="font-semibold text-gray-700 mb-1">
                    Offer Summary
                </p>

                <p>
                    {{ $offer->currentVersion?->items?->count() ?? 0 }}
                    hoarding(s)
                </p>

                <p class="font-bold text-emerald-700 text-sm">
                    ₹{{ number_format((float) $offer->price, 2) }}
                </p>

            </div>


            <div>

                <p class="font-semibold text-gray-700 mb-1">
                    Validity
                </p>

                <p>
                    {{
                        $offer->valid_until
                            ? $offer->valid_until->format('d M Y')
                            : 'No expiry'
                    }}
                </p>

                <p class="text-gray-400">
                    {{ $offer->getExpiryLabel() }}
                </p>

            </div>

        </div>

    </div>



    {{-- =========================================================
        FINAL OFFER
    ========================================================== --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">

        {{-- HEADER --}}
        <div class="px-6 py-4 border-b relative">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                <div>

                    <div class="flex items-center gap-2">

    <h3 class="font-bold text-gray-800 text-sm">
        Final Offer — Current Hoarding List
    </h3>

   @php
    $latestNegotiationDate = collect($versionDiffs)
        ->sortByDesc(function ($diff) {
            return optional($diff['version']->created_at)->timestamp ?? 0;
        })
        ->first();
@endphp

    @if($latestNegotiationDate)
        <span class="text-[10px] font-semibold px-2 py-1 rounded-full bg-blue-50 text-blue-600">
            Latest Negotiation:
            {{ optional($latestNegotiationDate['version']->created_at)->format('d M Y, h:i A') }}
        </span>
    @endif

</div>

                    <p class="text-xs text-gray-400 mt-0.5">
                        This is what's on the table right now, sorted by start date.
                    </p>

                </div>


                <div class="flex items-center gap-2">

                    {{-- SEARCH --}}
                    <div class="relative">

                        <input
                            type="text"
                            id="hoardingSearch"
                            placeholder="Search hoarding..."
                            class="w-52 pl-9 pr-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        >

                        <svg
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z"
                            />
                        </svg>

                    </div>


                    {{-- FILTER BUTTON --}}
                    <button
                        type="button"
                        id="hoardingFilterBtn"
                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                    >

                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 5h18M6 12h12m-8 7h4"
                            />
                        </svg>

                        Filter

                    </button>


                    {{-- HOARDING FILTER DROPDOWN --}}
                    <div
                        id="hoardingFilterDropdown"
                        class="hidden absolute right-6 top-full mt-2 z-50 w-72 bg-white border border-gray-200 rounded-xl shadow-lg"
                    >

                        <div class="p-4">

                            <div class="mb-3">

                                <h4 class="text-sm font-semibold text-gray-800">
                                    Filter by Created Enquiry
                                </h4>

                                <p class="text-xs text-gray-400 mt-1">
                                    Select enquiry creation date
                                </p>

                            </div>


                            {{-- ALL --}}
                            <label class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">

                                <input
                                    type="radio"
                                    name="enquiry_date_filter"
                                    value="all"
                                    class="enquiry-date-filter"
                                    checked
                                >

                                <span class="text-sm text-gray-700">
                                    All
                                </span>

                            </label>


                            {{-- LAST WEEK --}}
                            <label class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">

                                <input
                                    type="radio"
                                    name="enquiry_date_filter"
                                    value="last_week"
                                    class="enquiry-date-filter"
                                >

                                <span class="text-sm text-gray-700">
                                    Last Week
                                </span>

                            </label>


                            {{-- LAST MONTH --}}
                            <label class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">

                                <input
                                    type="radio"
                                    name="enquiry_date_filter"
                                    value="last_month"
                                    class="enquiry-date-filter"
                                >

                                <span class="text-sm text-gray-700">
                                    Last Month
                                </span>

                            </label>


                            {{-- LAST YEAR --}}
                            <label class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">

                                <input
                                    type="radio"
                                    name="enquiry_date_filter"
                                    value="last_year"
                                    class="enquiry-date-filter"
                                >

                                <span class="text-sm text-gray-700">
                                    Last Year
                                </span>

                            </label>


                            {{-- CUSTOM --}}
                            <label class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 cursor-pointer">

                                <input
                                    type="radio"
                                    name="enquiry_date_filter"
                                    value="custom"
                                    class="enquiry-date-filter"
                                >

                                <span class="text-sm text-gray-700">
                                    Customize
                                </span>

                            </label>


                            {{-- CUSTOM DATE FIELDS --}}
                            <div
                                id="customDateFields"
                                class="hidden mt-3 pt-3 border-t border-gray-100"
                            >

                                <div class="grid grid-cols-2 gap-3">

                                    <div>

                                        <label class="block text-xs font-medium text-gray-600 mb-1">
                                            Start Date
                                        </label>

                                        <input
                                            type="date"
                                            id="filterStartDate"
                                            class="w-full px-2.5 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                        >

                                    </div>


                                    <div>

                                        <label class="block text-xs font-medium text-gray-600 mb-1">
                                            End Date
                                        </label>

                                        <input
                                            type="date"
                                            id="filterEndDate"
                                            class="w-full px-2.5 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                                        >

                                    </div>

                                </div>


                                <button
                                    type="button"
                                    id="applyCustomDateFilter"
                                    class="w-full mt-3 px-3 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                                >
                                    Apply Filter
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- TABLE --}}
        <div class="overflow-x-auto">

            <table class="min-w-[760px] w-full text-xs text-left">

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
                            Source
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-50">

    @forelse(
        $offer->currentVersion?->items?->sortBy('start_date') ?? []
        as $item
    )

        @php

            $h = $item->hoarding;

            /*
            |--------------------------------------------------------------------------
            | Hoarding Image
            |--------------------------------------------------------------------------
            */

            $hoardingImage = $h?->heroImage();

            /*
            |--------------------------------------------------------------------------
            | Enquiry Date
            |--------------------------------------------------------------------------
            */

            $enquiryDate = optional(
                $item->enquiry?->created_at
            )->format('Y-m-d');

            /*
            |--------------------------------------------------------------------------
            | Search Data
            |--------------------------------------------------------------------------
            */

            $searchData = strtolower(
                ($h->title ?? '') . ' ' .
                ($h->city ?? '') . ' ' .
                ($item->hoarding_type ?? '') . ' ' .
                (
                    ($item->meta['source'] ?? '') === 'enquiry'
                        ? 'from enquiry'
                        : 'added'
                )
            );

        @endphp


        <tr
            class="hover:bg-gray-50 hoarding-row"
            data-enquiry-date="{{ $enquiryDate }}"
            data-search="{{ $searchData }}"
        >

            {{-- HOARDING --}}
            <td class="px-4 py-3">

                <div class="flex items-center gap-3">

                    {{-- IMAGE --}}
                    <div
                        class="w-14 h-14 rounded-lg overflow-hidden
                               bg-gray-100 border border-gray-200
                               flex-shrink-0"
                    >

                        @if($hoardingImage)

                            <img
                                src="{{ $hoardingImage }}"
                                alt="{{ $h->title ?? 'Hoarding' }}"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            >

                        @else

                            <div
                                class="w-full h-full flex items-center
                                       justify-center text-gray-400"
                            >

                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M4 16l4-4a2 2 0 012.828 0L16 17m-2-2l1.172-1.172a2 2 0 012.828 0L20 16M14 8h.01M5 20h14a1 1 0 001-1V5a1 1 0 001-1z"
                                    />
                                </svg>

                            </div>

                        @endif

                    </div>


                    {{-- DETAILS --}}
                    <div class="min-w-0">

                        <p
                            class="font-semibold text-gray-800 truncate max-w-[220px]"
                        >
                            {{ $h->title ?? 'Hoarding #' . $item->hoarding_id }}
                        </p>

                        <p class="text-[10px] text-gray-400 mt-0.5">
                            {{ $h->city ?? 'N/A' }}
                        </p>

                    </div>

                </div>

            </td>


            {{-- TYPE --}}
            <td class="px-4 py-3">

                <span
                    class="text-[10px] font-bold px-1.5 py-0.5 rounded
                    {{
                        $item->hoarding_type === 'dooh'
                            ? 'bg-purple-50 text-purple-600'
                            : 'bg-blue-50 text-blue-600'
                    }}"
                >
                    {{ strtoupper($item->hoarding_type ?? 'N/A') }}
                </span>

            </td>


            {{-- START DATE --}}
            <td class="px-4 py-3 whitespace-nowrap">

                {{ optional($item->start_date)->format('d M Y') }}

            </td>


            {{-- END DATE --}}
            <td class="px-4 py-3 whitespace-nowrap">

                {{ optional($item->end_date)->format('d M Y') }}

            </td>


            {{-- DURATION --}}
            <td class="px-4 py-3 whitespace-nowrap">

                {{ $item->duration_months }}

                month{{ $item->duration_months != 1 ? 's' : '' }}

            </td>


            {{-- PRICE --}}
            <td class="px-4 py-3 font-bold text-emerald-700 whitespace-nowrap">

                ₹{{ number_format((float) $item->final_price, 2) }}

            </td>


            {{-- SOURCE --}}
            <td class="px-4 py-3">

                @if(($item->meta['source'] ?? null) === 'enquiry')

                    <span
                        class="text-[9px] bg-blue-50 text-blue-600
                               px-1.5 py-0.5 rounded font-bold whitespace-nowrap"
                    >
                        From Enquiry
                    </span>

                @else

                    <span
                        class="text-[9px] bg-emerald-50 text-emerald-600
                               px-1.5 py-0.5 rounded font-bold whitespace-nowrap"
                    >
                        Added
                    </span>

                @endif

            </td>

        </tr>


    @empty

        <tr>

            <td
                colspan="7"
                class="px-4 py-8 text-center text-gray-400 text-xs italic"
            >
                No hoardings on this offer
            </td>

        </tr>

    @endforelse

</tbody>


                @if(($offer->currentVersion?->items?->count() ?? 0) > 0)

                    <tfoot>

                        <tr class="bg-gray-50 border-t-2 border-gray-200">

                            <td
                                colspan="5"
                                class="px-4 py-3 font-bold text-gray-700 text-right"
                            >
                                Total
                            </td>

                            <td
                                colspan="2"
                                class="px-4 py-3 font-bold text-emerald-700"
                            >
                                ₹{{ number_format((float) $offer->price, 2) }}
                            </td>

                        </tr>

                    </tfoot>

                @endif

            </table>

        </div>

    </div>


{{-- =========================================================
    NEGOTIATION HISTORY — UI
========================================================= --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">

    {{-- HEADER --}}
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

                {{-- SEARCH --}}
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


                {{-- ACTOR FILTER --}}
                <select
                    id="history-actor-filter"
                    class="px-3 py-2 text-xs border border-gray-300 rounded-md"
                >
                    <option value="all">All Actors</option>
                    <option value="vendor">Vendor</option>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>


                {{-- VERSION FILTER --}}
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


                {{-- EXPAND / COLLAPSE --}}
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


    {{-- VERSION LIST --}}
    <div class="divide-y divide-gray-100">

        @forelse($versionDiffs as $i => $diff)

            @php

                $isVendorRole =
                    isset($isVendorView) && $isVendorView;

                $actorType =
                    $diff['actor_type'] ?? 'system';

                $actorLabel = match($actorType) {

                    'vendor' =>
                        $isVendorRole
                            ? 'You (Vendor)'
                            : ($offer->vendor->name ?? 'Vendor'),

                    'customer' =>
                        $isVendorRole
                            ? ($offer->customer->name ?? 'Customer')
                            : 'You',

                    'admin' =>
                        'Admin',

                    default =>
                        'System',
                };


                $actorClass = match($actorType) {

                    'vendor' =>
                        $isVendorRole
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-blue-50 text-blue-700',

                    'customer' =>
                        $isVendorRole
                            ? 'bg-blue-50 text-blue-700'
                            : 'bg-emerald-50 text-emerald-700',

                    'admin' =>
                        'bg-purple-50 text-purple-700',

                    default =>
                        'bg-gray-100 text-gray-600',
                };


                $isLatest =
                    $i === count($versionDiffs) - 1;


                /*
                 * Current structure ke according unchanged items.
                 * Backend ko touch nahi karna.
                 */
                $unchangedItems =
                    $diff['previous_items']
                    ?? $diff['items']
                    ?? $diff['version']->items
                    ?? collect();

            @endphp


            {{-- =====================================================
                VERSION BLOCK
            ====================================================== --}}
            <div
                class="version-block"
                data-actor="{{ $actorType }}"
                data-version="{{ $diff['version']->version_number }}"
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


                    {{-- ACTOR --}}
                    <span
                        class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $actorClass }}"
                    >
                        {{ $actorLabel }}
                    </span>


                    {{-- VERSION --}}
                    <span class="text-xs font-bold text-gray-700">
                        Version {{ $diff['version']->version_number }}
                    </span>


                    {{-- DATE --}}
                    <span class="text-[10px] text-gray-400">

                        {{ optional($diff['version']->created_at)->format('d M Y, h:i A') }}

                    </span>


                    {{-- CHANGE SUMMARY --}}
                    @if(!$diff['is_initial'])

                        @if($diff['has_any_change'])

                            <span
                                class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-700"
                            >
                                {{ count($diff['added'] ?? []) }} added ·
                                {{ count($diff['removed'] ?? []) }} removed ·
                                {{ count($diff['changed'] ?? []) }} changed
                            </span>

                        @else

                            <span class="text-[10px] text-gray-400 italic">
                                No hoarding changes
                            </span>

                        @endif

                    @endif


                    {{-- TOTAL --}}
                    <span class="ml-auto text-xs font-bold text-gray-700">

                        ₹{{ number_format((float) ($diff['total_amount'] ?? 0), 2) }}

                    </span>

                </button>


                {{-- =================================================
                    VERSION BODY
                ================================================== --}}
                <div
                    id="version-body-{{ $diff['version']->id }}"
                    class="version-body px-6 pb-5 {{ $isLatest ? '' : 'hidden' }}"
                >


                    {{-- =================================================
                        INITIAL VERSION
                    ================================================= --}}
                    @if($diff['is_initial'])

                        <p class="text-xs text-gray-500 mb-3">

                            Initial offer created with
                            {{ $diff['item_count'] ?? count($diff['added'] ?? []) }}
                            hoarding(s):

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

                                    @foreach(($diff['added'] ?? []) as $item)

                                        @php

                                            $historyHoarding =
                                                $item->hoarding
                                                ?? \App\Models\Hoarding::find($item->hoarding_id);

                                            $historyImage =
                                                $historyHoarding?->heroImage();

                                        @endphp


                                        <tr class="hover:bg-gray-50">

                                            {{-- HOARDING --}}
                                            <td class="px-4 py-3">

                                                <div class="flex items-center gap-3">

                                                    <div
                                                        class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0"
                                                    >

                                                        @if($historyImage)

                                                            <img
                                                                src="{{ $historyImage }}"
                                                                alt="{{ $historyHoarding?->title ?? 'Hoarding' }}"
                                                                class="w-full h-full object-cover"
                                                                loading="lazy"
                                                            >

                                                        @else

                                                            <div class="w-full h-full flex items-center justify-center text-gray-400">

                                                                <i class="fas fa-image text-sm"></i>

                                                            </div>

                                                        @endif

                                                    </div>


                                                    <div class="min-w-0">

                                                        <p class="font-semibold text-gray-800 truncate">

                                                            {{ $historyHoarding?->title ?? 'Hoarding #' . $item->hoarding_id }}

                                                        </p>

                                                        <p class="text-[10px] text-gray-400">

                                                            {{ $historyHoarding?->city ?? '' }}

                                                        </p>

                                                    </div>

                                                </div>

                                            </td>


                                            {{-- TYPE --}}
                                            <td class="px-4 py-3">

                                                <span
                                                    class="text-[10px] font-bold px-1.5 py-0.5 rounded
                                                    {{ $item->hoarding_type === 'dooh'
                                                        ? 'bg-purple-50 text-purple-600'
                                                        : 'bg-blue-50 text-blue-600' }}"
                                                >

                                                    {{ strtoupper($item->hoarding_type ?? 'OOH') }}

                                                </span>

                                            </td>


                                            {{-- START --}}
                                            <td class="px-4 py-3">

                                                {{ optional($item->start_date)->format('d M Y') }}

                                            </td>


                                            {{-- END --}}
                                            <td class="px-4 py-3">

                                                {{ optional($item->end_date)->format('d M Y') }}

                                            </td>


                                            {{-- DURATION --}}
                                            <td class="px-4 py-3">

                                                {{ $item->duration_months }}

                                                month{{ $item->duration_months != 1 ? 's' : '' }}

                                            </td>


                                            {{-- PRICE --}}
                                            <td class="px-4 py-3 font-bold text-emerald-700">

                                                ₹{{ number_format((float) $item->final_price, 2) }}

                                            </td>


                                            {{-- STATUS --}}
                                            <td class="px-4 py-3">

                                                <span
                                                    class="text-[9px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-bold"
                                                >
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

                                                    $historyHoarding =
                                                        $item->hoarding
                                                        ?? \App\Models\Hoarding::find($item->hoarding_id);

                                                    $historyImage =
                                                        $historyHoarding?->heroImage();

                                                @endphp


                                                <tr class="hover:bg-emerald-50/30">

                                                    <td class="px-4 py-3">

                                                        <div class="flex items-center gap-3">

                                                            <div
                                                                class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0"
                                                            >

                                                                @if($historyImage)

                                                                    <img
                                                                        src="{{ $historyImage }}"
                                                                        alt="{{ $historyHoarding?->title ?? 'Hoarding' }}"
                                                                        class="w-full h-full object-cover"
                                                                        loading="lazy"
                                                                    >

                                                                @else

                                                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                                        <i class="fas fa-image text-sm"></i>
                                                                    </div>

                                                                @endif

                                                            </div>


                                                            <div class="min-w-0">

                                                                <p class="font-semibold text-gray-800 truncate">

                                                                    {{ $historyHoarding?->title ?? 'Hoarding #' . $item->hoarding_id }}

                                                                </p>

                                                                <p class="text-[10px] text-gray-400">

                                                                    {{ $historyHoarding?->city ?? '' }}

                                                                </p>

                                                            </div>

                                                        </div>

                                                    </td>


                                                    <td class="px-4 py-3">

                                                        <span
                                                            class="text-[10px] font-bold px-1.5 py-0.5 rounded
                                                            {{ $item->hoarding_type === 'dooh'
                                                                ? 'bg-purple-50 text-purple-600'
                                                                : 'bg-blue-50 text-blue-600' }}"
                                                        >

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
                                                        month{{ $item->duration_months != 1 ? 's' : '' }}

                                                    </td>


                                                    <td class="px-4 py-3 font-bold text-emerald-700">

                                                        ₹{{ number_format((float) $item->final_price, 2) }}

                                                    </td>


                                                    <td class="px-4 py-3">

                                                        <span
                                                            class="text-[9px] bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded font-bold"
                                                        >
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

                                                    $historyHoarding =
                                                        $item->hoarding
                                                        ?? \App\Models\Hoarding::find($item->hoarding_id);

                                                    $historyImage =
                                                        $historyHoarding?->heroImage();

                                                @endphp


                                                <tr class="hover:bg-red-50/30">

                                                    <td class="px-4 py-3">

                                                        <div class="flex items-center gap-3">

                                                            <div
                                                                class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0"
                                                            >

                                                                @if($historyImage)

                                                                    <img
                                                                        src="{{ $historyImage }}"
                                                                        alt="{{ $historyHoarding?->title ?? 'Hoarding' }}"
                                                                        class="w-full h-full object-cover"
                                                                        loading="lazy"
                                                                    >

                                                                @else

                                                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                                        <i class="fas fa-image text-sm"></i>
                                                                    </div>

                                                                @endif

                                                            </div>


                                                            <div class="min-w-0">

                                                                <p class="font-semibold text-gray-800 truncate line-through">

                                                                    {{ $historyHoarding?->title ?? 'Hoarding #' . $item->hoarding_id }}

                                                                </p>

                                                                <p class="text-[10px] text-gray-400">

                                                                    {{ $historyHoarding?->city ?? '' }}

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
                                                        month{{ $item->duration_months != 1 ? 's' : '' }}

                                                    </td>


                                                    <td class="px-4 py-3 font-bold text-red-600">

                                                        ₹{{ number_format((float) $item->final_price, 2) }}

                                                    </td>


                                                    <td class="px-4 py-3">

                                                        <span
                                                            class="text-[9px] bg-red-50 text-red-600 px-1.5 py-0.5 rounded font-bold"
                                                        >
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

                                                    $cur =
                                                        $change['current'];

                                                    $prev =
                                                        $change['previous'];


                                                    $historyHoarding =
                                                        $cur->hoarding
                                                        ?? \App\Models\Hoarding::find($cur->hoarding_id);

                                                    $historyImage =
                                                        $historyHoarding?->heroImage();


                                                    $dateChanged =

                                                        optional($prev->start_date)->format('Y-m-d')
                                                        !==
                                                        optional($cur->start_date)->format('Y-m-d')

                                                        ||

                                                        optional($prev->end_date)->format('Y-m-d')
                                                        !==
                                                        optional($cur->end_date)->format('Y-m-d');


                                                    $priceChanged =

                                                        round((float) $prev->final_price, 2)
                                                        !==
                                                        round((float) $cur->final_price, 2);

                                                @endphp


                                                <tr class="hover:bg-amber-50/30">


                                                    {{-- HOARDING --}}
                                                    <td class="px-4 py-3">

                                                        <div class="flex items-center gap-3">

                                                            <div
                                                                class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0"
                                                            >

                                                                @if($historyImage)

                                                                    <img
                                                                        src="{{ $historyImage }}"
                                                                        alt="{{ $historyHoarding?->title ?? 'Hoarding' }}"
                                                                        class="w-full h-full object-cover"
                                                                        loading="lazy"
                                                                    >

                                                                @else

                                                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                                        <i class="fas fa-image text-sm"></i>
                                                                    </div>

                                                                @endif

                                                            </div>


                                                            <div class="min-w-0">

                                                                <p class="font-semibold text-gray-800 truncate">

                                                                    {{ $historyHoarding?->title ?? 'Hoarding #' . $cur->hoarding_id }}

                                                                </p>

                                                                <p class="text-[10px] text-gray-400">

                                                                    {{ $historyHoarding?->city ?? '' }}

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

                                                            <span
                                                                class="text-[9px] bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded font-bold"
                                                            >
                                                                Changed
                                                            </span>

                                                        @else

                                                            <span
                                                                class="text-[9px] bg-gray-50 text-gray-400 px-1.5 py-0.5 rounded font-bold"
                                                            >
                                                                No Change
                                                            </span>

                                                        @endif

                                                    </td>


                                                    {{-- PRICE CHANGE --}}
                                                    <td class="px-4 py-3">

                                                        @if($priceChanged)

                                                            <span
                                                                class="text-[9px] bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded font-bold"
                                                            >

                                                                ₹{{ number_format((float) $prev->final_price, 2) }}

                                                                →

                                                                ₹{{ number_format((float) $cur->final_price, 2) }}

                                                            </span>

                                                        @else

                                                            <span
                                                                class="text-[9px] bg-gray-50 text-gray-400 px-1.5 py-0.5 rounded font-bold"
                                                            >
                                                                No Change
                                                            </span>

                                                        @endif

                                                    </td>


                                                    {{-- STATUS --}}
                                                    <td class="px-4 py-3">

                                                        <span
                                                            class="text-[9px] bg-amber-50 text-amber-600 px-1.5 py-0.5 rounded font-bold"
                                                        >
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
                        @if(!$diff['has_any_change'])

                            <div class="mb-5">

                                <details>

                                    <summary
                                        class="text-[10px] font-semibold text-gray-400 cursor-pointer select-none mb-2"
                                    >

                                        {{ count($unchangedItems) }}
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

                                                @foreach($unchangedItems as $item)

                                                    @php

                                                        $historyHoarding =
                                                            $item->hoarding
                                                            ?? \App\Models\Hoarding::find($item->hoarding_id);

                                                        $historyImage =
                                                            $historyHoarding?->heroImage();

                                                    @endphp


                                                    <tr class="hover:bg-gray-50">


                                                        {{-- HOARDING --}}
                                                        <td class="px-4 py-3">

                                                            <div class="flex items-center gap-3">

                                                                <div
                                                                    class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0"
                                                                >

                                                                    @if($historyImage)

                                                                        <img
                                                                            src="{{ $historyImage }}"
                                                                            alt="{{ $historyHoarding?->title ?? 'Hoarding' }}"
                                                                            class="w-full h-full object-cover"
                                                                            loading="lazy"
                                                                        >

                                                                    @else

                                                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                                            <i class="fas fa-image text-sm"></i>
                                                                        </div>

                                                                    @endif

                                                                </div>


                                                                <div class="min-w-0">

                                                                    <p class="font-semibold text-gray-800 truncate">

                                                                        {{ $historyHoarding?->title ?? 'Hoarding #' . $item->hoarding_id }}

                                                                    </p>

                                                                    <p class="text-[10px] text-gray-400">

                                                                        {{ $historyHoarding?->city ?? '' }}

                                                                    </p>

                                                                </div>

                                                            </div>

                                                        </td>


                                                        {{-- TYPE --}}
                                                        <td class="px-4 py-3">

                                                            <span
                                                                class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-50 text-blue-600"
                                                            >

                                                                {{ strtoupper($item->hoarding_type ?? 'OOH') }}

                                                            </span>

                                                        </td>


                                                        {{-- START --}}
                                                        <td class="px-4 py-3">

                                                            {{ optional($item->start_date)->format('d M Y') }}

                                                        </td>


                                                        {{-- END --}}
                                                        <td class="px-4 py-3">

                                                            {{ optional($item->end_date)->format('d M Y') }}

                                                        </td>


                                                        {{-- DURATION --}}
                                                        <td class="px-4 py-3">

                                                            {{ $item->duration_months }}

                                                            month{{ $item->duration_months != 1 ? 's' : '' }}

                                                        </td>


                                                        {{-- PRICE --}}
                                                        <td class="px-4 py-3 font-bold text-gray-600">

                                                            ₹{{ number_format((float) $item->final_price, 2) }}

                                                        </td>


                                                        {{-- STATUS --}}
                                                        <td class="px-4 py-3">

                                                            <span
                                                                class="text-[9px] bg-gray-50 text-gray-500 px-1.5 py-0.5 rounded font-bold"
                                                            >
                                                                Unchanged
                                                            </span>

                                                        </td>

                                                    </tr>

                                                @endforeach

                                            </tbody>

                                        </table>

                                    </div>

                                </details>

                            </div>

                        @endif


                        {{-- NO CHANGE --}}
                        @if(
                            !$diff['has_any_change']
                            && empty($unchangedItems)
                        )

                            <p class="text-[10px] text-gray-400 italic">
                                No hoardings on this version.
                            </p>

                        @endif

                    @endif

                </div>

            </div>

        @empty

            <div class="px-6 py-10 text-center">

                <p class="text-sm text-gray-400">
                    No negotiation history available.
                </p>

            </div>

        @endforelse

    </div>

</div>

{{-- =========================================================
    REJECT MODAL
========================================================== --}}
<div
    id="customerRejectModal"
    class="fixed inset-0 z-[90] hidden flex items-center justify-center"
>

    <div
        class="absolute inset-0 bg-black/50"
        onclick="closeCustomerRejectModal()"
    ></div>


    <div class="relative bg-white rounded-xl shadow-2xl w-[92vw] max-w-md p-6">

        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 mb-4">

            <p class="text-sm font-bold text-red-700">
                ⚠ Are you sure you want to reject this offer?
            </p>

        </div>


        <p class="text-xs text-gray-500 mb-3">
            This ends the negotiation. The vendor will be notified.
        </p>


        <textarea
            id="customer-reject-reason"
            rows="3"
            class="w-full border rounded p-2 text-sm mb-4"
            placeholder="Optional: reason for rejecting"
        ></textarea>


        <div class="flex justify-end gap-3">

            <button
                type="button"
                onclick="closeCustomerRejectModal()"
                class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-md"
            >
                Cancel
            </button>


            <button
                type="button"
                id="customer-reject-confirm-btn"
                class="px-6 py-2 bg-red-600 text-white text-sm font-bold rounded-md hover:bg-red-700"
            >
                Reject Offer
            </button>

        </div>

    </div>

</div>



{{-- =========================================================
    JAVASCRIPT
========================================================== --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    window.CSRF_TOKEN =
        '{{ csrf_token() }}';

    window.CUSTOMER_ACCEPT_URL =
        '{{ route('customer.offers.accept', $offer->id) }}';

    window.CUSTOMER_REJECT_URL =
        '{{ route('customer.offers.reject', $offer->id) }}';

    window.CUSTOMER_OFFERS_INDEX_URL =
        '{{ route('customer.offers.index') }}';


    let isAccepting = false;
    let isRejecting = false;


    /*
    |--------------------------------------------------------------------------
    | ACCEPT OFFER
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('customer-accept-btn')
        ?.addEventListener('click', async function () {

            if (isAccepting) {
                return;
            }


            if (
                !confirm(
                    'Accept this offer? This will reject any other offers on this enquiry.'
                )
            ) {
                return;
            }


            isAccepting = true;


            const btn =
                document.getElementById('customer-accept-btn');


            btn.disabled = true;
            btn.innerText = 'Accepting…';


            try {

                const response =
                    await fetch(
                        window.CUSTOMER_ACCEPT_URL,
                        {
                            method: 'POST',

                            headers: {
                                'X-CSRF-TOKEN':
                                    window.CSRF_TOKEN,

                                'Accept':
                                    'application/json'
                            }
                        }
                    );


                const data =
                    await response.json();


                alert(
                    data.message ||
                    'Something went wrong.'
                );


                if (data.success) {

                    window.location.reload();

                }

            } catch (error) {

                console.error(error);

                alert(
                    'Something went wrong. Please try again.'
                );

            } finally {

                btn.disabled = false;
                btn.innerText = 'Accept Offer';

                isAccepting = false;

            }

        });



    /*
    |--------------------------------------------------------------------------
    | REJECT MODAL
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('customer-reject-btn')
        ?.addEventListener('click', function () {

            document
                .getElementById('customerRejectModal')
                ?.classList.remove('hidden');

        });


    window.closeCustomerRejectModal = function () {

        document
            .getElementById('customerRejectModal')
            ?.classList.add('hidden');

    };


    /*
    |--------------------------------------------------------------------------
    | REJECT OFFER
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('customer-reject-confirm-btn')
        ?.addEventListener('click', async function () {

            if (isRejecting) {
                return;
            }


            isRejecting = true;


            const btn =
                document.getElementById(
                    'customer-reject-confirm-btn'
                );


            btn.disabled = true;
            btn.innerText = 'Rejecting…';


            try {

                const reason =
                    document
                        .getElementById(
                            'customer-reject-reason'
                        )
                        ?.value
                        .trim() || '';


                const response =
                    await fetch(
                        window.CUSTOMER_REJECT_URL,
                        {
                            method: 'POST',

                            headers: {
                                'X-CSRF-TOKEN':
                                    window.CSRF_TOKEN,

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json'
                            },

                            body:
                                JSON.stringify({
                                    reason: reason
                                })
                        }
                    );


                const data =
                    await response.json();


                alert(
                    data.message ||
                    'Something went wrong.'
                );


                if (data.success) {

                    window.location.href =
                        window.CUSTOMER_OFFERS_INDEX_URL;

                }

            } catch (error) {

                console.error(error);

                alert(
                    'Something went wrong. Please try again.'
                );

            } finally {

                btn.disabled = false;
                btn.innerText = 'Reject Offer';

                isRejecting = false;

            }

        });



    /*
    |--------------------------------------------------------------------------
    | HOARDING SEARCH + FILTER
    |--------------------------------------------------------------------------
    */

    const hoardingSearch =
        document.getElementById('hoardingSearch');

    const hoardingFilterBtn =
        document.getElementById('hoardingFilterBtn');

    const hoardingFilterDropdown =
        document.getElementById(
            'hoardingFilterDropdown'
        );

    const customDateFields =
        document.getElementById(
            'customDateFields'
        );

    const filterStartDate =
        document.getElementById(
            'filterStartDate'
        );

    const filterEndDate =
        document.getElementById(
            'filterEndDate'
        );

    const applyCustomDateFilter =
        document.getElementById(
            'applyCustomDateFilter'
        );


    const hoardingRows =
        document.querySelectorAll(
            'table tbody tr.hoarding-row'
        );


    let enquiryFilter =
        'all';

    let customEnquiryStart =
        null;

    let customEnquiryEnd =
        null;


    /*
    |--------------------------------------------------------------------------
    | OPEN FILTER
    |--------------------------------------------------------------------------
    */

    hoardingFilterBtn
        ?.addEventListener('click', function (event) {

            event.preventDefault();
            event.stopPropagation();

            hoardingFilterDropdown
                ?.classList.toggle('hidden');

        });


    /*
    |--------------------------------------------------------------------------
    | DROPDOWN CLICK
    |--------------------------------------------------------------------------
    */

    hoardingFilterDropdown
        ?.addEventListener('click', function (event) {

            event.stopPropagation();

        });


    /*
    |--------------------------------------------------------------------------
    | OUTSIDE CLICK
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function () {

        hoardingFilterDropdown
            ?.classList.add('hidden');

    });


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    hoardingSearch
        ?.addEventListener('input', function () {

            applyHoardingFilters();

        });


    /*
    |--------------------------------------------------------------------------
    | RADIO
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.enquiry-date-filter')
        .forEach(function (radio) {

            radio.addEventListener('change', function () {

                enquiryFilter =
                    this.value;


                if (
                    enquiryFilter === 'custom'
                ) {

                    customDateFields
                        ?.classList.remove('hidden');

                    return;

                }


                customDateFields
                    ?.classList.add('hidden');


                customEnquiryStart =
                    null;

                customEnquiryEnd =
                    null;


                applyHoardingFilters();

            });

        });


    /*
    |--------------------------------------------------------------------------
    | CUSTOM DATE
    |--------------------------------------------------------------------------
    */

    applyCustomDateFilter
        ?.addEventListener('click', function () {

            const start =
                filterStartDate?.value;

            const end =
                filterEndDate?.value;


            if (!start || !end) {

                alert(
                    'Please select both Start Date and End Date.'
                );

                return;

            }


            if (start > end) {

                alert(
                    'Start Date cannot be greater than End Date.'
                );

                return;

            }


            customEnquiryStart =
                start;

            customEnquiryEnd =
                end;


            applyHoardingFilters();


            hoardingFilterDropdown
                ?.classList.add('hidden');

        });


    /*
    |--------------------------------------------------------------------------
    | APPLY HOARDING FILTER
    |--------------------------------------------------------------------------
    */

    function applyHoardingFilters() {

        const searchValue =
            hoardingSearch
                ?.value
                .toLowerCase()
                .trim() || '';


        const today =
            new Date();

        today.setHours(
            23,
            59,
            59,
            999
        );


        let startDate =
            null;

        let endDate =
            today;


        /*
        |--------------------------------------------------------------------------
        | LAST WEEK
        |--------------------------------------------------------------------------
        */

        if (
            enquiryFilter === 'last_week'
        ) {

            startDate =
                new Date(today);


            startDate.setDate(
                today.getDate() - 7
            );


            startDate.setHours(
                0,
                0,
                0,
                0
            );

        }


        /*
        |--------------------------------------------------------------------------
        | LAST MONTH
        |--------------------------------------------------------------------------
        */

        else if (
            enquiryFilter === 'last_month'
        ) {

            startDate =
                new Date(today);


            startDate.setMonth(
                today.getMonth() - 1
            );


            startDate.setHours(
                0,
                0,
                0,
                0
            );

        }


        /*
        |--------------------------------------------------------------------------
        | LAST YEAR
        |--------------------------------------------------------------------------
        */

        else if (
            enquiryFilter === 'last_year'
        ) {

            startDate =
                new Date(today);


            startDate.setFullYear(
                today.getFullYear() - 1
            );


            startDate.setHours(
                0,
                0,
                0,
                0
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CUSTOM
        |--------------------------------------------------------------------------
        */

        else if (
            enquiryFilter === 'custom'
            &&
            customEnquiryStart
            &&
            customEnquiryEnd
        ) {

            startDate =
                new Date(
                    customEnquiryStart +
                    'T00:00:00'
                );


            endDate =
                new Date(
                    customEnquiryEnd +
                    'T23:59:59'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | CHECK ROWS
        |--------------------------------------------------------------------------
        */

        hoardingRows.forEach(function (row) {

            const searchText =
                (
                    row.dataset.search ||
                    row.innerText ||
                    ''
                ).toLowerCase();


            const enquiryDate =
                row.dataset.enquiryDate ||
                '';


            /*
            | Search
            */

            const matchesSearch =
                searchValue === ''
                ||
                searchText.includes(
                    searchValue
                );


            /*
            | Date
            */

            let matchesDate =
                true;


            if (
                enquiryFilter !== 'all'
            ) {

                if (!enquiryDate) {

                    matchesDate =
                        false;

                } else {

                    const rowDate =
                        new Date(
                            enquiryDate +
                            'T00:00:00'
                        );


                    matchesDate =
                        rowDate >= startDate
                        &&
                        rowDate <= endDate;

                }

            }


            /*
            | Final
            */

            row.style.display =
                matchesSearch &&
                matchesDate
                    ? ''
                    : 'none';

        });

    }


    /*
    |--------------------------------------------------------------------------
    | INITIAL HOARDING FILTER
    |--------------------------------------------------------------------------
    */

    applyHoardingFilters();



    /*
    |--------------------------------------------------------------------------
    | VERSION HISTORY SEARCH + FILTER
    |--------------------------------------------------------------------------
    */

    const versionSearch =
        document.getElementById(
            'versionHistorySearch'
        );

    const versionFilterBtn =
        document.getElementById(
            'versionFilterBtn'
        );

    const versionDropdown =
        document.getElementById(
            'versionFilterDropdown'
        );

    const versionCustomFields =
        document.getElementById(
            'versionCustomDateFields'
        );

    const versionStartDate =
        document.getElementById(
            'versionStartDate'
        );

    const versionEndDate =
        document.getElementById(
            'versionEndDate'
        );

    const applyVersionCustomDate =
        document.getElementById(
            'applyVersionCustomDate'
        );


    let versionFilter =
        'all';

    let customVersionStart =
        null;

    let customVersionEnd =
        null;


    /*
    |--------------------------------------------------------------------------
    | OPEN VERSION FILTER
    |--------------------------------------------------------------------------
    */

    versionFilterBtn
        ?.addEventListener('click', function (event) {

            event.preventDefault();
            event.stopPropagation();

            versionDropdown
                ?.classList.toggle('hidden');

        });


    /*
    |--------------------------------------------------------------------------
    | DROPDOWN CLICK
    |--------------------------------------------------------------------------
    */

    versionDropdown
        ?.addEventListener('click', function (event) {

            event.stopPropagation();

        });


    /*
    |--------------------------------------------------------------------------
    | OUTSIDE CLICK
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function () {

        versionDropdown
            ?.classList.add('hidden');

    });


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    versionSearch
        ?.addEventListener('input', function () {

            applyVersionFilters();

        });


    /*
    |--------------------------------------------------------------------------
    | RADIO
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.version-date-filter')
        .forEach(function (radio) {

            radio.addEventListener('change', function () {

                versionFilter =
                    this.value;


                if (
                    versionFilter === 'custom'
                ) {

                    versionCustomFields
                        ?.classList.remove('hidden');

                    return;

                }


                versionCustomFields
                    ?.classList.add('hidden');


                customVersionStart =
                    null;

                customVersionEnd =
                    null;


                applyVersionFilters();

            });

        });


    /*
    |--------------------------------------------------------------------------
    | CUSTOM VERSION DATE
    |--------------------------------------------------------------------------
    */

    applyVersionCustomDate
        ?.addEventListener('click', function () {

            const start =
                versionStartDate?.value;

            const end =
                versionEndDate?.value;


            if (!start || !end) {

                alert(
                    'Please select both Start Date and End Date.'
                );

                return;

            }


            if (start > end) {

                alert(
                    'Start Date cannot be greater than End Date.'
                );

                return;

            }


            customVersionStart =
                start;

            customVersionEnd =
                end;


            applyVersionFilters();


            versionDropdown
                ?.classList.add('hidden');

        });


    /*
    |--------------------------------------------------------------------------
    | APPLY VERSION FILTER
    |--------------------------------------------------------------------------
    */

    function applyVersionFilters() {

        const searchValue =
            versionSearch
                ?.value
                .toLowerCase()
                .trim() || '';


        const versionBlocks =
            document.querySelectorAll(
                '.version-block'
            );


        const today =
            new Date();

        today.setHours(
            23,
            59,
            59,
            999
        );


        let startDate =
            null;

        let endDate =
            today;


        /*
        |--------------------------------------------------------------------------
        | LAST WEEK
        |--------------------------------------------------------------------------
        */

        if (
            versionFilter === 'last_week'
        ) {

            startDate =
                new Date(today);


            startDate.setDate(
                today.getDate() - 7
            );


            startDate.setHours(
                0,
                0,
                0,
                0
            );

        }


        /*
        |--------------------------------------------------------------------------
        | LAST MONTH
        |--------------------------------------------------------------------------
        */

        else if (
            versionFilter === 'last_month'
        ) {

            startDate =
                new Date(today);


            startDate.setMonth(
                today.getMonth() - 1
            );


            startDate.setHours(
                0,
                0,
                0,
                0
            );

        }


        /*
        |--------------------------------------------------------------------------
        | LAST YEAR
        |--------------------------------------------------------------------------
        */

        else if (
            versionFilter === 'last_year'
        ) {

            startDate =
                new Date(today);


            startDate.setFullYear(
                today.getFullYear() - 1
            );


            startDate.setHours(
                0,
                0,
                0,
                0
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CUSTOM
        |--------------------------------------------------------------------------
        */

        else if (
            versionFilter === 'custom'
            &&
            customVersionStart
            &&
            customVersionEnd
        ) {

            startDate =
                new Date(
                    customVersionStart +
                    'T00:00:00'
                );


            endDate =
                new Date(
                    customVersionEnd +
                    'T23:59:59'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | CHECK VERSIONS
        |--------------------------------------------------------------------------
        */

        versionBlocks.forEach(function (block) {

            const blockText =
                (
                    block.innerText ||
                    ''
                ).toLowerCase();


            const versionDate =
                block.dataset.versionDate ||
                '';


            /*
            | Search
            */

            const matchesSearch =
                searchValue === ''
                ||
                blockText.includes(
                    searchValue
                );


            /*
            | Date
            */

            let matchesDate =
                true;


            if (
                versionFilter !== 'all'
            ) {

                if (!versionDate) {

                    matchesDate =
                        false;

                } else {

                    const rowDate =
                        new Date(
                            versionDate +
                            'T00:00:00'
                        );


                    matchesDate =
                        rowDate >= startDate
                        &&
                        rowDate <= endDate;

                }

            }


            /*
            | Final
            */

            block.style.display =
                matchesSearch &&
                matchesDate
                    ? ''
                    : 'none';

        });

    }


    /*
    |--------------------------------------------------------------------------
    | INITIAL VERSION FILTER
    |--------------------------------------------------------------------------
    */

    applyVersionFilters();

});



/*
|--------------------------------------------------------------------------
| VERSION EXPAND / COLLAPSE
|--------------------------------------------------------------------------
*/

document.querySelectorAll('.version-toggle')
    .forEach(function (btn) {

        btn.addEventListener('click', function () {

            const body =
                document.getElementById(
                    btn.dataset.target
                );

            const chevron =
                btn.querySelector(
                    '[data-chevron]'
                );


            if (!body) {
                return;
            }


            body.classList.toggle(
                'hidden'
            );


            chevron?.classList.toggle(
                '-rotate-90'
            );

        });

    });



/*
|--------------------------------------------------------------------------
| EXPAND ALL
|--------------------------------------------------------------------------
*/

function expandAllVersions() {

    document
        .querySelectorAll('.version-body')
        .forEach(function (el) {

            el.classList.remove(
                'hidden'
            );

        });


    document
        .querySelectorAll('[data-chevron]')
        .forEach(function (el) {

            el.classList.remove(
                '-rotate-90'
            );

        });

}
/*
|--------------------------------------------------------------------------
| COLLAPSE ALL
|--------------------------------------------------------------------------
*/

function collapseAllVersions() {

    document
        .querySelectorAll('.version-body')
        .forEach(function (el) {

            el.classList.add(
                'hidden'
            );

        });


    document
        .querySelectorAll('[data-chevron]')
        .forEach(function (el) {

            el.classList.add(
                '-rotate-90'
            );

        });

}

</script>

@endsection
