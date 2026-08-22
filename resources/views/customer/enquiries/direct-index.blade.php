@extends('layouts.customer')

@section('title', 'Direct Enquiries')

@section('content')

    <div class="px-6 py-6 bg-gray-50 min-h-screen">

        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">

            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    Direct Enquiries
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Your direct enquiry history
                </p>
            </div>

        </div>


        {{-- TABLE --}}
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead class="bg-gray-100 text-gray-700">
                        <tr>

                            <th class="px-4 py-3 w-12 text-center">
                                SN
                            </th>

                            <th class="px-4 py-3 text-left">
                                Name
                            </th>

                            <th class="px-4 py-3 text-left">
                                Contact
                            </th>

                            <th class="px-4 py-3 text-left">
                                City
                            </th>

                            <th class="px-4 py-3 text-center">
                                Preferred Locations
                            </th>

                            <th class="px-4 py-3 text-center">
                                Hoarding Type
                            </th>

                            <th class="px-4 py-3 text-center">
                                Preferred Modes
                            </th>

                            <th class="px-4 py-3 text-center">
                                Verified
                            </th>

                            <th class="px-4 py-3 text-center">
                                Status
                            </th>


                            <th class="px-4 py-3 text-center">
                                Action
                            </th>

                        </tr>
                    </thead>


                    <tbody class="divide-y">

                        @forelse($enquiries as $index => $enquiry)

                                        <tr class="hover:bg-gray-50">

                                            {{-- SN --}}
                                            <td class="px-4 py-3 w-12 text-center text-gray-500 font-medium">

                                                {{ $enquiries->firstItem() + $index }}

                                            </td>


                                            {{-- NAME --}}
                                            <td class="px-4 py-3 text-left font-semibold text-gray-900">

                                                {{ $enquiry->name }}

                                            </td>


                                            {{-- CONTACT --}}
                                            <td class="px-4 py-3 text-left">

                                                <div>
                                                    {{ $enquiry->email }}
                                                </div>

                                                <div class="text-xs text-gray-500">
                                                    {{ $enquiry->phone }}
                                                </div>

                                            </td>


                                            {{-- CITY --}}
                                            <td class="px-4 py-3 text-left">

                                                {{ $enquiry->location_city ?? '-' }}

                                            </td>


                                            {{-- PREFERRED LOCATIONS --}}
                                            <td class="px-4 py-3 text-center">

                                                @forelse($enquiry->preferred_locations ?? [] as $location)

                                                    <span class="inline-block px-2 py-1 bg-gray-100 text-xs rounded mr-1 mb-1">
                                                        {{ $location }}
                                                    </span>

                                                @empty

                                                    <span class="text-gray-400">
                                                        -
                                                    </span>

                                                @endforelse

                                            </td>


                                            {{-- HOARDING TYPE --}}
                                            <td class="px-4 py-3 text-center">

                                                @php
                                                    $hoardingTypes = is_array($enquiry->hoarding_type)
                                                        ? $enquiry->hoarding_type
                                                        : array_filter(
                                                            array_map(
                                                                'trim',
                                                                explode(',', $enquiry->hoarding_type ?? '')
                                                            )
                                                        );
                                                @endphp

                                                @forelse($hoardingTypes as $type)

                                                    <span
                                                        class="inline-block px-2 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold mr-1 mb-1">
                                                        {{ $type }}
                                                    </span>

                                                @empty

                                                    <span class="text-gray-400">
                                                        -
                                                    </span>

                                                @endforelse

                                            </td>


                                            {{-- PREFERRED MODES --}}
                                            <td class="px-4 py-3 text-center">

                                                @forelse($enquiry->preferred_modes ?? [] as $mode)

                                                    <span class="inline-block px-2 py-1 bg-gray-100 text-xs rounded mr-1 mb-1">
                                                        {{ $mode }}
                                                    </span>

                                                @empty

                                                    <span class="text-gray-400">
                                                        -
                                                    </span>

                                                @endforelse

                                            </td>


                                            {{-- VERIFIED --}}
                                            <td class="px-4 py-3 text-center text-xs">

                                                <span class="{{ $enquiry->is_phone_verified
                            ? 'text-green-600'
                            : 'text-red-500' }}">

                                                    Phone

                                                </span>

                                            </td>


                                            {{-- STATUS --}}
                                            <td class="px-4 py-3 text-center">

                                                @php
                                                    $statusClasses = match ($enquiry->status) {
                                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                                        'new' => 'bg-green-100 text-green-700',
                                                        'completed' => 'bg-green-100 text-green-700',
                                                        'cancelled' => 'bg-red-100 text-red-700',
                                                        default => 'bg-gray-100 text-gray-700',
                                                    };
                                                @endphp

                                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">

                                                    {{ strtoupper($enquiry->status ?? 'NEW') }}

                                                </span>

                                            </td>

                                            <td class="px-4 py-3 text-center">
                                                <a href="{{ route('customer.direct.enquiries.show', $enquiry->id) }}"
                                                    class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold inline-block whitespace-nowrap transition-colors">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>

                        @empty

                            {{-- EMPTY STATE --}}
                            <tr>

                                <td colspan="9" class="py-12 text-center">

                                    <div class="flex flex-col items-center justify-center">

                                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">

                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                                            </svg>

                                        </div>

                                        <p class="text-sm font-medium text-gray-700">
                                            No Direct Enquiries Yet
                                        </p>

                                        <p class="text-xs text-gray-500 mt-1">
                                            Your direct enquiries will appear here.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}
            @if($enquiries->hasPages())

                <div class="px-4 py-3 border-t bg-gray-50">

                    {{ $enquiries->links() }}

                </div>

            @endif

        </div>

    </div>

@endsection