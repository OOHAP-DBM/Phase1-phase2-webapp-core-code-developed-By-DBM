{{-- @extends('layouts.vendor')

@section('title', 'Create Offer')
@section('content')
<div class="px-6 py-6 bg-gray-50">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7">
            @include('vendor.offers.components.offer-form')
        </div>
        <div class="lg:col-span-5">
            @include('vendor.offers.components.offer-inventory')
        </div>
    </div>
    <div id="preview-screen" class="hidden animate-fade-in">
        @include('vendor.offers.components.offer-preview')
    </div>
</div>
@endsection --}}
@extends('layouts.vendor')

@section('title', 'Create Offer')
@section('content')
<div class="px-6 py-6 bg-gray-50">

    <div id="selection-screen" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7">
            @include('vendor.offers.components.offer-form')
        </div>
        <div class="lg:col-span-5">
            @include('vendor.offers.components.offer-inventory')
        </div>
    </div>

    <div id="preview-screen" class="hidden animate-fade-in">
        @include('vendor.offers.components.offer-preview')
    </div>

</div>
{{-- Confirm Offer modal --}}
<div id="offerConfirmModal" class="fixed inset-0 z-[90] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeOfferConfirm()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-[92vw] max-w-md p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-2">Confirm Offer</h3>
        <p class="text-sm text-gray-600 mb-3">Are you sure you want to send this offer to customer?</p>
        <p class="text-xs font-semibold text-gray-700 mb-1">Once Confirmed</p>
        <ul class="text-xs text-gray-500 list-disc pl-4 space-y-0.5 mb-6">
            <li>Offer will send to the customer</li>
            <li>Customer can modify this offer</li>
        </ul>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeOfferConfirm()" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-md">Cancel</button>
            <button type="button" id="offer-confirm-yes-btn" class="px-6 py-2 bg-[#2E5B42] text-white text-sm font-bold rounded-md hover:bg-opacity-90">Yes</button>
        </div>
    </div>
</div>

{{-- Success popup --}}
<div id="offerSuccessModal" class="fixed inset-0 z-[90] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-[92vw] max-w-md overflow-hidden">
        <div class="h-2 bg-emerald-100"></div>
        <div class="px-6 py-8 text-center">
            <div class="mx-auto w-20 h-20 rounded-full border-4 border-emerald-500 flex items-center justify-center mb-4">
                <svg class="w-9 h-9 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.879 6.196 9 9 0 015.12 17.804z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                </svg>
            </div>
            <p class="text-emerald-600 font-bold text-lg mb-2">Offer ID: #<span id="success-offer-id"></span></p>
            <p class="text-sm text-gray-600 mb-1">We are delighted to inform you that you have successfully sent the <span class="text-emerald-600 font-semibold">offer</span> to the customer</p>
            <p class="text-xs text-gray-400 mb-6">You will get notified from customer shortly!</p>
            <a id="success-manage-offers-btn" href="#" class="block w-full py-3 bg-gray-900 text-white text-sm font-bold rounded-md hover:bg-black">Go to manage offers</a>
        </div>
    </div>
</div>

<style>
.animate-fade-in{animation:fadeIn .3s ease-out forwards}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
</style>

@php
    $seed = [];
    if (isset($enquiry) && $enquiry) {
        foreach ($enquiry->items as $it) {
            $h = $it->hoarding ?? null;
            if (!$h) continue;
            $seed[] = [
                'hoarding_id'         => $h->id,
                'enquiry_item_id'     => $it->id,
                'title'               => $h->title ?? $h->address,
                'city'                => $h->city,
                'location'            => $h->address,
                'hoarding_type'       => $it->hoarding_type,
                'price_per_month'     => (float) ($it->meta['pricing_display']['price'] ?? 0),
                'image_url'           => $it->image_url ?? null,
                'startDate'           => optional($it->preferred_start_date)->format('Y-m-d'),
                'endDate'             => optional($it->preferred_end_date)->format('Y-m-d'),
                'total_slots_per_day' => $h->doohScreen->total_slots_per_day ?? 300,
                'source'              => 'enquiry',
            ];
        }
    }

    // Build this as a plain PHP variable first — passing an inline
    // ternary + array literal straight into @json() trips Blade's
    // directive parser and produces a misleading error elsewhere.
    $offerCustomer = $enquiry ? [
        'name'     => $enquiry->customer->name ?? 'N/A',
        'business' => $enquiry->customer->posProfile->business_name ?? null,
        'gstin'    => $enquiry->customer->gstin ?? null,
        'mobile'   => $enquiry->contact_number,
        'email'    => $enquiry->customer->email ?? null,
        'address'  => $enquiry->customer->address ?? null,
    ] : [];
@endphp

<script>
window.CSRF_TOKEN               = '{{ csrf_token() }}';
window.OFFER_STORE_URL          = '{{ route('vendor.offers.store') }}';
window.HOARDINGS_API            = '/vendor/pos/api/hoardings';
window.ENQUIRY_ID               = {{ $enquiry->id ?? 'null' }};
window.OFFER_CUSTOMER           = @json($offerCustomer);
// window.OFFER_SEED_ITEMS         = @json($seed);

window.OFFER_SEED_ITEMS = @json(!empty($seedFromOffer) ? $seedFromOffer : $seed);
window.EDITING_OFFER_ID = {{ $editingOffer->id ?? 'null' }};
window.VALID_UNTIL_DEFAULT_DAYS = 8;
</script>
<script src="{{ asset('js/offer-builder.js') }}"></script>
@endsection
