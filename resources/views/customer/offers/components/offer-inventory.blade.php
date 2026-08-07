{{-- resources/views/customer/offers/components/offer-inventory.blade.php --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 lg:sticky lg:top-4">

    <div class="px-4 sm:px-5 pt-4 sm:pt-5 flex items-center gap-3">
        <h3 class="font-bold text-gray-800 text-sm">Vendor's Available Hoardings</h3>
        <span class="bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full text-xs font-bold" id="available-count">0</span>
    </div>
    <p class="px-4 sm:px-5 text-xs text-gray-400 mt-0.5 mb-0">Browse and add more hoardings from this vendor.</p>

    <div class="p-3 sm:p-4 lg:p-5">
        <div class="relative mb-3">
            <input type="text" id="hoarding-search" placeholder="Search by name, location…"
                class="w-full pl-9 pr-3 border border-gray-300 text-xs rounded h-[38px] focus:ring-green-500">
            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-3.5-3.5"/>
                </svg>
            </span>
        </div>

        <div id="hoardings-grid"
            class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 gap-2 max-h-[520px] overflow-y-auto pr-1"></div>

        <div id="hoardings-pagination" class="flex justify-center items-center gap-1.5 mt-3 flex-wrap"></div>
    </div>
</div>

<div id="datePickerModal" class="fixed inset-0 flex items-center justify-center z-[70] hidden">
    <div class="bg-black/60 absolute inset-0" onclick="closeDatePickerModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl p-4 sm:p-5 w-[95vw] sm:max-w-[760px] z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start mb-3 gap-3">
            <div class="min-w-0">
                <h3 id="datePickerTitle" class="font-black text-gray-800 text-sm sm:text-base truncate">Select Booking Dates</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Duration auto-rounds up to nearest 30-day multiple. Minimum 30 days.</p>
            </div>
            <button onclick="closeDatePickerModal()" class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="grid grid-cols-3 gap-2 sm:gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-3 sm:px-4 py-3 mb-3">
            <div>
                <p class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider mb-0.5">Period</p>
                <p id="dp-range-label" class="text-[10px] sm:text-[11px] font-black text-emerald-900 leading-tight">— Pick a date</p>
            </div>
            <div class="text-center">
                <p class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider mb-0.5">Duration</p>
                <p id="dp-months-label" class="text-[10px] sm:text-[11px] font-black text-emerald-900">—</p>
            </div>
            <div class="text-right">
                <p class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider mb-0.5">Est. Cost</p>
                <p id="dp-cost-label" class="text-[10px] sm:text-[11px] font-black text-emerald-900">—</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 px-1 mb-3">
            <span class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500"><span class="w-3 h-3 rounded-sm bg-green-100 border border-green-300"></span>Available</span>
            <span class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500"><span class="w-3 h-3 rounded-sm bg-red-100 border border-red-300"></span>Booked</span>
            <span class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500"><span class="w-3 h-3 rounded-sm bg-gray-200 border border-gray-300"></span>Blocked</span>
            {{-- <span class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500"><span class="w-3 h-3 rounded-sm bg-amber-100 border border-amber-300"></span>On Hold</span> --}}
            {{-- <span class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500"><span class="w-3 h-3 rounded-sm bg-orange-100 border border-orange-300"></span>Partial</span> --}}
        </div>

        <input id="date-picker-input" type="text" class="hidden">
        <div id="date-picker-inline" class="w-full overflow-x-auto"></div>

        <div class="flex flex-wrap items-center gap-2 mt-3">
            <span class="text-[10px] text-gray-400 font-semibold">Quick:</span>
            <button onclick="quickSelectMonths(1)"  data-months="1"  class="dp-quick-chip px-2.5 sm:px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">1 Month</button>
            <button onclick="quickSelectMonths(2)"  data-months="2"  class="dp-quick-chip px-2.5 sm:px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">2 Months</button>
            <button onclick="quickSelectMonths(3)"  data-months="3"  class="dp-quick-chip px-2.5 sm:px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">3 Months</button>
            <button onclick="quickSelectMonths(6)"  data-months="6"  class="dp-quick-chip px-2.5 sm:px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">6 Months</button>
            <button onclick="quickSelectMonths(12)" data-months="12" class="dp-quick-chip px-2.5 sm:px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">12 Months</button>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 mt-4 pt-4 border-t border-gray-100">
            <button onclick="closeDatePickerModal()" class="w-full sm:w-auto min-h-[44px] px-5 py-2 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
            <button onclick="confirmDateSelection()" class="w-full sm:w-auto min-h-[44px] px-7 py-2 bg-[#2D5A43] text-white rounded-xl text-sm font-bold hover:bg-opacity-90 transition">✓ Confirm Dates</button>
        </div>
    </div>
</div>

<style>
.hoarding-card{transition:border-color .15s,box-shadow .15s;cursor:pointer}
.hoarding-card:hover:not(.is-selected){border-color:#6ee7b7;background:#f9fafb}
.hoarding-card.is-selected{border-color:#16a34a!important;box-shadow:0 0 0 2px #bbf7d055;background:#f0fdf4!important}
.flatpickr-day.avail-day       { background:#dcfce7!important; border-color:#86efac!important; color:#14532d!important; }
.flatpickr-day.avail-day.flatpickr-disabled { background:#f3f4f6!important; border-color:#e5e7eb!important; color:#9ca3af!important; cursor:not-allowed!important; }
.flatpickr-day.day-booked,
.flatpickr-day.day-partial  { background:#fee2e2!important; color:#991b1b!important; border-color:#fca5a5!important; cursor:not-allowed!important; text-decoration:line-through; pointer-events:none; }
.flatpickr-day.day-blocked  { background:#f3f4f6!important; color:#9ca3af!important; border-color:#e5e7eb!important; cursor:not-allowed!important; pointer-events:none; }
.flatpickr-day.day-hold     { background:#fef9c3!important; color:#78350f!important; border-color:#fde047!important; cursor:not-allowed!important; pointer-events:none; }
.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange     { background:#2D5A43!important; border-color:#2D5A43!important; color:#fff!important; }
.flatpickr-day.inRange      { background:#e5e7eb!important; border-color:#d1d5db!important; color:#1f2937!important; box-shadow:none!important; }
.dp-quick-chip.chip-active  { border-color:#059669; color:#059669; background:#ecfdf5; }
</style>
