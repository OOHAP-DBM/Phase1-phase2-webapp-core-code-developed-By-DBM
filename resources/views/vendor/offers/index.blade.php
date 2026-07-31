@extends('layouts.vendor')

@section('title', 'Manage Offers')
@section('content')
<div class="px-6 py-6 bg-gray-50">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">

        <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Manage Offers</h2>
                <p class="text-xs text-gray-400 mt-0.5">Check all your sent offers to customers, you can track and manage them here</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" id="offer-search" placeholder="Search customer by name, email, mobile number…"
                        class="w-72 pl-9 pr-3 border border-gray-300 rounded text-xs h-[38px]">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="M21 21l-3.5-3.5"/>
                    </svg>
                </div>
                <button id="filter-toggle-btn" type="button" class="border border-gray-300 bg-white px-4 h-[38px] text-xs font-semibold rounded hover:bg-gray-50">Filter</button>
            </div>
        </div>

        {{-- Filter panel --}}
        <div id="filter-panel" class="hidden border-b bg-gray-50 px-6 py-4">
            <div class="flex flex-col sm:flex-row gap-8">
                <div>
                    <p class="text-xs font-bold text-gray-700 mb-2">Offer Status</p>
                    <div class="flex flex-wrap gap-4 text-xs">
                        <label class="flex items-center gap-1.5"><input type="checkbox" class="offer-status-cb" value="all" checked> All</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" class="offer-status-cb" value="sent"> Pending</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" class="offer-status-cb" value="accepted"> Accepted</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" class="offer-status-cb" value="rejected"> Rejected</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" class="offer-status-cb" value="expired"> Expired</label>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-700 mb-2">Created Offer by date</p>
                    <div class="flex flex-wrap items-center gap-4 text-xs mb-2">
                        <label class="flex items-center gap-1.5"><input type="radio" name="date_preset" value="all" checked> All</label>
                        <label class="flex items-center gap-1.5"><input type="radio" name="date_preset" value="last_week"> Last week</label>
                        <label class="flex items-center gap-1.5"><input type="radio" name="date_preset" value="last_month"> Last month</label>
                        <label class="flex items-center gap-1.5"><input type="radio" name="date_preset" value="last_year"> Last year</label>
                        <label class="flex items-center gap-1.5"><input type="radio" name="date_preset" value="custom"> Custom Date</label>
                    </div>
                    <div id="custom-date-range" class="hidden flex items-center gap-2">
                        <input type="date" id="filter-from-date" class="border border-gray-300 rounded text-xs h-[34px] px-2">
                        <span class="text-gray-400 text-xs">to</span>
                        <input type="date" id="filter-to-date" class="border border-gray-300 rounded text-xs h-[34px] px-2">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button id="filter-reset-btn" type="button" class="px-4 py-2 text-xs font-semibold text-gray-600 border border-gray-300 rounded hover:bg-white">Reset</button>
                <button id="filter-apply-btn" type="button" class="px-5 py-2 text-xs font-bold text-white bg-[#2E5B42] rounded hover:bg-opacity-90">Apply Filter</button>
            </div>
        </div>

        <div class="px-6 pt-4">
            <p class="text-sm font-semibold text-gray-700">All Created Offers (<span id="offers-total-count">{{ $offers->total() }}</span>)</p>
        </div>

        <div class="overflow-x-auto px-2 pb-2">
            <table class="min-w-[1000px] w-full text-xs text-left">
                <thead class="text-gray-500 border-b">
                    <tr>
                        <th class="px-3 py-3 font-semibold w-8"><input type="checkbox" id="select-all-offers"></th>
                        <th class="px-3 py-3 font-semibold">Sn</th>
                        <th class="px-3 py-3 font-semibold">Offer ID</th>
                        <th class="px-3 py-3 font-semibold">Customer Name</th>
                        <th class="px-3 py-3 font-semibold"># of Hoardings</th>
                        <th class="px-3 py-3 font-semibold"># of Locations</th>
                        <th class="px-3 py-3 font-semibold">Offer Valid Till</th>
                        <th class="px-3 py-3 font-semibold">Offer Status</th>
                        <th class="px-3 py-3 font-semibold">Action</th>
                        <th class="px-3 py-3 font-semibold w-8"></th>
                    </tr>
                </thead>
                <tbody id="offers-table-body">
                    @include('vendor.offers.partials.table', ['offers' => $offers])
                </tbody>
            </table>
        </div>

        <div id="offers-pagination" class="px-6 py-4 border-t">
            @include('vendor.offers.partials.pagination', ['offers' => $offers])
        </div>

        <button type="button" onclick="toggleArchivedSection()" class="w-full text-left px-6 py-3 border-t bg-gray-50 text-sm text-gray-600 flex items-center justify-between">
            <span><span id="archived-count">{{ $archivedCount }}</span> Offer{{ $archivedCount === 1 ? '' : 's' }} are Archived</span>
            <svg id="archived-chevron" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div id="archived-section" class="hidden px-6 pb-4"></div>
    </div>
</div>

{{-- Row "..." menu --}}
<div id="row-menu" class="hidden fixed z-50 bg-white border border-gray-200 rounded-lg shadow-lg text-xs w-44">
    <button class="w-full text-left px-4 py-2.5 hover:bg-gray-50 flex items-center gap-2" onclick="rowMenuAction('view')">👁 View Offer Details</button>
    <button class="w-full text-left px-4 py-2.5 hover:bg-gray-50 flex items-center gap-2" onclick="rowMenuAction('modify')">✏️ Modify Offer</button>
    <button class="w-full text-left px-4 py-2.5 hover:bg-gray-50 flex items-center gap-2 text-red-600" onclick="rowMenuAction('archive')">🗄 Archive this Offer</button>
</div>

{{-- Archive confirm --}}
<div id="archiveConfirmModal" class="fixed inset-0 z-[90] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeArchiveConfirm()"></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-[92vw] max-w-md p-6">
        <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 mb-4">
            <p class="text-sm font-bold text-amber-800">⚠ Are you sure you want to archive this offer?</p>
        </div>
        <p class="text-xs text-gray-500 mb-1">Archiving this offer moves it out of your active list.</p>
        <p class="text-xs text-red-500 mb-6">This can be restored later from the archived section.</p>
        <div class="flex justify-end gap-3">
            <button type="button" onclick="closeArchiveConfirm()" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-md">Cancel</button>
            <button type="button" id="archive-confirm-yes-btn" class="px-6 py-2 bg-red-600 text-white text-sm font-bold rounded-md hover:bg-red-700">Proceed to Archive</button>
        </div>
    </div>
</div>

<script>
window.OFFERS_INDEX_URL = '{{ route('vendor.offers.index') }}';
window.OFFERS_ARCHIVE_URL_TEMPLATE = '{{ route('vendor.offers.archive', ['offer' => '__ID__']) }}';
window.OFFERS_UNARCHIVE_URL_TEMPLATE = '{{ route('vendor.offers.unarchive', ['offer' => '__ID__']) }}';
window.OFFERS_REMIND_URL_TEMPLATE = '{{ route('vendor.offers.remind', ['offer' => '__ID__']) }}';
window.OFFERS_CREATE_URL = '{{ route('vendor.offers.create') }}';
window.CSRF_TOKEN = '{{ csrf_token() }}';
window.OFFERS_ACCEPT_CUSTOMER_MOD_URL_TEMPLATE = '{{ route('vendor.offers.accept-customer-modification', ['offer' => '__ID__']) }}';
</script>
<script src="{{ asset('js/offers-index.js') }}"></script>
@endsection
