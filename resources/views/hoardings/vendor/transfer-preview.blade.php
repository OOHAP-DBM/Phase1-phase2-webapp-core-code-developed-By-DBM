@extends('layouts.vendor')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8">
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
        <h1 class="text-xl font-semibold text-gray-900">Review Excel hoarding import</h1>
        <p class="mt-2 text-sm text-gray-600">
            Transfer file is mapped to your signed-in vendor account ({{ $preview['vendor_name'] }}).
            Ready listings will be imported as drafts. Already-imported records and original listings still present in this same database will be skipped.
        </p>

        <div class="mt-5 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
            <div class="rounded-lg bg-green-50 p-3 text-green-800">Ready: {{ $preview['ready_count'] }}</div>
            <div class="rounded-lg bg-amber-50 p-3 text-amber-800">Already imported (will skip): {{ $preview['duplicate_count'] }}</div>
            <div class="rounded-lg bg-sky-50 p-3 text-sky-800">Originals still present here (will skip): {{ $preview['source_exists_count'] }}</div>
        </div>
        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[640px] border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs uppercase text-gray-500">
                        <th class="px-3 py-3">Hoarding</th>
                        <th class="px-3 py-3">Type</th>
                        <th class="px-3 py-3">Location</th>
                        <th class="px-3 py-3">Media</th>
                        <th class="px-3 py-3">Import action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview['items'] as $item)
                        <tr class="border-b border-gray-100">
                            <td class="px-3 py-3 font-medium text-gray-900">{{ $item['title'] }}</td>
                            <td class="px-3 py-3">{{ $item['type'] }}</td>
                            <td class="px-3 py-3">{{ $item['location'] ?: '—' }}</td>
                            <td class="px-3 py-3">{{ $item['media_count'] }}</td>
                            <td class="px-3 py-3">
                                @if($item['status'] === 'duplicate')
                                    <span class="font-medium text-amber-700">Skip — already imported</span>
                                    @if($item['existing_hoarding_id'])
                                        <span class="block text-xs text-gray-500">DEV ID: {{ $item['existing_hoarding_id'] }}</span>
                                    @endif
                                @elseif($item['status'] === 'source_exists')
                                    <span class="font-medium text-sky-700">Skip — original still exists here</span>
                                @else
                                    <span class="font-medium text-green-700">Create as draft</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <form action="{{ route('vendor.hoardings.import.cancel') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <button type="submit" class="w-full rounded-md border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">
                    Cancel
                </button>
            </form>
            <form action="{{ route('vendor.hoardings.import.confirm') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <button type="submit"
                    @disabled($preview['ready_count'] < 1)
                    class="w-full rounded-md bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                    Import {{ $preview['ready_count'] }} ready hoarding(s)
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
