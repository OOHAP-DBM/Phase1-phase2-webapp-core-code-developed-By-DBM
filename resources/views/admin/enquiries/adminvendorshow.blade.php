@extends('layouts.admin')

@section('title', 'Enquiry Vendors')

@section('content')

    <div class="px-6 py-6 bg-gray-50 min-h-screen">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">

            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    Enquiry Details
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    View enquiry information and vendors reached.
                </p>
            </div>

            <a href="{{ route('admin.direct-enquiries.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-md">
                ← Back to Enquiries
            </a>

        </div>


        {{-- Enquiry Information --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">

            {{-- Section Header --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Enquiry Information
                        </h2>

                        <p class="text-xs text-gray-500 mt-1">
                            Enquiry #{{ $enquiry->id }}
                        </p>
                    </div>

                    {{-- Status --}}
                    <span class="inline-flex w-fit px-3 py-1 text-xs font-semibold rounded-full
                        @if($enquiry->status === 'pending')
                            bg-yellow-100 text-yellow-700
                        @elseif($enquiry->status === 'approved' || $enquiry->status === 'completed')
                            bg-green-100 text-green-700
                        @elseif($enquiry->status === 'rejected' || $enquiry->status === 'cancelled')
                            bg-red-100 text-red-700
                        @else
                            bg-gray-100 text-gray-700
                        @endif
                    ">
                        {{ ucfirst(str_replace('_', ' ', $enquiry->status ?? 'Pending')) }}
                    </span>

                </div>

            </div>


            {{-- Details --}}
            <div class="p-6">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-6">

                    {{-- Name --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Name
                        </p>

                        <p class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $enquiry->name ?: '—' }}
                        </p>
                    </div>


                    {{-- Email --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Email
                        </p>

                        <p class="mt-1 text-sm text-gray-900 break-all">
                            {{ $enquiry->email ?: '—' }}
                        </p>
                    </div>


                    {{-- Phone --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Phone
                        </p>

                        <p class="mt-1 text-sm text-gray-900">
                            {{ $enquiry->phone ?: '—' }}
                        </p>
                    </div>


                    {{-- City --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Location City
                        </p>

                        <p class="mt-1 text-sm text-gray-900">
                            {{ $enquiry->location_city ?: '—' }}
                        </p>
                    </div>


                    {{-- Preferred Locations --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Preferred Locations
                        </p>

                        <div class="mt-2 flex flex-wrap gap-1">

                            @forelse($enquiry->preferred_locations ?? [] as $location)

                                <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                                    {{ $location }}
                                </span>

                            @empty

                                <span class="text-sm text-gray-500">
                                    —
                                </span>

                            @endforelse

                        </div>
                    </div>


                    {{-- Hoarding Type --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Hoarding Type
                        </p>

                        <p class="mt-1">
                            <span
                                class="inline-flex px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                                {{ $enquiry->hoarding_type ?: '—' }}
                            </span>
                        </p>
                    </div>


                    {{-- Hoarding Location --}}
                    <div class="lg:col-span-2">

                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Hoarding Location
                        </p>

                        <p class="mt-1 text-sm text-gray-900">
                            {{ $enquiry->hoarding_location ?: '—' }}
                        </p>

                    </div>


                    {{-- Preferred Modes --}}
                    <div>

                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Preferred Modes
                        </p>

                        <div class="mt-2 flex flex-wrap gap-1">

                            @forelse($enquiry->preferred_modes ?? [] as $mode)

                                <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                                    {{ $mode }}
                                </span>

                            @empty

                                <span class="text-sm text-gray-500">
                                    —
                                </span>

                            @endforelse

                        </div>

                    </div>


                    {{-- Submitted --}}
                    <div>

                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Submitted On
                        </p>

                        <p class="mt-1 text-sm text-gray-900">
                            {{ $enquiry->created_at?->format('d M Y, h:i A') ?? '—' }}
                        </p>

                    </div>


                    {{-- Updated --}}
                    <div>

                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Last Updated
                        </p>

                        <p class="mt-1 text-sm text-gray-900">
                            {{ $enquiry->updated_at?->format('d M Y, h:i A') ?? '—' }}
                        </p>

                    </div>

                </div>


                {{-- Remarks --}}
                <div class="mt-8 pt-6 border-t border-gray-200">

                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        Remarks
                    </p>

                    <div class="mt-2 bg-gray-50 border border-gray-200 p-4 text-sm text-gray-700 min-h-[80px]">
                        {{ $enquiry->remarks ?: 'No remarks added.' }}
                    </div>

                </div>

            </div>

        </div>


        {{-- Vendors Section --}}
        <div class="mt-6 bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">

            {{-- Vendors Header --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Vendors Reached
                        </h2>

                        <p class="text-xs text-gray-500 mt-1">
                            Vendors to whom this enquiry was sent.
                        </p>
                    </div>


                    {{-- Vendor Count --}}
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-md">

                        <span class="text-xs font-medium text-blue-600">
                            Total Vendors
                        </span>

                        <span class="text-sm font-bold text-blue-700">
                            {{ $enquiry->assignedVendors->count() }}
                        </span>

                    </div>

                </div>

            </div>


            {{-- Vendors Table --}}
            @if($enquiry->assignedVendors->count())

                <div class="overflow-x-auto">

                    <table class="min-w-full text-sm">

                        <thead class="bg-gray-100 text-gray-700">

                            <tr>

                                <th class="px-4 py-3 w-12 text-center">
                                    SN
                                </th>

                                <th class="px-4 py-3 text-left">
                                    Vendor
                                </th>

                                <th class="px-4 py-3 text-left">
                                    Email
                                </th>

                                <th class="px-4 py-3 text-left">
                                    Phone
                                </th>

                                <th class="px-4 py-3 text-center">
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody class="divide-y divide-gray-200 bg-white">

                            @foreach($enquiry->assignedVendors as $index => $vendor)

                                <tr class="hover:bg-gray-50 transition">

                                    {{-- SN --}}
                                    <td class="px-4 py-4 text-center text-gray-500 font-medium">
                                        {{ $index + 1 }}
                                    </td>


                                    {{-- Vendor --}}
                                    <td class="px-4 py-4">

                                        <div class="font-semibold text-gray-900">
                                            {{ $vendor->name ?? '—' }}
                                        </div>

                                    </td>


                                    {{-- Email --}}
                                    <td class="px-4 py-4">

                                        <div class="text-gray-700 break-all">
                                            {{ $vendor->email ?? '—' }}
                                        </div>

                                    </td>


                                    {{-- Phone --}}
                                    <td class="px-4 py-4">

                                        <div class="text-gray-700">
                                            {{ $vendor->phone ?? '—' }}
                                        </div>

                                    </td>


                                    {{-- Status --}}
                                    <td class="px-4 py-4 text-center">

                                        <span
                                            class="inline-flex px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-medium">
                                            Awaiting Response
                                        </span>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="px-6 py-12 text-center">

                    <div class="text-gray-400 mb-2">
                        <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>

                    <p class="text-sm font-medium text-gray-600">
                        No vendors were assigned to this enquiry.
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        This enquiry has not been sent to any vendor yet.
                    </p>

                </div>

            @endif

        </div>

    </div>

@endsection