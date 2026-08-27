@extends('layouts.admin')

@section('title', 'Create Email Template')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Home', 'route' => route('admin.dashboard')],
        ['label' => 'Settings', 'route' => route('admin.settings.razorpay')],
        ['label' => 'Email Templates', 'route' => route('email_templates.index')],
        ['label' => 'Create Template']
    ]" />
@endsection

@section('content')

        {{-- Quill --}}
        <link
            href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
            rel="stylesheet"
        />

        <div class="min-h-screen bg-[#f6f8f7]">

            {{-- Page Header --}}
            <div class="border-b border-gray-200 bg-white">
                <div class="px-6 lg:px-8 py-6">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#00995c]/10">
                                    <svg
                                        class="h-5 w-5 text-[#00995c]"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>

                                <span class="text-xs font-semibold uppercase tracking-wider text-[#00995c]">
                                    Email Management
                                </span>
                            </div>

                            <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                                Create Email Template
                            </h1>

                            <p class="mt-1 text-sm text-gray-500">
                                Create a reusable email template for OOHAPP notifications.
                            </p>
                        </div>

                        <a
                            href="{{ route('email_templates.index') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                                />
                            </svg>

                            Back to Templates
                        </a>

                    </div>

                </div>
            </div>


            {{-- Main --}}
            <div class="px-6 lg:px-8 py-7">

                {{-- Errors --}}
                @if($errors->any())
                    <div class="mb-6 overflow-hidden rounded-2xl border border-red-200 bg-red-50">

                        <div class="flex items-start gap-3 px-5 py-4">

                            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100">
                                <svg
                                    class="h-4 w-4 text-red-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-red-800">
                                    Please fix the following errors
                                </p>

                                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>

                        </div>
                    </div>
                @endif


                <form
                    method="POST"
                    action="{{ route('email_templates.store') }}"
                    x-data
                    id="emailTemplateForm"
                >

                    @csrf

                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">

                        {{-- =====================================================
                            LEFT : MAIN FORM
                        ====================================================== --}}
                        <div class="min-w-0">

                            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                                {{-- Card Header --}}
                                <div class="border-b border-gray-100 px-6 py-5">

                                    <div class="flex items-center gap-3">

                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#00995c]/10">
                                            <svg
                                                class="h-5 w-5 text-[#00995c]"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                        </div>

                                        <div>
                                            <h2 class="text-base font-semibold text-gray-900">
                                                Template Details
                                            </h2>

                                            <p class="mt-0.5 text-xs text-gray-500">
                                                Configure the identity, subject and content of your email.
                                            </p>
                                        </div>

                                    </div>

                                </div>


                                {{-- Form Body --}}
                                <div class="space-y-7 p-6">

                                    {{-- =================================================
                                        BASIC INFORMATION
                                    ================================================== --}}
                                    <div>

                                        <div class="mb-5">
                                            <h3 class="text-sm font-semibold text-gray-900">
                                                Basic Information
                                            </h3>

                                            <p class="mt-1 text-xs text-gray-500">
                                                These details are used internally to identify the template.
                                            </p>
                                        </div>


                                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                                            {{-- Name --}}
                                            <div class="md:col-span-2">

                                                <label
                                                    for="template_name"
                                                    class="mb-2 block text-sm font-medium text-gray-700"
                                                >
                                                    Template Name
                                                    <span class="text-red-500">*</span>
                                                </label>

                                                <input
                                                    id="template_name"
                                                    type="text"
                                                    name="name"
                                                    value="{{ old('name') }}"
                                                    placeholder="Customer Registration"
                                                    class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                                                    required
                                                >

                                                <p class="mt-1.5 text-xs text-gray-400">
                                                    Internal name used to identify this template.
                                                </p>

                                            </div>


                                            {{-- Key --}}
                                            <div>

                                                <label
                                                    for="template_key"
                                                    class="mb-2 block text-sm font-medium text-gray-700"
                                                >
                                                    Template Key
                                                    <span class="text-red-500">*</span>
                                                </label>

                                                <div class="relative">

                                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                                        <span class="text-sm text-gray-400">
                                                            #
                                                        </span>
                                                    </span>

                                                    <input
                                                        id="template_key"
                                                        type="text"
                                                        name="key"
                                                        value="{{ old('key') }}"
                                                        placeholder="customer_registered"
                                                        pattern="[a-z0-9_]+"
                                                        class="block w-full rounded-xl border-gray-200 bg-gray-50 py-3 pl-9 pr-4 font-mono text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                                                        required
                                                    >

                                                </div>

                                                <p class="mt-1.5 text-xs text-gray-400">
                                                    Lowercase, numbers and underscores only.
                                                </p>

                                            </div>


                                            {{-- Status --}}
                                            <div>

                                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                                    Template Status
                                                </label>

                                                <label
                                                    class="flex cursor-pointer items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition hover:border-[#00995c]/30 hover:bg-[#00995c]/5"
                                                >

                                                    <div>
                                                        <p class="text-sm font-medium text-gray-800">
                                                            Active Template
                                                        </p>

                                                        <p class="mt-0.5 text-xs text-gray-400">
                                                            Available for email sending
                                                        </p>
                                                    </div>

                                                    <div class="relative">

                                                        <input
                                                            type="checkbox"
                                                            name="is_active"
                                                            value="1"
                                                            class="peer sr-only"
                                                            {{ old('is_active', true) ? 'checked' : '' }}
                                                        >

                                                        <div class="h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-[#00995c]"></div>

                                                        <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full border border-gray-300 bg-white shadow-sm transition peer-checked:translate-x-5 peer-checked:border-white"></div>

                                                    </div>

                                                </label>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- Divider --}}
                                    <div class="border-t border-gray-100"></div>


                                    {{-- =================================================
                                        EMAIL CONTENT
                                    ================================================== --}}
                                    <div>

                                        <div class="mb-5">

                                            <h3 class="text-sm font-semibold text-gray-900">
                                                Email Content
                                            </h3>

                                            <p class="mt-1 text-xs text-gray-500">
                                                Define the subject and message that recipients will receive.
                                            </p>

                                        </div>


                                        {{-- Subject --}}
                                        <div class="mb-6">

                                            <label
                                                for="email_subject"
                                                class="mb-2 block text-sm font-medium text-gray-700"
                                            >
                                                Email Subject
                                                <span class="text-red-500">*</span>
                                            </label>

                                            <input
                                                id="email_subject"
                                                type="text"
                                                name="subject"
                                                value="{{ old('subject') }}"
                                                placeholder="Welcome to {{ config('app.name', 'OOHAPP') }}"
                                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                                                required
                                            >

                                        </div>


                                        {{-- Body --}}
                                        <!-- <div>

                                            <div class="mb-2 flex items-center justify-between">

                                                <label class="block text-sm font-medium text-gray-700">
                                                    Email Body
                                                    <span class="text-red-500">*</span>
                                                </label>

                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-500">

                                                    <span class="h-1.5 w-1.5 rounded-full bg-[#00995c]"></span>

                                                    Rich Text Editor

                                                </span>

                                            </div>


                                            {{-- Quill --}}
                                            <div class="email-editor-wrapper overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition focus-within:border-[#00995c] focus-within:ring-2 focus-within:ring-[#00995c]/10">

                                                <div
                                                    id="email_editor"
                                                    class="min-h-[320px]"
                                                ></div>

                                            </div>


                                            {{-- Actual field --}}
                                            <textarea
                                                id="email_body"
                                                name="body"
                                                class="hidden"
                                                required
                                            >{{ old('body') }}</textarea>


                                            <div class="mt-3 flex items-start gap-2 rounded-xl bg-[#00995c]/5 px-3 py-2.5">

                                                <svg
                                                    class="mt-0.5 h-4 w-4 shrink-0 text-[#00995c]"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                    />
                                                </svg>

                                                <p class="text-xs leading-5 text-gray-500">
                                                    Use the shortcodes on the right to insert dynamic values into your email.
                                                </p>

                                            </div>

                                        </div> -->

                                        {{-- Body --}}
    <div>

        <div class="mb-2 flex items-center justify-between">

            <label class="block text-sm font-medium text-gray-700">
                Email Body
                <span class="text-red-500">*</span>
            </label>

            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-500">

                <span class="h-1.5 w-1.5 rounded-full bg-[#00995c]"></span>

                Rich Text Editor

            </span>

        </div>


        {{-- Email Editor Canvas --}}
        <div
            class="email-editor-wrapper overflow-hidden rounded-2xl border border-gray-200 bg-[#f4f6f8] shadow-sm transition focus-within:border-[#00995c] focus-within:ring-2 focus-within:ring-[#00995c]/10"
        >

            {{-- ============================================================
                 EMAIL HEADER
            ============================================================= --}}
            <div class="email-template-header bg-white">

                @include('emails.partials.header')

            </div>


            {{-- ============================================================
                 EDITABLE EMAIL BODY
            ============================================================= --}}
            <div class="bg-white px-6 py-5">

                <div
                    id="email_editor"
                    class="min-h-[320px]"
                ></div>

            </div>


            {{-- ============================================================
                 EMAIL FOOTER
            ============================================================= --}}
            <div class="email-template-footer bg-white">

                @include('emails.partials.footer')

            </div>

        </div>


        {{-- Actual form field --}}
        <textarea
            id="email_body"
            name="body"
            class="hidden"
            required
        >{{ old('body') }}</textarea>


        {{-- Information --}}
        <div class="mt-3 flex items-start gap-2 rounded-xl bg-[#00995c]/5 px-3 py-2.5">

            <svg
                class="mt-0.5 h-4 w-4 shrink-0 text-[#00995c]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>

            <p class="text-xs leading-5 text-gray-500">
                Header and footer are automatically included in every email.
                Use the shortcodes on the right to insert dynamic values into your email body.
            </p>

        </div>

    </div>
                                    </div>

                                </div>


                                {{-- Footer --}}
                                <div class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50/70 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">

                                    <p class="text-xs text-gray-400">
                                        Fields marked with <span class="text-red-500">*</span> are required.
                                    </p>

                                    <div class="flex items-center justify-end gap-3">

                                        <a
                                            href="{{ route('email_templates.index') }}"
                                            class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50"
                                        >
                                            Cancel
                                        </a>

                                        <button
                                            type="submit"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#00995c] px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-[#00995c]/20 transition hover:bg-[#008450] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#00995c]/30"
                                        >

                                            <svg
                                                class="h-4 w-4"
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

                                            Save Template

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- =====================================================
                            RIGHT : SHORTCODES
                        ====================================================== --}}
                        <div>

                            <div class="sticky top-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                                {{-- Header --}}
                                <div class="border-b border-gray-100 px-5 py-5">

                                    <div class="flex items-start justify-between gap-3">

                                        <div class="flex items-center gap-3">

                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50">

                                                <svg
                                                    class="h-5 w-5 text-blue-600"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M8 9l3 3-3 3m5 0h3M5 4h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z"
                                                    />
                                                </svg>

                                            </div>

                                            <div>
                                                <h2 class="text-sm font-semibold text-gray-900">
                                                    Available Shortcodes
                                                </h2>

                                                <p class="mt-0.5 text-xs text-gray-500">
                                                    Insert dynamic values
                                                </p>
                                            </div>

                                        </div>

                                    </div>

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


                                {{-- Shortcodes --}}
                                <div class="max-h-[560px] space-y-2 overflow-y-auto p-4">

                                    @foreach($shortcodes as $code => $description)

                                        <button
                                            type="button"
                                            onclick="insertShortcode('{{ $code }}')"
                                            class="group w-full rounded-xl border border-gray-100 bg-gray-50/70 p-3 text-left transition duration-200 hover:border-[#00995c]/30 hover:bg-[#00995c]/5"
                                        >

                                            <div class="flex items-center justify-between gap-3">

                                                <code class="min-w-0 truncate text-xs font-semibold text-[#008c56]">
                                                    {{ $code }}
                                                </code>

                                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-gray-400 shadow-sm transition group-hover:bg-[#00995c] group-hover:text-white">

                                                    <svg
                                                        class="h-3.5 w-3.5"
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

                                                </span>

                                            </div>

                                            <p class="mt-1.5 text-[11px] text-gray-400">
                                                {{ $description }}
                                            </p>

                                        </button>

                                    @endforeach

                                </div>


                                {{-- Info --}}
                                <div class="border-t border-gray-100 p-4">

                                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-3.5">

                                        <div class="flex gap-2.5">

                                            <svg
                                                class="mt-0.5 h-4 w-4 shrink-0 text-blue-600"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>

                                            <div>
                                                <p class="text-xs font-semibold text-blue-800">
                                                    Quick Tip
                                                </p>

                                                <p class="mt-1 text-[11px] leading-5 text-blue-700">
                                                    Place your cursor in the editor and click any shortcode to insert it automatically.
                                                </p>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        {{-- ================================================================
            QUILL
        ================================================================= --}}
        @push('scripts')

            <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

            <script>

                document.addEventListener('DOMContentLoaded', function () {

                    const editorElement = document.getElementById('email_editor');
                    const bodyInput = document.getElementById('email_body');
                    const form = document.getElementById('emailTemplateForm');

                    if (!editorElement || !bodyInput) {
                        console.error('Email editor elements not found.');
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Initialize Quill
                    |--------------------------------------------------------------------------
                    */

                    const quill = new Quill('#email_editor', {

                        theme: 'snow',

                        placeholder: 'Start writing your email content...',

                        modules: {

                            toolbar: [

                                [
                                    {
                                        header: [1, 2, 3, false]
                                    }
                                ],

                                [
                                    'bold',
                                    'italic',
                                    'underline',
                                    'strike'
                                ],

                                [
                                    {
                                        color: []
                                    },
                                    {
                                        background: []
                                    }
                                ],

                                [
                                    {
                                        align: []
                                    }
                                ],

                                [
                                    {
                                        list: 'ordered'
                                    },
                                    {
                                        list: 'bullet'
                                    }
                                ],

                                [
                                    'blockquote'
                                ],

                                [
                                    'link'
                                ],

                                [
                                    'clean'
                                ]

                            ]

                        }

                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Global editor
                    |--------------------------------------------------------------------------
                    */

                    window.emailBodyEditor = quill;


                    /*
                    |--------------------------------------------------------------------------
                    | Load Old Content
                    |--------------------------------------------------------------------------
                    */

                    const existingContent = bodyInput.value.trim();

                    if (existingContent !== '') {

                        quill.clipboard.dangerouslyPasteHTML(
                            existingContent
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Sync Editor -> Textarea
                    |--------------------------------------------------------------------------
                    */

                    quill.on('text-change', function () {

                        bodyInput.value = quill.root.innerHTML;

                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Form Submit
                    |--------------------------------------------------------------------------
                    */

                    if (form) {

                        form.addEventListener('submit', function () {

                            bodyInput.value = quill.root.innerHTML;

                        });

                    }

                });


                /*
                |--------------------------------------------------------------------------
                | Insert Shortcode
                |--------------------------------------------------------------------------
                */

                function insertShortcode(code) {

                    const editor = window.emailBodyEditor;

                    if (!editor) {

                        console.error('Email editor is not initialized.');

                        return;

                    }


                    editor.focus();


                    /*
                    |--------------------------------------------------------------------------
                    | Get Cursor
                    |--------------------------------------------------------------------------
                    */

                    let range = editor.getSelection(true);


                    /*
                    |--------------------------------------------------------------------------
                    | Fallback to End
                    |--------------------------------------------------------------------------
                    */

                    if (!range) {

                        const length = editor.getLength();

                        range = {
                            index: Math.max(0, length - 1),
                            length: 0
                        };

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Insert
                    |--------------------------------------------------------------------------
                    */

                    editor.insertText(
                        range.index,
                        code,
                        'user'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Move Cursor
                    |--------------------------------------------------------------------------
                    */

                    editor.setSelection(
                        range.index + code.length,
                        0,
                        'user'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Sync textarea
                    |--------------------------------------------------------------------------
                    */

                    const bodyInput = document.getElementById('email_body');

                    if (bodyInput) {

                        bodyInput.value = editor.root.innerHTML;

                    }

                }

            </script>


            {{-- Quill UI Customization --}}
            <style>

                /* ============================================================
                   Quill Container
                ============================================================ */

                .email-editor-wrapper .ql-toolbar.ql-snow {

                    border: 0;

                    border-bottom: 1px solid #f0f0f0;

                    background: #fafafa;

                    padding: 12px 14px;

                }


                .email-editor-wrapper .ql-container.ql-snow {

                    border: 0;

                    font-family: inherit;

                    font-size: 14px;

                }


                .email-editor-wrapper .ql-editor {

                    min-height: 320px;

                    padding: 18px;

                    color: #374151;

                    line-height: 1.7;

                }


                .email-editor-wrapper .ql-editor.ql-blank::before {

                    color: #9ca3af;

                    font-style: normal;

                    left: 18px;

                    right: 18px;

                }


                /* ============================================================
                   Toolbar
                ============================================================ */

                .email-editor-wrapper .ql-snow .ql-picker {

                    color: #6b7280;

                }


                .email-editor-wrapper .ql-snow .ql-stroke {

                    stroke: #6b7280;

                }


                .email-editor-wrapper .ql-snow .ql-fill {

                    fill: #6b7280;

                }


                .email-editor-wrapper .ql-snow button:hover .ql-stroke,

                .email-editor-wrapper .ql-snow .ql-picker-label:hover,

                .email-editor-wrapper .ql-snow button.ql-active .ql-stroke {

                    stroke: #00995c;

                }


                .email-editor-wrapper .ql-snow button:hover .ql-fill,

                .email-editor-wrapper .ql-snow button.ql-active .ql-fill {

                    fill: #00995c;

                }


                /* ============================================================
                   Scrollbar
                ============================================================ */

                .max-h-\[560px\]::-webkit-scrollbar {

                    width: 5px;

                }


                .max-h-\[560px\]::-webkit-scrollbar-track {

                    background: transparent;

                }


                .max-h-\[560px\]::-webkit-scrollbar-thumb {

                    background: #d1d5db;

                    border-radius: 999px;

                }


                .max-h-\[560px\]::-webkit-scrollbar-thumb:hover {

                    background: #9ca3af;

                }

            </style>

        @endpush

@endsection
