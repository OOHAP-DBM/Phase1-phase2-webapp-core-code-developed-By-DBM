{{-- resources/views/customer/offers/modify.blade.php --}}
@extends('layouts.customer')

@section('title', 'Modify Offer')
@section('content')
<div class="px-6 py-6 bg-gray-50">

    <div id="selection-screen" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7">
            @include('customer.offers.components.offer-form')
        </div>
        <div class="lg:col-span-5">
            @include('customer.offers.components.offer-inventory')
        </div>
    </div>

    <div id="preview-screen" class="hidden animate-fade-in">
        @include('customer.offers.components.offer-preview')
    </div>

</div>

<div id="offerConfirmModal" class="fixed inset-0 z-[90] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeOfferConfirm()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-[92vw] max-w-md p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-2">Send Modified Offer</h3>
        <p class="text-sm text-gray-600 mb-6">Send these changes to {{ $offer->vendor->name ?? 'the vendor' }} for review?</p>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeOfferConfirm()" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-md">Cancel</button>
            <button type="button" id="offer-confirm-yes-btn" class="px-6 py-2 bg-[#2E5B42] text-white text-sm font-bold rounded-md hover:bg-opacity-90">Yes, Send</button>
        </div>
    </div>
</div>

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
            <p class="text-sm text-gray-600 mb-6">Your changes have been sent to the vendor. You'll be notified when they respond.</p>
            <a id="success-view-offer-btn" href="#" class="block w-full py-3 bg-gray-900 text-white text-sm font-bold rounded-md hover:bg-black">View Offer</a>
        </div>
    </div>
</div>

<style>
.animate-fade-in{animation:fadeIn .3s ease-out forwards}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
</style>
@php
    $vendorData = [
        'name'  => optional($offer->vendor)->name ?? 'N/A',
        'email' => optional($offer->vendor)->email,
        'phone' => optional($offer->vendor)->phone,
    ];
@endphp
<script>
window.CSRF_TOKEN            = '{{ csrf_token() }}';
window.OFFER_ID              = {{ $offer->id }};
window.OFFER_STORE_URL       = '{{ route('customer.offers.modify.store', $offer->id) }}';
window.OFFER_SHOW_URL        = '{{ route('customer.offers.show', $offer->id) }}';
window.HOARDINGS_API         = '{{ route('customer.offers.api.hoardings', $offer->id) }}';
window.OFFER_VENDOR = @json($vendorData);
window.OFFER_SEED_ITEMS      = @json($seed);
</script>
<script src="{{ asset('js/customer-offer-builder.js') }}"></script>
@endsection
