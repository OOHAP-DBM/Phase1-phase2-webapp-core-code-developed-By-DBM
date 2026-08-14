{{-- resources/views/vendor/offers/partials/archived-table.blade.php --}}
@forelse($offers as $index => $offer)
@php
    $hoardingCount = $offer->hoardingCount();
    $statusMap = [
        'draft'    => 'Draft', 'sent' => 'Sent', 'accepted' => 'Accepted',
        'rejected' => 'Rejected', 'expired' => 'Expired',
    ];
@endphp
<tr class="border-b hover:bg-gray-50">
    <td class="px-3 py-3 text-gray-500">{{ $offers->firstItem() + $index }}</td>
    <td class="px-3 py-3">
        <a href="{{ route('offers.show', $offer->id) }}" class="font-bold text-gray-600 hover:underline">#{{ $offer->id }}</a>
    </td>
    <td class="px-3 py-3">{{ $offer->customer->name ?? 'N/A' }}</td>
    <td class="px-3 py-3">{{ $hoardingCount }}</td>
    <td class="px-3 py-3 text-gray-500">{{ $statusMap[$offer->status] ?? ucfirst($offer->status) }}</td>
    <td class="px-3 py-3 text-gray-400">{{ $offer->archived_at?->format('d M, y') }}</td>
    <td class="px-3 py-3 text-right">
        <button type="button" onclick="confirmUnarchive({{ $offer->id }}, this)"
            class="px-3 py-1.5 rounded text-emerald-700 text-[11px] font-semibold border border-emerald-200 bg-emerald-50 hover:bg-emerald-100">
            Restore
        </button>
    </td>
</tr>
@empty
<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400 text-xs">No archived offers</td></tr>
@endforelse
