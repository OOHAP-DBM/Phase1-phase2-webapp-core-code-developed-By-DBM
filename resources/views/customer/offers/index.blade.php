{{-- resources/views/customer/offers/index.blade.php --}}
@extends('layouts.customer')

@section('title', 'My Offers')
@section('content')
<div class="px-6 py-6 bg-gray-50">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">

        <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-800">My Offers</h2>
                <p class="text-xs text-gray-400 mt-0.5">Offers sent to you by vendors — review, accept, or request changes.</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" id="offer-search" placeholder="Search vendor by name, email, mobile…"
                        class="w-72 pl-9 pr-3 border border-gray-300 rounded text-xs h-[38px]">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="M21 21l-3.5-3.5"/>
                    </svg>
                </div>
                <button id="filter-toggle-btn" type="button" class="border border-gray-300 bg-white px-4 h-[38px] text-xs font-semibold rounded hover:bg-gray-50">Filter</button>
            </div>
        </div>

        <div id="filter-panel" class="hidden border-b bg-gray-50 px-6 py-4">
            <p class="text-xs font-bold text-gray-700 mb-2">Offer Status</p>
            <div class="flex flex-wrap gap-4 text-xs mb-4">
                <label class="flex items-center gap-1.5"><input type="checkbox" class="offer-status-cb" value="all" checked> All</label>
                <label class="flex items-center gap-1.5"><input type="checkbox" class="offer-status-cb" value="sent"> Pending</label>
                <label class="flex items-center gap-1.5"><input type="checkbox" class="offer-status-cb" value="accepted"> Accepted</label>
                <label class="flex items-center gap-1.5"><input type="checkbox" class="offer-status-cb" value="rejected"> Rejected</label>
                <label class="flex items-center gap-1.5"><input type="checkbox" class="offer-status-cb" value="expired"> Expired</label>
            </div>
            <div class="flex justify-end gap-2">
                <button id="filter-reset-btn" type="button" class="px-4 py-2 text-xs font-semibold text-gray-600 border border-gray-300 rounded hover:bg-white">Reset</button>
                <button id="filter-apply-btn" type="button" class="px-5 py-2 text-xs font-bold text-white bg-[#2E5B42] rounded hover:bg-opacity-90">Apply Filter</button>
            </div>
        </div>

        <div class="px-6 pt-4">
            <p class="text-sm font-semibold text-gray-700">All Offers (<span id="offers-total-count">{{ $offers->total() }}</span>)</p>
        </div>

        <div class="overflow-x-auto px-2 pb-2">
            <table class="min-w-[1000px] w-full text-xs text-left">
                <thead class="text-gray-500 border-b">
                    <tr>
                        <th class="px-3 py-3 font-semibold">Sn</th>
                        <th class="px-3 py-3 font-semibold">Enquiry ID</th>
                        <th class="px-3 py-3 font-semibold">Offer ID</th>
                        <th class="px-3 py-3 font-semibold">Vendor Name</th>
                        <th class="px-3 py-3 font-semibold"># of Hoardings</th>
                        <th class="px-3 py-3 font-semibold"># of Locations</th>
                        <th class="px-3 py-3 font-semibold">Offer Valid Till</th>
                        <th class="px-3 py-3 font-semibold">Offer Status</th>
                        <th class="px-3 py-3 font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody id="offers-table-body">
                    @include('customer.offers.partials.table', ['offers' => $offers])
                </tbody>
            </table>
        </div>

        <div id="offers-pagination" class="px-6 py-4 border-t">
            @include('customer.offers.partials.pagination', ['offers' => $offers])
        </div>
    </div>
</div>

<script>
window.OFFERS_INDEX_URL = '{{ route('customer.offers.index') }}';
</script>
<script src="{{ asset('js/customer-offers-index.js') }}"></script>
@endsection
