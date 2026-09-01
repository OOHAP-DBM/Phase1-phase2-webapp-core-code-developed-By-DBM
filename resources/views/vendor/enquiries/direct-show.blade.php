
@php
    $user = auth()->user();

    $layout = ($user && $user->hasRole('admin'))
        ? 'layouts.admin'
        : 'layouts.vendor';

    $status = $enquiry->status ?? 'new';

    $statusClasses = [
        'new'        => 'text-blue-600',
        'pending'    => 'text-yellow-600',
        'contacted'  => 'text-indigo-600',
        'quoted'     => 'text-orange-600',
        'accepted'   => 'text-green-600',
        'rejected'   => 'text-red-600',
        'cancelled'  => 'text-red-600',
        'closed'     => 'text-gray-600',
    ];
@endphp

@extends($layout)

@section('title', 'Direct Enquiry')

@section('content')

<div class="px-4 sm:px-6 lg:px-8 py-6 bg-white">

    {{-- ================= HEADER ================= --}}
    <div class="flex items-center justify-between py-1 bg-white mb-6">

        <div class="flex items-start">

            <a href="{{ route('vendor.direct-enquiries.index') }}" class="mx-2">

                <svg width="16" class="mt-2" height="10"
                     viewBox="0 0 16 14"
                     fill="none"
                     xmlns="http://www.w3.org/2000/svg">

                    <path
                        d="M15.414 7.91412L3.828 7.91412L8.328 12.4141L6.914 13.8281L-2.93326e-07 6.91412L6.914 0.000125592L8.328 1.41413L3.828 5.91412L15.414 5.91412L15.414 7.91412Z"
                        fill="#3C3C3C"
                    />

                </svg>

            </a>

            <div>

                <h2 class="text-base font-semibold text-gray-900">

                    Direct Enquiry

                    <span class="text-green-600">
                        ({{ $enquiry->formatted_id ?? '#' . $enquiry->id }})
                    </span>

                </h2>

                <p class="text-xs text-gray-500">
                    Details of direct enquiry submitted to you
                </p>

            </div>

        </div>

    </div>


    {{-- ================= MAIN CONTENT ================= --}}
    <div
        class="space-y-6"
        x-data="{
            openDetails: true,
            openRequirements: true
        }"
    >


        {{-- ================= ENQUIRY DETAILS ================= --}}
        <div class="rounded mb-6">

            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-[#f7f7f7]">

                <span class="font-semibold text-sm">
                    Enquiry Details
                </span>

                <button
                    @click="openDetails = !openDetails"
                    type="button"
                    class="flex items-center cursor-pointer gap-1 text-xs bg-gray-200 px-3 py-1 rounded hover:bg-gray-300"
                >

                    <span x-text="openDetails ? 'Collapse' : 'Expand'"></span>

                    <svg
                        class="w-4 h-4 transition-transform"
                        :class="openDetails ? 'rotate-180' : ''"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />

                    </svg>

                </button>

            </div>


            <div
                x-show="openDetails"
                x-transition
                class="px-6 py-6 bg-[#f7f7f7]"
            >

                <div class="grid grid-cols-1 md:grid-cols-12 gap-8">


                    {{-- ================= CONTACT DETAILS ================= --}}
                    <div class="md:col-span-4">

                        <h3 class="text-sm font-semibold mb-4">
                            Contact Details
                        </h3>

                        <div class="space-y-3 text-xs">

                            <div>
                                Name :
                                <span class="font-medium text-gray-900">
                                    {{ $enquiry->name ?? 'N/A' }}
                                </span>
                            </div>

                            <div>
                                Email :
                                <span class="font-medium text-gray-900">
                                    {{ $enquiry->email ?? 'N/A' }}
                                </span>
                            </div>

                            <div>
                                Phone :
                                <span class="font-medium text-gray-900">
                                    {{ $enquiry->phone ?? 'N/A' }}
                                </span>
                            </div>

                            <div>
                                Phone Verified :

                                @if($enquiry->is_phone_verified)

                                    <span class="font-semibold text-green-600">
                                        Verified
                                    </span>

                                @else

                                    <span class="font-semibold text-gray-500">
                                        Not Verified
                                    </span>

                                @endif

                            </div>

                        </div>

                    </div>


                    {{-- ================= REQUIREMENT DETAILS ================= --}}
                    <div class="md:col-span-4">

                        <h3 class="text-sm font-semibold mb-4">
                            Requirement Details
                        </h3>

                        <div class="space-y-3 text-xs">

                            <div>
                                Enquiry ID :
                                <span class="font-medium text-gray-900">
                                    {{ $enquiry->formatted_id ?? '#' . $enquiry->id }}
                                </span>
                            </div>

                            <div>
                                Hoarding Type :
                                <span class="font-medium text-gray-900">
                                    {{ $enquiry->hoarding_type ?? 'N/A' }}
                                </span>
                            </div>

                            <div>
                                Location :
                                <span class="font-medium text-gray-900">
                                    {{ $enquiry->location_city ?? 'N/A' }}
                                </span>
                            </div>

                            <div>
                                Status :
                                <span class="font-semibold {{ $statusClasses[$status] ?? 'text-gray-600' }}">
                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                </span>
                            </div>

                            <div>
                                Source :
                                <span class="font-medium text-gray-900">
                                    {{ ucfirst($enquiry->source ?? 'N/A') }}
                                </span>
                            </div>

                        </div>

                    </div>


                    {{-- ================= RECEIVED DATE ================= --}}
                    <div class="md:col-span-4">

                        <h3 class="text-sm font-semibold mb-4">
                            Received On
                        </h3>

                        @if($enquiry->created_at)

                            <span class="text-lg font-semibold leading-none">
                                {{ $enquiry->created_at->format('d') }}
                            </span>

                            <span class="text-sm text-gray-500">
                                {{ $enquiry->created_at->format('M Y') }}
                            </span>

                            <div class="mt-3 text-xs text-gray-500">
                                {{ $enquiry->created_at->format('d M, Y | H:i') }}
                            </div>

                        @else

                            <span class="text-sm text-gray-400">
                                N/A
                            </span>

                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- ================= LOCATION & PREFERENCES ================= --}}
        <div class="rounded mb-6">

            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-[#f7f7f7]">

                <span class="font-semibold text-sm">
                    Location & Preferences
                </span>

                <button
                    @click="openRequirements = !openRequirements"
                    type="button"
                    class="flex items-center cursor-pointer gap-1 text-xs bg-gray-200 px-3 py-1 rounded hover:bg-gray-300"
                >

                    <span x-text="openRequirements ? 'Collapse' : 'Expand'"></span>

                    <svg
                        class="w-4 h-4 transition-transform"
                        :class="openRequirements ? 'rotate-180' : ''"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />

                    </svg>

                </button>

            </div>


            <div
                x-show="openRequirements"
                x-transition
                class="px-6 py-6 bg-[#f7f7f7]"
            >

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">


                    {{-- LOCATION --}}
                    <div>

                        <h3 class="text-sm font-semibold mb-3">
                            Location
                        </h3>

                        <div class="text-xs text-gray-700">

                            <div class="mb-2">
                                City :
                                <span class="font-medium">
                                    {{ $enquiry->location_city ?? 'N/A' }}
                                </span>
                            </div>

                            <div>
                                Preferred Locations :
                            </div>

                            @php
                                $preferredLocations = $enquiry->preferred_locations;

                                if (is_string($preferredLocations)) {
                                    $decodedLocations = json_decode($preferredLocations, true);

                                    $preferredLocations = is_array($decodedLocations)
                                        ? $decodedLocations
                                        : [$preferredLocations];
                                }

                                $preferredLocations = is_array($preferredLocations)
                                    ? $preferredLocations
                                    : [];
                            @endphp

                            @if(count($preferredLocations))

                                <div class="mt-2 space-y-1">

                                    @foreach($preferredLocations as $location)

                                        <div class="text-gray-600">
                                            • {{ $location }}
                                        </div>

                                    @endforeach

                                </div>

                            @else

                                <span class="text-gray-400">
                                    N/A
                                </span>

                            @endif

                        </div>

                    </div>


                    {{-- PREFERRED MODES --}}
                    <div>

                        <h3 class="text-sm font-semibold mb-3">
                            Preferred Contact Modes
                        </h3>

                        @php
                            $preferredModes = $enquiry->preferred_modes;

                            if (is_string($preferredModes)) {
                                $decodedModes = json_decode($preferredModes, true);

                                $preferredModes = is_array($decodedModes)
                                    ? $decodedModes
                                    : [$preferredModes];
                            }

                            $preferredModes = is_array($preferredModes)
                                ? $preferredModes
                                : [];
                        @endphp


                        @if(count($preferredModes))

                            <div class="flex flex-wrap gap-2">

                                @foreach($preferredModes as $mode)

                                    <span class="px-3 py-1 bg-white border border-gray-200 rounded text-xs text-gray-700">

                                        {{ $mode }}

                                    </span>

                                @endforeach

                            </div>

                        @else

                            <span class="text-xs text-gray-400">
                                N/A
                            </span>

                        @endif

                    </div>


                    {{-- REMARKS --}}
                    <div>

                        <h3 class="text-sm font-semibold mb-3">
                            Remarks
                        </h3>

                        @if($enquiry->remarks)

                            <p class="text-xs text-gray-700 leading-relaxed">
                                {{ $enquiry->remarks }}
                            </p>

                        @else

                            <span class="text-xs text-gray-400">
                                No remarks provided.
                            </span>

                        @endif

                    </div>

                </div>

            </div>

        </div>




        {{-- ================= ADMIN NOTES ================= --}}
        @if($enquiry->admin_notes)

            <div class="rounded mb-6">

                <div class="px-5 py-3 border-b border-gray-200 bg-[#f7f7f7]">

                    <span class="font-semibold text-sm">
                        Enquiry Notes
                    </span>

                </div>

                <div class="px-6 py-5 bg-[#f7f7f7]">

                    <p class="text-sm text-gray-700 leading-relaxed">
                        {{ $enquiry->admin_notes }}
                    </p>

                </div>

            </div>

        @endif


        {{-- ================= UPDATED DATE ================= --}}
        @if($enquiry->updated_at)

            <div class="text-right text-xs text-gray-400">

                Last Updated:
                {{ $enquiry->updated_at->format('d M, Y | H:i') }}

            </div>

        @endif

    </div>

</div>

@endsection

