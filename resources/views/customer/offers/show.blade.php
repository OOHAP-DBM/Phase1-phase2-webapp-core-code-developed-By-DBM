@extends('layouts.customer')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Offer #{{ $offer->offer_number }}</h2>
                <p class="text-xs text-gray-400">From {{ $offer->vendor->name ?? 'Vendor' }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold
                {{ $offer->status === 'sent' ? 'bg-blue-50 text-blue-600' : '' }}
                {{ $offer->status === 'accepted' ? 'bg-emerald-50 text-emerald-600' : '' }}
                {{ $offer->status === 'rejected' ? 'bg-red-50 text-red-600' : '' }}">
                {{ ucfirst($offer->status) }}
            </span>
            @if($offer->version > 1)
    <p class="text-xs text-gray-400 mt-1">This is version {{ $offer->version }} — updated by the vendor.</p>
@endif
        </div>

        <div class="space-y-2 mb-6">

            @foreach($offer->currentVersion->items as $item)
            <div class="border rounded p-3 text-sm">
                <p class="font-semibold">{{ $item->hoarding->title ?? 'Hoarding' }}</p>
                <p class="text-xs text-gray-500">{{ optional($item->start_date)->format('d M Y') }} – {{ optional($item->end_date)->format('d M Y') }}</p>
                <p class="text-sm font-bold text-emerald-700">₹{{ number_format((float) $item->final_price, 2) }}</p>
            </div>
            @endforeach
        </div>

        <div class="border-t pt-4 flex justify-between items-center mb-6">
            <span class="font-bold text-gray-800">Total</span>
            <span class="font-bold text-xl text-emerald-700">₹{{ number_format((float) $offer->price, 2) }}</span>
        </div>

        @if($offer->canAccept())
        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="acceptOffer({{ $offer->id }})" class="flex-1 py-3 bg-[#2E5B42] text-white font-bold rounded-md">Accept Offer</button>
            <button onclick="openModifyDialog()" class="flex-1 py-3 border border-gray-300 text-gray-700 font-bold rounded-md">Request Changes</button>
            <button onclick="rejectOffer({{ $offer->id }})" class="flex-1 py-3 border border-red-300 text-red-600 font-bold rounded-md">Reject</button>
        </div>
        @endif
    </div>
</div>

{{-- Modify-request dialog --}}
<div id="modifyDialog" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeModifyDialog()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-[92vw] max-w-md p-6">
        <h3 class="font-bold text-gray-800 mb-2">What changes would you like?</h3>
        <textarea id="modify-notes" rows="4" class="w-full border rounded p-2 text-sm" placeholder="e.g. Please reduce the price, or change dates to..."></textarea>
        <div class="flex justify-end gap-3 mt-4">
            <button onclick="closeModifyDialog()" class="px-4 py-2 text-sm text-gray-600">Cancel</button>
            <button onclick="submitModifyRequest({{ $offer->id }})" class="px-5 py-2 bg-[#2E5B42] text-white text-sm font-bold rounded-md">Send Request</button>
        </div>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';
function openModifyDialog(){ document.getElementById('modifyDialog').classList.remove('hidden'); }
function closeModifyDialog(){ document.getElementById('modifyDialog').classList.add('hidden'); }

async function acceptOffer(id) {
    if (!confirm('Accept this offer? This will reject any other offers on this enquiry.')) return;
    const res = await fetch(`/customer/offers/${id}/accept`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' } }).then(r => r.json());
    alert(res.message);
    if (res.success) location.reload();
}
async function rejectOffer(id) {
    const reason = prompt('Optional: reason for rejecting?') || '';
    const res = await fetch(`/customer/offers/${id}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ reason }),
    }).then(r => r.json());
    alert(res.message);
    if (res.success) location.reload();
}
async function submitModifyRequest(id) {
    const notes = document.getElementById('modify-notes').value.trim();
    if (!notes) { alert('Please describe the changes you want.'); return; }
    const res = await fetch(`/customer/offers/${id}/modify`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ notes }),
    }).then(r => r.json());
    alert(res.message);
    closeModifyDialog();
}
</script>
@endsection
