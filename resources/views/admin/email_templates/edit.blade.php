@extends('layouts.admin')

@section('title', 'Edit Email Template')

@section('breadcrumb')


@endsection


@section('content')

                <div class="min-h-screen bg-[#f6f8f7]">

                    <div class="border-b border-gray-200 bg-white">

                        <div class="px-6 py-6 lg:px-8">

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                <div>

                                    <div class="mb-2 flex items-center gap-2">

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
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-8.5a2.121 2.121 0 013 3L12 14l-4 1 1-4 7.5-7.5z"
                                                />
                                            </svg>

                                        </div>

                                        <span class="text-xs font-semibold uppercase tracking-wider text-[#00995c]">
                                            Email Management
                                        </span>

                                    </div>

                                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                                        Edit Email Template
                                    </h1>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Update the content and settings of this email template.
                                    </p>

                                </div>


                                <div class="flex items-center gap-2">

                                    <a
                                        href="{{ route('email_templates.preview', $emailTemplate->id) }}"
                                        target="_blank"
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
                                                stroke-width="1.8"
                                                d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"
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

                                        Back

                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="px-6 py-7 lg:px-8">

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

                                                <li>
                                                    {{ $error }}
                                                </li>

                                            @endforeach

                                        </ul>

                                    </div>

                                </div>

                            </div>

                        @endif

                        <form
                            method="POST"
                            action="{{ route('email_templates.update', $emailTemplate->id) }}"
                            id="emailTemplateForm"
                        >

                            @csrf
                            @method('PUT')


                            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">

                                <div class="min-w-0">

                                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">


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
                                                        Update the identity, subject and content of your email.
                                                    </p>

                                                </div>

                                            </div>

                                        </div>


                                        <div class="space-y-7 p-6">

                                            <div>

                                                <div class="mb-5">

                                                    <h3 class="text-sm font-semibold text-gray-900">
                                                        Basic Information
                                                    </h3>

                                                    <p class="mt-1 text-xs text-gray-500">
                                                        Update the details used to identify this template.
                                                    </p>

                                                </div>


                                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">


                                                    {{-- Template Name --}}
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
                                                            value="{{ old('name', $emailTemplate->name) }}"
                                                            placeholder="Customer Registration"
                                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                                                            required
                                                        >

                                                        <p class="mt-1.5 text-xs text-gray-400">
                                                            Internal name used to identify this template.
                                                        </p>

                                                    </div>


                                                    {{-- Template Key --}}
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
                                                                value="{{ old('key', $emailTemplate->key) }}"
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

                                                        <label class="flex cursor-pointer items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 transition hover:border-[#00995c]/30 hover:bg-[#00995c]/5">

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
                                                                    {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}
                                                                >

                                                                <div class="h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-[#00995c]"></div>

                                                                <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full border border-gray-300 bg-white shadow-sm transition peer-checked:translate-x-5 peer-checked:border-white"></div>

                                                            </div>

                                                        </label>

                                                    </div>

                                                </div>

                                            </div>

                                            <div class="border-t border-gray-100"></div>


                                            <div>

                                                <div class="mb-5">

                                                    <h3 class="text-sm font-semibold text-gray-900">
                                                        Email Content
                                                    </h3>

                                                    <p class="mt-1 text-xs text-gray-500">
                                                        Update the subject and message that recipients will receive.
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
                                                        value="{{ old('subject', $emailTemplate->subject) }}"
                                                        placeholder="Welcome to {{ config('app.name', 'OOHAPP') }}"
                                                        class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                                                        required
                                                    >

                                                </div>

            {{-- Email Body --}}
         {{-- Email Body --}}
        {{-- Email Body --}}
    <div>

        <div class="mb-2 flex items-center justify-between">

            <label
                class="block text-sm font-medium text-gray-700"
            >
                Email Body
                <span class="text-red-500">*</span>
            </label>

            <span
                class="inline-flex items-center gap-1.5
                       rounded-full bg-gray-50 border border-gray-200
                       px-2.5 py-1 text-[11px] font-medium text-gray-500"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-[#00995c]"></span>
                Email Composer
            </span>

        </div>


        {{-- ============================================================
             EMAIL CANVAS
        ============================================================ --}}
        <div class="email-composer-wrapper">

            {{-- ========================================================
                 HEADER
            ========================================================= --}}
            <div class="email-composer-header">

                @include('emails.partials.header')

            </div>


            {{-- ========================================================
                 BODY / QUILL
            ========================================================= --}}
            <div class="email-composer-body">

                {{-- Toolbar --}}
                <div
                    id="email_toolbar"
                    class="email-editor-toolbar"
                >

                    <span class="ql-formats">

                        <select class="ql-header">
                            <option selected></option>
                            <option value="1">Heading 1</option>
                            <option value="2">Heading 2</option>
                            <option value="3">Heading 3</option>
                        </select>

                    </span>

                    <span class="email-toolbar-divider"></span>

                    <span class="ql-formats">

                        <button
                            type="button"
                            class="ql-bold"
                        ></button>

                        <button
                            type="button"
                            class="ql-italic"
                        ></button>

                        <button
                            type="button"
                            class="ql-underline"
                        ></button>

                    </span>

                    <span class="email-toolbar-divider"></span>

                    <span class="ql-formats">

                        <select class="ql-color"></select>

                        <select class="ql-background"></select>

                    </span>

                    <span class="email-toolbar-divider"></span>

                    <span class="ql-formats">

                        <button
                            type="button"
                            class="ql-align"
                        ></button>

                        <button
                            type="button"
                            class="ql-align"
                            value="center"
                        ></button>

                        <button
                            type="button"
                            class="ql-align"
                            value="right"
                        ></button>

                    </span>

                    <span class="email-toolbar-divider"></span>

                    <span class="ql-formats">

                        <button
                            type="button"
                            class="ql-list"
                            value="ordered"
                        ></button>

                        <button
                            type="button"
                            class="ql-list"
                            value="bullet"
                        ></button>

                    </span>

                    <span class="email-toolbar-divider"></span>

                    <span class="ql-formats">

                        <button
                            type="button"
                            class="ql-blockquote"
                        ></button>

                        <button
                            type="button"
                            class="ql-link"
                        ></button>

                        <button
                            type="button"
                            class="ql-clean"
                        ></button>

                    </span>

                </div>


                {{-- Editor --}}
                <div
                    id="email_editor"
                    class="email-editor"
                ></div>

            </div>


            {{-- ========================================================
                 FOOTER
            ========================================================= --}}
            <div class="email-composer-footer">

                @include('emails.partials.footer')

            </div>

        </div>


        {{-- Actual database field --}}
        <textarea
            id="email_body"
            name="body"
            class="hidden"
            required
        >{{ old('body', $emailTemplate->body) }}</textarea>


        {{-- Help --}}
        <div
            class="mt-3 flex items-start gap-2
                   rounded-xl border border-[#00995c]/10
                   bg-[#00995c]/5 px-3 py-2.5"
        >

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
                    d="M13 16h-1v-4h-1m1-4h.01
                       M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>

            <p class="text-xs leading-5 text-gray-500">
                Header and footer are automatically included in every email.
                Use the shortcodes on the right to insert dynamic values.
            </p>

        </div>

    </div>

                                            </div>

                                        </div>



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

                                                    Update Template

                                                </button>

                                            </div>

                                        </div>

                                    </div>

                                </div>



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


                                        {{-- Shortcodes --}}
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


               @push('scripts')

                <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

                <script>
                document.addEventListener('DOMContentLoaded', function () {

                    const editorElement = document.getElementById('email_editor');
                    const bodyInput = document.getElementById('email_body');

                    if (!editorElement || !bodyInput) {
                        return;
                    }

                    const quill = new Quill('#email_editor', {

                        theme: 'snow',

                        placeholder: 'Write your email content here...',

                        modules: {
                            toolbar: '#email_toolbar'
                        }

                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Make editor globally available
                    |--------------------------------------------------------------------------
                    */

                    window.emailBodyEditor = quill;


                    /*
                    |--------------------------------------------------------------------------
                    | Load existing content
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
                    | Sync editor with hidden textarea
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

                    const form = bodyInput.closest('form');

                    if (form) {

                        form.addEventListener('submit', function () {

                            bodyInput.value = quill.root.innerHTML;

                        });

                    }

                });
                </script>



                            <style>

                                .email-editor-wrapper .tox-tinymce {

                                    border: 0 !important;

                                    border-radius: 0 !important;

                                    box-shadow: none !important;

                                }



                                .email-editor-wrapper .tox .tox-editor-header {

                                    border-bottom: 1px solid #f0f0f0 !important;

                                    background: #fafafa !important;

                                    box-shadow: none !important;

                                }



                                .email-editor-wrapper .tox .tox-toolbar,

                                .email-editor-wrapper .tox .tox-toolbar__overflow,

                                .email-editor-wrapper .tox .tox-toolbar__primary {

                                    background: #fafafa !important;

                                }


                                .email-editor-wrapper .tox .tox-tbtn {

                                    border-radius: 7px !important;

                                }


                                .email-editor-wrapper .tox .tox-tbtn:hover,

                                .email-editor-wrapper .tox .tox-tbtn--enabled {

                                    background: rgba(0, 153, 92, 0.08) !important;

                                }




                                .email-editor-wrapper .tox .tox-edit-area {

                                    border: 0 !important;

                                }


                                .email-editor-wrapper .tox .tox-edit-area__iframe {

                                    background: #ffffff !important;

                                }




                                .email-editor-wrapper .tox .tox-statusbar {

                                    border-top: 1px solid #f0f0f0 !important;

                                    background: #fafafa !important;

                                }



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

            @push('styles')

                <link
                    href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
                    rel="stylesheet"
                />

                <style>

                    /* ============================================================
                       EMAIL EDITOR
                    ============================================================ */

                    .email-editor-wrapper {
                        width: 100%;
                        overflow: hidden;

                        background: #ffffff;

                        border: 1px solid #e5e7eb;
                        border-radius: 16px;

                        box-shadow:
                            0 1px 2px rgba(0, 0, 0, 0.03);

                        transition:
                            border-color 180ms ease,
                            box-shadow 180ms ease;
                    }


                    .email-editor-wrapper:focus-within {
                        border-color: rgba(0, 153, 92, 0.45);

                        box-shadow:
                            0 0 0 3px rgba(0, 153, 92, 0.08),
                            0 4px 12px rgba(0, 0, 0, 0.04);
                    }


                    /* ============================================================
                       TOOLBAR
                    ============================================================ */

                    .email-editor-toolbar {
                        min-height: 48px;

                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;

                        gap: 2px;

                        padding: 6px 10px;

                        background: #fafafa;

                        border-bottom: 1px solid #eeeeee;
                    }


                    .email-editor-toolbar .ql-formats {
                        display: inline-flex;

                        align-items: center;

                        margin-right: 0 !important;
                    }


                    .email-editor-toolbar button,
                    .email-editor-toolbar select {
                        transition:
                            background-color 150ms ease,
                            border-color 150ms ease;
                    }


                    .email-editor-toolbar button {
                        width: 30px;
                        height: 30px;

                        border-radius: 7px;
                    }


                    .email-editor-toolbar button:hover {
                        background: #eeeeee;
                    }


                    .email-editor-toolbar button.ql-active {
                        background: rgba(0, 153, 92, 0.10);
                    }


                    .email-editor-toolbar .ql-picker {
                        height: 30px;
                    }


                    .email-editor-toolbar .ql-picker-label {
                        border-radius: 7px;

                        padding: 2px 6px;
                    }


                    .email-editor-toolbar .ql-picker-label:hover {
                        background: #eeeeee;
                    }


                    .email-toolbar-divider {
                        width: 1px;
                        height: 22px;

                        margin: 0 5px;

                        background: #e5e7eb;
                    }


                    /* ============================================================
                       EDITOR CANVAS
                    ============================================================ */

                    .email-editor-canvas {
                        background: #ffffff;
                    }


                    .email-editor {
                        min-height: 360px;
                    }


                    .email-editor.ql-container {
                        border: 0 !important;
                    }


                    .email-editor .ql-editor {
                        min-height: 360px;

                        padding: 24px 26px;

                        color: #374151;

                        font-family:
                            Arial,
                            Helvetica,
                            sans-serif;

                        font-size: 14px;

                        line-height: 1.7;
                    }


                    .email-editor .ql-editor:focus {
                        outline: none;
                    }


                    .email-editor .ql-editor.ql-blank::before {
                        left: 26px;

                        color: #9ca3af;

                        font-family:
                            Arial,
                            Helvetica,
                            sans-serif;

                        font-size: 14px;

                        font-style: normal;
                    }


                    /* ============================================================
                       CONTENT
                    ============================================================ */

                    .email-editor .ql-editor h1 {
                        font-size: 26px;

                        font-weight: 700;

                        line-height: 1.3;

                        margin-bottom: 12px;
                    }


                    .email-editor .ql-editor h2 {
                        font-size: 22px;

                        font-weight: 700;

                        line-height: 1.35;

                        margin-bottom: 10px;
                    }


                    .email-editor .ql-editor h3 {
                        font-size: 18px;

                        font-weight: 600;

                        line-height: 1.4;

                        margin-bottom: 8px;
                    }


                    .email-editor .ql-editor p {
                        margin-bottom: 10px;
                    }


                    .email-editor .ql-editor a {
                        color: #00995c;

                        text-decoration: underline;
                    }


                    /* ============================================================
                       MOBILE
                    ============================================================ */

                    @media (max-width: 640px) {

                        .email-editor-toolbar {
                            padding: 6px;

                            gap: 1px;
                        }

                        .email-toolbar-divider {
                            margin: 0 2px;
                        }

                        .email-editor .ql-editor {
                            padding: 20px;
                        }

                    }


                    

                </style>

            @endpush

@endsection
