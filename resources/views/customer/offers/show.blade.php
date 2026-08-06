@extends('layouts.customer')

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
                        $customerTurn = $offer->canAccept() && $offer->wasLastModifiedByVendor();
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

    {{-- FINAL OFFER — current version, date-wise --}}
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

    {{-- VERSION HISTORY — who added/removed/changed what, at each step --}}
    {{-- <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b">
            <h3 class="font-bold text-gray-800 text-sm">Negotiation History</h3>
            <p class="text-xs text-gray-400 mt-0.5">Every change, and who made it.</p>
        </div>

        <div class="divide-y divide-gray-100">
            @foreach($versionDiffs as $diff)
            @php
                $actorLabel = match($diff['actor_type']) {
                    'vendor'   => $offer->vendor->name ?? 'Vendor',
                    'customer' => 'You',
                    'admin'    => 'Admin',
                    default    => 'System',
                };
                $actorClass = match($diff['actor_type']) {
                    'vendor'   => 'bg-blue-50 text-blue-700',
                    'customer' => 'bg-emerald-50 text-emerald-700',
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
                    <p class="text-xs text-gray-500 mb-2">Initial offer created with {{ $diff['item_count'] }} hoarding(s):</p>
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

{{-- Reject confirm modal --}}
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
@endsection
