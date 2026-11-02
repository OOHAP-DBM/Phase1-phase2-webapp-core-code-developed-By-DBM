<div class="flex items-center justify-between text-xs text-gray-500">
    <div class="flex items-center gap-2">
        {{-- <span>Rows per page</span>
        <select id="per-page-select" class="border border-gray-300 rounded px-2 py-1">
            <option value="5" {{ $offers->perPage()==5?'selected':'' }}>05</option>
            <option value="10" {{ $offers->perPage()==10?'selected':'' }}>10</option>
            <option value="25" {{ $offers->perPage()==25?'selected':'' }}>25</option>
        </select> --}}
        <span>Showing {{ $offers->firstItem() ?? 0 }} - {{ $offers->lastItem() ?? 0 }} of {{ $offers->total() }}</span>
    </div>
    <div class="flex items-center gap-1">
        {{ $offers->onEachSide(2)->links('vendor.offers.partials.pagination-links') }}
    </div>
</div>
