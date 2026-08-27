@extends('layouts.admin')

@section('title', 'Email Template Preview')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Home', 'route' => route('admin.dashboard')],
        ['label' => 'Email Templates', 'route' => route('email_templates.index')],
        ['label' => 'Preview'],
    ]" />
@endsection

@section('content')

<div class="bg-[#F7F7F7] w-full min-h-screen p-6">

    {{-- ============================================================
         PAGE HEADER
    ============================================================= --}}
    <div class="flex items-center justify-between mb-6">

        <div>
            <h1 class="text-xl font-bold text-gray-900">
                Email Template Preview
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Preview how this email will appear to the recipient.
            </p>
        </div>

        <div class="flex items-center gap-3">

            <a
                href="{{ route('email_templates.edit', $emailTemplate) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition"
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
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                           M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"
                    />
                </svg>

                Edit Template
            </a>

            <a
                href="{{ route('email_templates.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#00995c] text-white rounded-lg hover:bg-[#008451] transition"
            >
                Back to Templates
            </a>

        </div>

    </div>


    {{-- ============================================================
         TEMPLATE INFORMATION
    ============================================================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- Name --}}
            <div>

                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">
                    Template Name
                </p>

                <p class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $emailTemplate->name }}
                </p>

            </div>


            {{-- Key --}}
            <div>

                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">
                    Template Key
                </p>

                <p class="mt-1">
                    <code class="text-sm bg-gray-100 px-2 py-1 rounded text-gray-700">
                        {{ $emailTemplate->key }}
                    </code>
                </p>

            </div>


            {{-- Status --}}
            <div>

                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">
                    Status
                </p>

                <div class="mt-1">

                    @if($emailTemplate->is_active)

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">

                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>

                            Active

                        </span>

                    @else

                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">

                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>

                            Inactive

                        </span>

                    @endif

                </div>

            </div>

        </div>


        {{-- Subject --}}
        <div class="mt-5 pt-5 border-t border-gray-100">

            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">
                Subject
            </p>

            <p class="mt-1 text-sm text-gray-900">
                {{ $emailTemplate->subject }}
            </p>

        </div>

    </div>


    {{-- ============================================================
         EMAIL PREVIEW
    ============================================================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Preview Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">

            <div>

                <h2 class="text-sm font-semibold text-gray-900">
                    Email Preview
                </h2>

                <p class="text-xs text-gray-500 mt-0.5">
                    Sample data is used for dynamic variables.
                </p>

            </div>

            <span class="inline-flex items-center gap-1.5 text-xs text-gray-500">

                <span class="h-2 w-2 rounded-full bg-green-500"></span>

                Preview Mode

            </span>

        </div>


        {{-- ============================================================
             EMAIL CANVAS
        ============================================================= --}}
        <div class="bg-[#f4f6f8] p-6 md:p-10">

            <div class="mx-auto w-full max-w-[600px]">

                {{-- ====================================================
                     EMAIL CONTAINER
                ==================================================== --}}
                <div
                    class="bg-white overflow-hidden"
                    style="
                        border-radius:8px;
                        box-shadow:0 2px 8px rgba(0,0,0,0.08);
                    "
                >

                    {{-- =================================================
                         GLOBAL HEADER
                    ================================================== --}}
                    <div class="email-preview-header">

                        @include('emails.partials.header')

                    </div>


                    {{-- =================================================
                         TEMPLATE BODY
                    ================================================== --}}
                    <div class="email-preview-body">

                        {!! $body !!}

                    </div>


                    {{-- =================================================
                         GLOBAL FOOTER
                    ================================================== --}}
                    <div class="email-preview-footer">

                        @include('emails.partials.footer')

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         PREVIEW INFORMATION
    ============================================================= --}}
    <div class="mt-5 flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">

        <svg
            class="w-5 h-5 text-blue-500 mt-0.5 shrink-0"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01
                   M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
        </svg>

        <div>

            <p class="text-sm font-medium text-blue-800">
                Preview Information
            </p>

            <p class="text-xs text-blue-700 mt-1 leading-5">
                Dynamic variables such as customer name, email,
                enquiry number and application details are replaced
                with sample values for preview purposes.
            </p>

        </div>

    </div>

</div>

@endsection


@push('styles')

<style>

    /*
    |--------------------------------------------------------------------------
    | Email Preview
    |--------------------------------------------------------------------------
    */

    .email-preview-body {
        width: 100%;
        overflow: hidden;
    }


    /*
    |--------------------------------------------------------------------------
    | Email Content
    |--------------------------------------------------------------------------
    */

    .email-preview-body img {
        max-width: 100%;
        height: auto;
    }


    .email-preview-body table {
        max-width: 100%;
    }


    .email-preview-body a {
        word-break: break-word;
    }


    /*
    |--------------------------------------------------------------------------
    | Header / Footer
    |--------------------------------------------------------------------------
    */

    .email-preview-header,
    .email-preview-footer {
        width: 100%;
        overflow: hidden;
    }


    .email-preview-header table,
    .email-preview-footer table {
        max-width: 100%;
    }


    /*
    |--------------------------------------------------------------------------
    | Mobile Preview
    |--------------------------------------------------------------------------
    */

    @media (max-width: 640px) {

        .email-preview-body {
            font-size: 14px;
        }

        .email-preview-body table {
            width: 100% !important;
        }

        .email-preview-body td {
            max-width: 100%;
        }

    }

</style>

@endpush