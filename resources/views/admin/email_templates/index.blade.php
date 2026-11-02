@extends('layouts.admin')

@section('title', 'Email Templates')

@section('breadcrumb')


   


@endsection

@section('content')

    <div class="bg-[#F7F7F7] w-full min-h-screen p-6">


        {{-- =========================================================
            PAGE HEADER
        ========================================================== --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

            <div>
                <h1 class="text-xl font-bold text-gray-900">
                    Email Templates
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Create and manage professional system-generated emails
                </p>
            </div>

            {{-- CREATE BUTTON --}}
            <a
                href="{{ route('email_templates.create') }}"
                class="inline-flex items-center justify-center gap-2
                       px-4 py-2.5
                       bg-[#00995c] hover:bg-[#008450]
                       text-white text-sm font-semibold
                       rounded-xl shadow-sm
                       transition"
            >

                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                    />
                </svg>

                Create Template

            </a>

        </div>


        {{-- =========================================================
            SUCCESS MESSAGE
        ========================================================== --}}
        @if(session('success'))

            <div
                class="mb-5 flex items-center gap-3
                       p-4
                       bg-green-50
                       border border-green-200
                       text-green-700
                       rounded-xl"
            >

                <svg
                    class="w-5 h-5 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />
                </svg>

                <span class="text-sm font-medium">
                    {{ session('success') }}
                </span>

            </div>

        @endif


        {{-- =========================================================
            EMAIL TEMPLATE TYPE CARDS
        ========================================================== --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">

            {{-- Email --}}
            <div
                class="relative bg-white border-2 border-[#00995c]
                       rounded-xl p-5 shadow-sm"
            >

                {{-- Active Corner --}}
                <div
                    class="absolute top-0 right-0
                           w-0 h-0
                           border-t-[32px] border-t-[#00995c]
                           border-l-[32px] border-l-transparent"
                ></div>

                <div class="flex items-center gap-3">

                    <div
                        class="w-11 h-11 rounded-xl
                               bg-[#00995c]/10
                               flex items-center justify-center"
                    >

                        <svg
                            class="w-5 h-5 text-[#00995c]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M3 8l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                            />
                        </svg>

                    </div>

                    <div>

                        <h3 class="text-sm font-semibold text-gray-900">
                            Email Template
                        </h3>

                        <p class="text-xs text-gray-500 mt-1">
                            System email notifications
                        </p>

                    </div>

                </div>

            </div>


            {{-- SMS --}}
            <div
                class="bg-white border border-gray-200
                       rounded-xl p-5 shadow-sm
                       opacity-80"
            >

                <div class="flex items-center gap-3">

                    <div
                        class="w-11 h-11 rounded-xl
                               bg-gray-100
                               flex items-center justify-center"
                    >

                        <svg
                            class="w-5 h-5 text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-width="1.8"
                                d="M10 18h4"
                            />
                        </svg>

                    </div>

                    <div>

                        <h3 class="text-sm font-semibold text-gray-700">
                            SMS Template
                        </h3>

                        <p class="text-xs text-gray-400 mt-1">
                            Coming soon
                        </p>

                    </div>

                </div>

            </div>


            {{-- Push --}}
            <div
                class="bg-white border border-gray-200
                       rounded-xl p-5 shadow-sm
                       opacity-80"
            >

                <div class="flex items-center gap-3">

                    <div
                        class="w-11 h-11 rounded-xl
                               bg-gray-100
                               flex items-center justify-center"
                    >

                        <svg
                            class="w-5 h-5 text-gray-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"
                            />
                        </svg>

                    </div>

                    <div>

                        <h3 class="text-sm font-semibold text-gray-700">
                            Push Notification
                        </h3>

                        <p class="text-xs text-gray-400 mt-1">
                            Coming soon
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
            SHORT CODES
        ========================================================== --}}
        <div
            class="bg-white rounded-2xl
                   border border-gray-100
                   shadow-sm overflow-hidden mb-6"
        >

            <div
                class="px-5 py-3
                       bg-[#00995c]
                       flex items-center justify-between"
            >

                <div class="flex items-center gap-2">

                    <svg
                        class="w-4 h-4 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 9l-3 3 3 3M16 9l3 3-3 3M14 5l-4 14"
                        />
                    </svg>

                    <span class="text-xs font-semibold text-white">
                        Short Codes
                    </span>

                </div>

                <span class="text-[11px] text-white/80">
                    Available Variables
                </span>

            </div>


            @php

                $shortcodes = [
                    '{{customer_name}}' => 'Customer Name',
                    '{{customer_email}}' => 'Customer Email',
                    '{{vendor_name}}' => 'Vendor Name',
                    '{{vendor_email}}' => 'Vendor Email',
                    '{{enquiry_number}}' => 'Enquiry Number',
                    '{{quotation_number}}' => 'Quotation Number',
                    '{{app_name}}' => 'Application Name',
                    '{{login_url}}' => 'Login URL',
                    '{{support_email}}' => 'Support Email',
                ];

            @endphp


            <div class="divide-y divide-gray-100">

                @foreach($shortcodes as $code => $description)

                    <div
                        class="flex items-center justify-between
                               px-5 py-3
                               hover:bg-gray-50 transition"
                    >

                        <button
                            type="button"
                            onclick="copyShortcode('{{ $code }}', this)"
                            class="text-left"
                        >

                            <code
                                class="text-xs font-medium
                                       text-[#00995c]
                                       hover:underline"
                            >
                                {{ $code }}
                            </code>

                        </button>

                        <span class="text-xs text-gray-500">
                            {{ $description }}
                        </span>

                    </div>

                @endforeach

            </div>

        </div>


        {{-- =========================================================
            TEMPLATES
        ========================================================== --}}
        <div
            class="bg-white rounded-2xl
                   border border-gray-100
                   shadow-sm overflow-hidden"
        >

            {{-- Header --}}
            <div
                class="px-6 py-5
                       border-b border-gray-100
                       flex flex-col sm:flex-row
                       sm:items-center
                       sm:justify-between gap-3"
            >

                <div>

                    <h2 class="text-base font-semibold text-gray-900">
                        Notification Templates
                    </h2>

                    <p class="text-xs text-gray-500 mt-1">
                        Manage emails used throughout OOHAPP
                    </p>

                </div>

                <span
                    class="inline-flex items-center
                           px-3 py-1.5
                           rounded-full
                           bg-gray-100
                           text-gray-600
                           text-xs font-medium"
                >
                    {{ $templates->count() }}
                    {{ Str::plural('Template', $templates->count()) }}
                </span>

            </div>


            {{-- Template List --}}
            @forelse($templates as $template)

                <div
                    class="px-6 py-5
                           border-b border-gray-100
                           last:border-b-0
                           hover:bg-gray-50
                           transition"
                >

                    <div
                        class="flex flex-col
                               lg:flex-row
                               lg:items-center
                               lg:justify-between
                               gap-4"
                    >

                        {{-- Info --}}
                        <div class="flex items-start gap-4">

                            <div
                                class="w-10 h-10
                                       rounded-xl
                                       bg-[#00995c]/10
                                       flex items-center justify-center
                                       flex-shrink-0"
                            >

                                <svg
                                    class="w-5 h-5 text-[#00995c]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M3 8l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                                    />
                                </svg>

                            </div>


                            <div class="min-w-0">

                                <div class="flex items-center gap-2 flex-wrap">

                                    <h3 class="text-sm font-semibold text-gray-900">
                                        {{ $template->name }}
                                    </h3>

                                    @if($template->is_active)

                                        <span
                                            class="inline-flex items-center gap-1
                                                   px-2 py-0.5
                                                   rounded-full
                                                   bg-green-50
                                                   border border-green-100
                                                   text-green-700
                                                   text-[10px] font-medium"
                                        >
                                            <span
                                                class="w-1.5 h-1.5 rounded-full bg-green-500"
                                            ></span>

                                            Active
                                        </span>

                                    @else

                                        <span
                                            class="inline-flex items-center gap-1
                                                   px-2 py-0.5
                                                   rounded-full
                                                   bg-gray-100
                                                   border border-gray-200
                                                   text-gray-500
                                                   text-[10px] font-medium"
                                        >
                                            Inactive
                                        </span>

                                    @endif

                                </div>

                                <p class="text-xs text-[#00995c] mt-1">
                                    {{ $template->key }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1 truncate">
                                    {{ $template->subject }}
                                </p>

                            </div>

                        </div>


                        {{-- Actions --}}
                        <div class="flex items-center gap-2">

                            <a
                                href="{{ route('email_templates.preview', $template->id) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1.5
                                       px-3 py-2
                                       text-xs font-medium
                                       text-gray-600
                                       bg-gray-100
                                       hover:bg-gray-200
                                       rounded-lg
                                       transition"
                            >

                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12z"
                                    />

                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="2.5"
                                        stroke-width="1.8"
                                    />
                                </svg>

                                Preview

                            </a>


                            <a
                                href="{{ route('email_templates.edit', $template->id) }}"
                                class="inline-flex items-center gap-1.5
                                       px-3 py-2
                                       text-xs font-medium
                                       text-white
                                       bg-[#00995c]
                                       hover:bg-[#008450]
                                       rounded-lg
                                       transition"
                            >

                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 3.5a2.121 2.121 0 013 3L12 16l-4 1 1-4 9.5-9.5z"
                                    />
                                </svg>

                                Edit

                            </a>

                        </div>

                    </div>

                </div>

            @empty

                {{-- EMPTY STATE --}}
                <div class="px-6 py-16 text-center">

                    <div
                        class="w-16 h-16
                               mx-auto
                               rounded-2xl
                               bg-[#00995c]/10
                               flex items-center justify-center
                               mb-5"
                    >

                        <svg
                            class="w-8 h-8 text-[#00995c]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M3 8l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                            />
                        </svg>

                    </div>


                    <h3 class="text-sm font-semibold text-gray-900">
                        No email templates yet
                    </h3>

                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Create your first email template to start managing
                        system-generated notifications.
                    </p>


                    {{-- Empty State Create --}}
                    <a
                        href="{{ route('email_templates.create') }}"
                        class="inline-flex items-center gap-2
                               mt-5
                               px-4 py-2.5
                               bg-[#00995c]
                               hover:bg-[#008450]
                               text-white
                               text-xs font-semibold
                               rounded-xl
                               transition"
                    >

                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>

                        Create Your First Template

                    </a>

                </div>

            @endforelse

        </div>

    </div>


    {{-- =========================================================
        COPY SHORTCODE
    ========================================================= --}}
    <script>

    function copyShortcode(code, button) {

        navigator.clipboard.writeText(code).then(function () {

            const oldText = button.innerText;

            button.innerText = 'Copied!';

            setTimeout(function () {
                button.innerText = oldText;
            }, 1200);

        });

    }

    </script>

@endsection