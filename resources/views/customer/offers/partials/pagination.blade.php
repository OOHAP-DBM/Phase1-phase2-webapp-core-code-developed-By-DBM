{{-- resources/views/customer/offers/partials/pagination.blade.php --}}
<div class="flex items-center justify-between text-xs text-gray-500">
    <span>Showing {{ $offers->firstItem() ?? 0 }} - {{ $offers->lastItem() ?? 0 }} of {{ $offers->total() }}</span>
    <div class="flex items-center gap-1">{{ $offers->onEachSide(2)->links() }}</div>
</div>
