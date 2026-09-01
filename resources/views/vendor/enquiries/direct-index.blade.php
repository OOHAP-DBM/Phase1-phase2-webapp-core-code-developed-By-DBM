 blade
@php
    $user = auth()->user();

    $layout = ($user && $user->hasRole('admin'))
        ? 'layouts.admin'
        : 'layouts.vendor';
@endphp

@extends($layout)

@section('title', 'My Direct Enquiries')

@section('content')

<div x-data="{
        openFilter: false
    }"
    class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">

    <div class="bg-white rounded-md shadow">

        {{-- HEADER --}}
        <div
            class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-4 sm:px-6 py-4 bg-primary rounded-t-xl">

            <div>

                <div class="flex items-center gap-2">

                    {{-- Mobile Back --}}
                    <button onclick="window.history.back()" type="button"
                        class="md:hidden inline-flex items-center justify-center rounded-full text-gray-700 hover:bg-gray-100 focus:outline-none">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7" />

                        </svg>

                    </button>

                    <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800">
                        My Direct Enquiries
                    </h4>

                </div>

                <p class="text-sm text-gray-700 mt-2">
                    View and manage your direct enquiries.
                </p>

            </div>


            {{-- SEARCH + FILTER --}}
            <div class="flex items-center gap-2 w-full lg:w-auto">

                <form method="GET"
                    action="{{ route('vendor.direct-enquiries.index') }}"
                    class="relative flex-1 sm:w-80 lg:w-96">

                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name, phone, location..."
                        class="w-full border border-gray-300 px-3 py-2 pr-9 text-sm focus:ring-1 focus:ring-primary focus:outline-none">

                    <span
                        class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" />

                        </svg>

                    </span>

                </form>


                <button type="button"
                    @click="openFilter = true"
                    class="border border-gray-300 bg-white text-gray-800 px-4 py-2 text-sm font-medium hover:bg-gray-50 transition whitespace-nowrap">

                    Filter

                </button>

            </div>

        </div>


        {{-- TABLE --}}
        <div class="p-4 sm:p-6 space-y-6">

            <div class="overflow-x-auto rounded-lg shadow">

                <table class="min-w-full text-xs sm:text-sm">

                    <thead class="bg-gray-100 text-left">

                        <tr>

                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">
                                SN
                            </th>

                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">
                                Enquiry ID
                            </th>

                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">
                                Enquirer
                            </th>

                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">
                                Hoarding Type
                            </th>

                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">
                                Location
                            </th>

                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">
                                Preferred Modes
                            </th>

                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">
                                Status
                            </th>

                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">
                                Created
                            </th>

                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap text-center">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-100 bg-white">

                        @forelse($enquiries as $index => $enquiry)

                            <tr class="hover:bg-gray-50">

                                {{-- SN --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 text-gray-700 whitespace-nowrap">

                                    {{ ($enquiries->currentPage() - 1) * $enquiries->perPage() + $index + 1 }}

                                </td>


                                {{-- ENQUIRY ID --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">

                                    <a href="{{ route('vendor.direct-enquiries.show', $enquiry->id) }}"
                                        class="text-green-600 font-semibold hover:text-green-700 hover:underline">

                                        {{ $enquiry->formatted_id ?? $enquiry->enquiry_number ?? '#' . $enquiry->id }}

                                    </a>

                                </td>


                                {{-- ENQUIRER --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3">

                                    <div class="font-semibold text-gray-900">
                                        {{ $enquiry->name ?? 'N/A' }}
                                    </div>

                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $enquiry->phone ?? '' }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $enquiry->email ?? '' }}
                                    </div>

                                </td>


                                {{-- HOARDING TYPE --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">

                                    @php
                                        $types = collect(explode(',', $enquiry->hoarding_type ?? ''))
                                            ->map(fn($type) => trim($type))
                                            ->filter();
                                    @endphp

                                    @forelse($types as $type)

                                        <span class="inline-block text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded mr-1 mb-1">
                                            {{ $type }}
                                        </span>

                                    @empty

                                        <span class="text-gray-400">
                                            -
                                        </span>

                                    @endforelse

                                </td>


                                {{-- LOCATION --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 max-w-xs">

                                    <div class="font-medium text-gray-800">
                                        {{ $enquiry->location_city ?? '-' }}
                                    </div>

                                    @php
                                        $preferredLocations = $enquiry->preferred_locations;

                                        if (is_string($preferredLocations)) {
                                            $preferredLocations = json_decode($preferredLocations, true);
                                        }

                                        $preferredLocations = is_array($preferredLocations)
                                            ? $preferredLocations
                                            : [];
                                    @endphp

                                    @if(count($preferredLocations))

                                        <div class="mt-1 space-y-1">

                                            @foreach(array_slice($preferredLocations, 0, 2) as $location)

                                                <div class="text-xs text-gray-500">
                                                    • {{ $location }}
                                                </div>

                                            @endforeach

                                            @if(count($preferredLocations) > 2)

                                                <div class="text-xs text-gray-400 italic">
                                                    +{{ count($preferredLocations) - 2 }} more
                                                </div>

                                            @endif

                                        </div>

                                    @endif

                                </td>


                                {{-- PREFERRED MODES --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">

                                    @php
                                        $modes = $enquiry->preferred_modes;

                                        if (is_string($modes)) {
                                            $modes = json_decode($modes, true);
                                        }

                                        $modes = is_array($modes) ? $modes : [];
                                    @endphp

                                    @forelse($modes as $mode)

                                        <span class="inline-block text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded mr-1 mb-1">
                                            {{ $mode }}
                                        </span>

                                    @empty

                                        <span class="text-gray-400">
                                            -
                                        </span>

                                    @endforelse

                                </td>


                                {{-- STATUS --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">

                                    @php

                                        $status = $enquiry->status ?? 'new';

                                        $statusClasses = [
                                            'new' => 'text-blue-600',
                                            'draft' => 'text-gray-600',
                                            'submitted' => 'text-green-600',
                                            'pending' => 'text-yellow-600',
                                            'responded' => 'text-orange-500',
                                            'accepted' => 'text-green-600',
                                            'rejected' => 'text-red-500',
                                            'cancelled' => 'text-red-600',
                                        ];

                                    @endphp

                                    <div class="text-xs font-semibold {{ $statusClasses[$status] ?? 'text-gray-600' }}">

                                        {{ ucwords(str_replace('_', ' ', $status)) }}

                                    </div>

                                </td>


                                {{-- CREATED --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">

                                    <div class="text-xs text-gray-700">

                                        {{ optional($enquiry->created_at)->format('d M, Y') }}

                                    </div>

                                    <div class="text-xs text-gray-400">

                                        {{ optional($enquiry->created_at)->format('H:i') }}

                                    </div>

                                </td>


                                {{-- ACTION --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 text-center whitespace-nowrap">

                                    <a href="{{ route('vendor.direct-enquiries.show', $enquiry->id) }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 rounded text-xs font-semibold text-white hover:bg-blue-600 transition whitespace-nowrap"
                                        style="background-color: var(--booking-btn-color);">

                                        View Enquiry

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="9"
                                    class="px-4 py-12 text-center text-gray-500">

                                    <div class="space-y-2">

                                        <p class="font-medium">
                                            No direct enquiries found
                                        </p>

                                        <p class="text-xs">
                                            No direct enquiries have been submitted yet.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}
            <div
                class="pt-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm text-gray-600">

                <div class="font-medium">

                    Showing
                    {{ $enquiries->firstItem() ?? 0 }}
                    -
                    {{ $enquiries->lastItem() ?? 0 }}
                    of
                    {{ $enquiries->total() }}

                </div>

                <div>

                    {{ $enquiries->links('pagination.vendor-compact') }}

                </div>

            </div>

        </div>

    </div>


    {{-- FILTER MODAL --}}
    <div x-show="openFilter"
        x-cloak
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-5">

        <div @click.away="openFilter = false"
            class="bg-white w-full max-w-lg rounded shadow-lg relative">

            {{-- HEADER --}}
            <div class="flex items-center justify-between h-10 bg-green-100 px-4 rounded-t">

                <span class="font-semibold text-gray-800">
                    Filter Enquiries
                </span>

                <button @click="openFilter = false"
                    class="text-gray-800 hover:text-black text-xl cursor-pointer">

                    ✕

                </button>

            </div>


            <form method="GET"
                action="{{ route('vendor.direct-enquiries.index') }}"
                class="p-6 space-y-6">

                {{-- KEEP SEARCH --}}
                @if(request('search'))

                    <input type="hidden"
                        name="search"
                        value="{{ request('search') }}">

                @endif


                {{-- STATUS --}}
                <div>

                    <h3 class="text-sm font-semibold text-gray-800 mb-3">
                        Status
                    </h3>

                    <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">

                        @foreach([
                            'new' => 'New',
                            'submitted' => 'Submitted',
                            'pending' => 'Pending',
                            'responded' => 'Responded',
                            'accepted' => 'Accepted',
                            'rejected' => 'Rejected',
                            'cancelled' => 'Cancelled',
                        ] as $value => $label)

                            <label class="flex items-center gap-2">

                                <input type="radio"
                                    name="status"
                                    value="{{ $value }}"
                                    {{ request('status') === $value ? 'checked' : '' }}>

                                {{ $label }}

                            </label>

                        @endforeach

                    </div>

                </div>


                {{-- BUTTONS --}}
                <div class="flex items-center justify-end gap-6 pt-4">

                    <a href="{{ route('vendor.direct-enquiries.index') }}"
                        class="text-sm text-black font-semibold hover:underline">

                        Reset

                    </a>

                    <button type="submit"
                        class="px-6 py-2 bg-green-800 text-white text-sm font-semibold hover:bg-green-900 cursor-pointer">

                        Apply Filter

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
 
