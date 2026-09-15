<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

    <div class="px-6 py-4 border-b flex items-center justify-between">
        <div>
            <h3 class="font-bold text-gray-800 text-lg">Offer Preview</h3>
            <p class="text-xs text-gray-400">Send an offer for customer review.</p>
        </div>
        <button type="button" onclick="backToSelection()" class="text-gray-400 hover:text-gray-700 text-lg">✕</button>
    </div>

    <div class="px-6 py-4 border-b grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
        <div>
            <p class="font-semibold text-gray-700 mb-1">Customer Details</p>
            <p>Name: <span id="op-cust-name">—</span></p>
            <p>Business: <span id="op-cust-business">—</span></p>
            <p>GSTIN: <span id="op-cust-gstin">—</span></p>
            <p>Mobile: <span id="op-cust-mobile">—</span></p>
            <p>Email: <span id="op-cust-email">—</span></p>
            <p>Address: <span id="op-cust-address">—</span></p>
        </div>
        <div>
            <p class="font-semibold text-gray-700 mb-1">Hoarding Details</p>
            <p>Total Hoardings: <span id="op-total">0</span> | OOH <span id="op-ooh-count">0</span> | DOOH <span id="op-dooh-count">0</span></p>
            <p class="mt-1">Including Cities: <span id="op-cities" class="text-emerald-700"></span></p>
        </div>
        <div>
            <p class="font-semibold text-gray-700 mb-1">Offer Details</p>
            <p>Valid till: <span id="op-valid-till">—</span></p>
        </div>
    </div>

    <div class="px-6 py-4 max-h-[55vh] overflow-y-auto">
        <p class="font-semibold text-gray-800 mb-2">Total Hoardings (<span id="op-total-2">0</span>)</p>

        <div class="border rounded-lg overflow-hidden mb-4">
            <div class="bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-600">
                OOH <span id="op-ooh-count-2">0</span> – Selected Static Hoardings for the offer
            </div>
            <table class="w-full text-xs">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Sn</th>
                        <th class="px-3 py-2 text-left">Hoardings</th>
                        <th class="px-3 py-2 text-left">Rental</th>
                        <th class="px-3 py-2 text-left">Duration</th>
                        <th class="px-3 py-2 text-left">Final Price</th>
                    </tr>
                </thead>
                <tbody id="op-ooh-rows"></tbody>
            </table>
        </div>

        <div class="border rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-3 py-2 text-xs font-semibold text-gray-600">
                Digital-DOOH <span id="op-dooh-count-2">0</span> – Selected Digital Screens for the offer
            </div>
            <table class="w-full text-xs">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-3 py-2 text-left">Sn</th>
                        <th class="px-3 py-2 text-left">Hoardings</th>
                        <th class="px-3 py-2 text-left">Rental</th>
                        <th class="px-3 py-2 text-left">Slot</th>
                        <th class="px-3 py-2 text-left">Duration</th>
                        <th class="px-3 py-2 text-left">Final Price</th>
                    </tr>
                </thead>
                <tbody id="op-dooh-rows"></tbody>
            </table>
        </div>
    </div>

    <div class="px-6 py-4 border-t flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="text-xs">
            <span class="font-semibold text-gray-700">Where you want to send offer?</span>
            <label class="ml-3 inline-flex items-center gap-1"><input type="checkbox" id="op-send-email"> Email</label>
            <label class="ml-3 inline-flex items-center gap-1"><input type="checkbox" id="op-send-whatsapp"> Whatsapp</label>
            <p id="op-send-error" class="hidden text-red-500 mt-1">Please select at least one sending option</p>
        </div>
        <button id="op-confirm-btn" type="button"
            class="min-h-[44px] px-6 bg-[#2E5B42] text-white font-bold rounded-md hover:bg-opacity-90">
            Confirm &amp; Send offer
        </button>
    </div>
</div>

