<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-lg md:text-xl font-bold text-gray-800">Create Offer</h2>
            <p class="text-xs text-gray-400 mt-0.5">Select hoardings from your enquiry or add more, then send the offer.</p>
        </div>
        <div class="flex items-center gap-2">
            <label for="offer-valid-till" class="text-xs font-semibold text-gray-600 whitespace-nowrap">Valid till</label>
            <input id="offer-valid-till" type="date"
                class="border border-gray-300 rounded-md px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
    </div>

    <div class="p-4 sm:p-5 lg:p-6 space-y-6">
@if(isset($editingOffer) && $editingOffer->modification_notes)
<div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800 mb-4">
    <p class="font-bold mb-1">💬 Customer requested changes:</p>
    <p>{{ $editingOffer->modification_notes }}</p>
</div>
@endif
        {{-- Enquiry summary --}}
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-700 space-y-1">
            <p class="font-semibold text-gray-800 mb-1">Enquiry Details</p>
            <p><span class="font-semibold text-gray-600">Customer:</span> {{ $enquiry->customer->name ?? 'N/A' }}</p>
            <p><span class="font-semibold text-gray-600">Mobile:</span> {{ $enquiry->contact_number ?? 'N/A' }}</p>
            <p><span class="font-semibold text-gray-600">Email:</span> {{ $enquiry->customer->email ?? 'N/A' }}</p>
            <p><span class="font-semibold text-gray-600">Note:</span> {{ $enquiry->customer_note ?? 'No enquiry details available.' }}</p>
        </div>

        {{-- OOH table --}}
        <div>
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center mb-2">
                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span> OOH (Static)
                <span class="ml-auto bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-[10px] font-bold" id="ooh-count">0</span>
            </h4>
            <div class="overflow-x-auto border border-gray-100 rounded">
                <table class="min-w-[520px] w-full divide-y divide-gray-200 text-xs text-left">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-3 py-2 font-semibold">Hoarding</th>
                            <th class="px-3 py-2 font-semibold">Rental</th>
                            <th class="px-3 py-2 font-semibold">Duration</th>
                            <th class="px-3 py-2 font-semibold">Total</th>
                            <th class="px-3 py-2 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="ooh-selected-list" class="divide-y divide-gray-50 bg-white">
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 italic text-xs">No OOH hoardings selected</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- DOOH table --}}
        <div>
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center mb-2">
                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span> Digital (DOOH)
                <span class="ml-auto bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-[10px] font-bold" id="dooh-count">0</span>
            </h4>
            <div class="overflow-x-auto border border-gray-100 rounded">
                <table class="min-w-[560px] w-full divide-y divide-gray-200 text-xs text-left">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-3 py-2 font-semibold">Hoarding</th>
                            <th class="px-3 py-2 font-semibold">Rental</th>
                            <th class="px-3 py-2 font-semibold">Slots</th>
                            <th class="px-3 py-2 font-semibold">Duration</th>
                            <th class="px-3 py-2 font-semibold">Total</th>
                            <th class="px-3 py-2 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="dooh-selected-list" class="divide-y divide-gray-50 bg-white">
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400 italic text-xs">No DOOH screens selected</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Availability alert --}}
        <div id="availability-alert" class="hidden rounded-lg border border-red-200 bg-red-50 p-3">
            <h4 class="text-xs font-bold text-red-700 mb-1">Availability Conflicts Found</h4>
            <div id="availability-alert-body" class="text-[11px] text-red-600 space-y-1"></div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-2 border-t border-gray-100">
            <button type="button" onclick="location.reload()"
                class="w-full sm:flex-1 min-h-[44px] py-3 bg-gray-100 border border-gray-200 font-bold text-gray-700 rounded text-sm">
                Cancel
            </button>
            <button id="offer-preview-btn" type="button"
                class="w-full sm:flex-1 min-h-[44px] py-3 bg-[#2E5B42] text-white font-bold rounded text-sm hover:bg-opacity-90">
                Preview &amp; Send Offer (<span id="offer-selected-count">0</span>)
            </button>
        </div>
    </div>
</div>
