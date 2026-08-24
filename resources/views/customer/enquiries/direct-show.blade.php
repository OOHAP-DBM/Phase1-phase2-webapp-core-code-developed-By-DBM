@extends('layouts.customer')

@section('title', 'Enquiry Details')

@section('content')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Enquiry Details
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    View complete enquiry information and vendor responses.
                </p>
            </div>

            <a href="{{ route('customer.direct.enquiries.index') }}"
                class="px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                ← Back to Enquiries
            </a>
        </div>


        {{-- Enquiry Details --}}
        <div class="bg-white border border-gray-200 shadow-sm">

            {{-- Section Header --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Enquiry Information
                        </h2>

                        <p class="text-xs text-gray-500 mt-1">
                            Enquiry #{{ $enquiry->id }}
                        </p>
                    </div>

                    {{-- Status --}}
                    <span class="px-3 py-1 text-xs font-semibold rounded-full
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


            {{-- Details Grid --}}
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

                    {{-- Phone --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Phone
                        </p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $enquiry->phone ?: '—' }}
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

                    {{-- Location City --}}
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
                        <p class="mt-1 text-sm text-gray-900">
                            {{ is_array($enquiry->preferred_locations)
        ? implode(', ', $enquiry->preferred_locations)
        : ($enquiry->preferred_locations ?: '—') }}
                        </p>
                    </div>

                    {{-- Hoarding Type --}}
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Hoarding Type
                        </p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $enquiry->hoarding_type ?: '—' }}
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

                    {{-- Created --}}
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




    </div>

@endsection