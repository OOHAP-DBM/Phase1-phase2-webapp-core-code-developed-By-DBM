{{-- resources/views/customer/offers/components/offer-preview.blade.php --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

    <div class="px-6 py-4 border-b flex items-center justify-between">
        <div>
            <h3 class="font-bold text-gray-800 text-lg">Review Your Changes</h3>
            <p class="text-xs text-gray-400">Confirm before sending to the vendor.</p>
        </div>
        <button type="button" onclick="backToSelection()" class="text-gray-400 hover:text-gray-700 text-lg">✕</button>
    </div>

    <div class="px-6 py-4 border-b grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        <div>
            <p class="font-semibold text-gray-700 mb-1">Vendor</p>

            <span id="op-vendor-name">—</span>
        </div>
        <div>
            <p class="font-semibold text-gray-700 mb-1">Summary</p>
            <p>Total Hoardings: <span id="op-total">0</span> | OOH <span id="op-ooh-count">0</span> | DOOH <span id="op-dooh-count">0</span></p>
        </div>
    </div>

    <div class="px-6 py-4 max-h-[55vh] overflow-y-auto">
        <p class="font-semibold text-gray-800 mb-2">Total Hoardings (<span id="op-total-2">0</span>)</p>

        <div class="border rounded-lg overflow-hidden mb-4">
            <div class="bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-600">OOH <span id="op-ooh-count-2">0</span></div>
            <table class="w-full text-xs">
                <thead class="bg-gray-50 text-gray-500">
                    <tr><th class="px-3 py-2 text-left">Sn</th><th class="px-3 py-2 text-left">Hoardings</th><th class="px-3 py-2 text-left">Rental</th><th class="px-3 py-2 text-left">Duration</th><th class="px-3 py-2 text-left">Final Price</th></tr>
                </thead>
                <tbody id="op-ooh-rows"></tbody>
            </table>
        </div>

        <div class="border rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-600">Digital-DOOH <span id="op-dooh-count-2">0</span></div>
            <table class="w-full text-xs">
                <thead class="bg-gray-50 text-gray-500">
                    <tr><th class="px-3 py-2 text-left">Sn</th><th class="px-3 py-2 text-left">Hoardings</th><th class="px-3 py-2 text-left">Rental</th><th class="px-3 py-2 text-left">Slot</th><th class="px-3 py-2 text-left">Duration</th><th class="px-3 py-2 text-left">Final Price</th></tr>
                </thead>
                <tbody id="op-dooh-rows"></tbody>
            </table>
        </div>
    </div>

    <div class="px-6 py-4 border-t flex justify-end">
        <button id="op-confirm-btn" type="button"
            class="min-h-[44px] px-6 bg-[#2E5B42] text-white font-bold rounded-md hover:bg-opacity-90">
            Confirm &amp; Send to Vendor
        </button>
    </div>
</div>
