@extends('layouts.admin')

@section('title', 'Email Settings')

@section('breadcrumb')
    <x-breadcrumb :items="[
        ['label' => 'Home', 'route' => route('admin.dashboard')],
        ['label' => 'Communication'],
        ['label' => 'Email Settings'],
    ]" />
@endsection


@section('content')

    <div class="min-h-screen bg-[#f6f8f7]">

        {{-- ============================================================
            PAGE HEADER
        ============================================================= --}}
        <div class="border-b border-gray-200 bg-white">

            <div class="px-6 py-6 lg:px-8">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    {{-- Title --}}
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
                                        d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>

                            </div>

                            <span class="text-xs font-semibold uppercase tracking-wider text-[#00995c]">
                                Communication
                            </span>

                        </div>

                        <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                            Email Settings
                        </h1>

                        <p class="mt-1 text-sm text-gray-500">
                            Configure SMTP and system email delivery settings.
                        </p>

                    </div>


                    {{-- Status Badge --}}
                    <div>

                        @if(old('is_active', $emailSetting?->is_active ?? true))

                            <div class="inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-3.5 py-2 text-xs font-semibold text-green-700">

                                <span class="h-2 w-2 rounded-full bg-green-500"></span>

                                Email Sending Enabled

                            </div>

                        @else

                            <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-semibold text-gray-500">

                                <span class="h-2 w-2 rounded-full bg-gray-400"></span>

                                Email Sending Disabled

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
            MAIN CONTENT
        ============================================================= --}}
        <div class="px-6 py-7 lg:px-8">


            {{-- ========================================================
                SUCCESS
            ========================================================= --}}
            @if(session('success'))

                <div class="mb-6 overflow-hidden rounded-2xl border border-green-200 bg-green-50">

                    <div class="flex items-start gap-3 px-5 py-4">

                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-green-100">

                            <svg
                                class="h-4 w-4 text-green-600"
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

                        </div>

                        <div>

                            <p class="text-sm font-semibold text-green-800">
                                Success
                            </p>

                            <p class="mt-0.5 text-sm text-green-700">
                                {{ session('success') }}
                            </p>

                        </div>

                    </div>

                </div>

            @endif


            {{-- ========================================================
                ERROR
            ========================================================= --}}
            @if(session('error'))

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
                                Email Configuration Error
                            </p>

                            <p class="mt-0.5 text-sm text-red-700">
                                {{ session('error') }}
                            </p>

                        </div>

                    </div>

                </div>

            @endif


            {{-- ========================================================
                VALIDATION
            ========================================================= --}}
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


            {{-- ========================================================
                SETTINGS FORM
            ========================================================= --}}
            <form
                method="POST"
                action="{{ route('email_settings.update') }}"
                class="space-y-6"
            >

                @csrf
                @method('PUT')


                {{-- ====================================================
                    SMTP CONFIGURATION
                ===================================================== --}}
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                    {{-- Header --}}
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
                                        d="M4 6h16M4 10h16M4 14h10M4 18h7"
                                    />
                                </svg>

                            </div>

                            <div>

                                <h2 class="text-base font-semibold text-gray-900">
                                    SMTP Configuration
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Configure the mail server used by OOHAPP.
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- Body --}}
                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">


                        {{-- Mailer --}}
                        <div>

                            <label
                                for="mailer"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Mailer
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="mailer"
                                name="mailer"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                            >

                                <option
                                    value="smtp"
                                    @selected(old('mailer', $emailSetting?->mailer ?? 'smtp') === 'smtp')
                                >
                                    SMTP
                                </option>

                            </select>

                        </div>


                        {{-- Host --}}
                        <div>

                            <label
                                for="smtp_host"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                SMTP Host
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="smtp_host"
                                type="text"
                                name="host"
                                value="{{ old('host', $emailSetting?->host) }}"
                                placeholder="smtp.example.com"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                                required
                            >

                        </div>


                        {{-- Port --}}
                        <div>

                            <label
                                for="smtp_port"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                SMTP Port
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="smtp_port"
                                type="number"
                                name="port"
                                value="{{ old('port', $emailSetting?->port ?? 587) }}"
                                placeholder="587"
                                min="1"
                                max="65535"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                                required
                            >

                            <p class="mt-1.5 text-xs text-gray-400">
                                Common ports: 587 for TLS, 465 for SSL.
                            </p>

                        </div>


                        {{-- Encryption --}}
                        <div>

                            @php
                                $encryption = old(
                                    'encryption',
                                    $emailSetting?->encryption ?? 'tls'
                                );
                            @endphp

                            <label
                                for="encryption"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Encryption
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="encryption"
                                name="encryption"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                            >

                                <option
                                    value="tls"
                                    @selected($encryption === 'tls')
                                >
                                    TLS
                                </option>

                                <option
                                    value="ssl"
                                    @selected($encryption === 'ssl')
                                >
                                    SSL
                                </option>

                                <option
                                    value="null"
                                    @selected($encryption === 'null')
                                >
                                    None
                                </option>

                            </select>

                        </div>


                        {{-- Username --}}
                        <div>

                            <label
                                for="smtp_username"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                SMTP Username
                            </label>

                            <input
                                id="smtp_username"
                                type="text"
                                name="username"
                                value="{{ old('username', $emailSetting?->username) }}"
                                placeholder="noreply@example.com"
                                autocomplete="username"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                            >

                        </div>


                        {{-- Password --}}
                        <div>

                            <label
                                for="smtp_password"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                SMTP Password
                            </label>

                            <input
                                id="smtp_password"
                                type="password"
                                name="password"
                                autocomplete="new-password"
                                placeholder="{{ $emailSetting?->exists ? 'Leave blank to keep existing password' : 'SMTP password' }}"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                            >

                            <p class="mt-1.5 text-xs text-gray-400">
                                Leave blank to keep the existing password.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- ====================================================
                    SENDER INFORMATION
                ===================================================== --}}
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                    {{-- Header --}}
                    <div class="border-b border-gray-100 px-6 py-5">

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
                                        d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>

                            </div>

                            <div>

                                <h2 class="text-base font-semibold text-gray-900">
                                    Sender Information
                                </h2>

                                <p class="mt-0.5 text-xs text-gray-500">
                                    Configure the identity shown to email recipients.
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- Body --}}
                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">


                        {{-- From Name --}}
                        <div>

                            <label
                                for="from_name"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                From Name
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="from_name"
                                type="text"
                                name="from_name"
                                value="{{ old('from_name', $emailSetting?->from_name) }}"
                                placeholder="OOHAPP"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                                required
                            >

                            <p class="mt-1.5 text-xs text-gray-400">
                                Name recipients will see in their inbox.
                            </p>

                        </div>


                        {{-- From Email --}}
                        <div>

                            <label
                                for="from_email"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                From Email
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="from_email"
                                type="email"
                                name="from_email"
                                value="{{ old('from_email', $emailSetting?->from_email) }}"
                                placeholder="noreply@oohapp.com"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                                required
                            >

                        </div>


                        {{-- Reply To Name --}}
                        <div>

                            <label
                                for="reply_to_name"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Reply-To Name
                            </label>

                            <input
                                id="reply_to_name"
                                type="text"
                                name="reply_to_name"
                                value="{{ old('reply_to_name', $emailSetting?->reply_to_name) }}"
                                placeholder="OOHAPP Support"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                            >

                        </div>


                        {{-- Reply To Email --}}
                        <div>

                            <label
                                for="reply_to_email"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Reply-To Email
                            </label>

                            <input
                                id="reply_to_email"
                                type="email"
                                name="reply_to_email"
                                value="{{ old('reply_to_email', $emailSetting?->reply_to_email) }}"
                                placeholder="support@oohapp.com"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                            >

                        </div>

                    </div>

                </div>


                {{-- ====================================================
                    EMAIL STATUS
                ===================================================== --}}
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                    <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex items-start gap-3">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50">

                                <svg
                                    class="h-5 w-5 text-amber-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"
                                    />
                                </svg>

                            </div>

                            <div>

                                <h2 class="text-base font-semibold text-gray-900">
                                    Email Sending
                                </h2>

                                <p class="mt-1 text-sm text-gray-500">
                                    Enable or disable system email delivery.
                                </p>

                            </div>

                        </div>


                        {{-- Toggle --}}
                        <label class="relative inline-flex shrink-0 cursor-pointer items-center">

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                class="peer sr-only"
                                @checked(old('is_active', $emailSetting?->is_active ?? true))
                            >

                            <div class="h-7 w-12 rounded-full bg-gray-200 transition peer-checked:bg-[#00995c]"></div>

                            <div class="absolute left-0.5 top-0.5 h-6 w-6 rounded-full border border-gray-300 bg-white shadow-sm transition peer-checked:translate-x-5 peer-checked:border-white"></div>

                        </label>

                    </div>

                </div>


                {{-- ====================================================
                    FORM FOOTER
                ===================================================== --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <p class="text-xs text-gray-400">
                        Changes will apply to future outgoing emails.
                    </p>


                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#00995c] px-6 py-3 text-sm font-semibold text-white shadow-sm shadow-[#00995c]/20 transition hover:bg-[#008450] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#00995c]/30"
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

                        Save Email Settings

                    </button>

                </div>

            </form>


            {{-- ========================================================
                TEST EMAIL
            ========================================================= --}}
            <div class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                {{-- Header --}}
                <div class="border-b border-gray-100 px-6 py-5">

                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50">

                            <svg
                                class="h-5 w-5 text-purple-600"
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

                        <div>

                            <h2 class="text-base font-semibold text-gray-900">
                                Send Test Email
                            </h2>

                            <p class="mt-0.5 text-xs text-gray-500">
                                Verify that your SMTP configuration is working correctly.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Test Form --}}
                <form
                    method="POST"
                    action="{{ route('email_settings.test') }}"
                    class="p-6"
                >

                    @csrf

                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end">

                        <div class="flex-1">

                            <label
                                for="test_email"
                                class="mb-2 block text-sm font-medium text-gray-700"
                            >
                                Test Email Address
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="test_email"
                                type="email"
                                name="test_email"
                                value="{{ old('test_email') }}"
                                placeholder="you@example.com"
                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-[#00995c] focus:bg-white focus:ring-2 focus:ring-[#00995c]/10"
                                required
                            >

                            <p class="mt-1.5 text-xs text-gray-400">
                                A test message will be sent using the current SMTP configuration.
                            </p>

                        </div>


                        <button
                            type="submit"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-[#00995c] bg-white px-6 py-3 text-sm font-semibold text-[#00995c] transition hover:bg-[#00995c] hover:text-white focus:outline-none focus:ring-2 focus:ring-[#00995c]/20"
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
                                    d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>

                            Send Test Email

                        </button>

                    </div>

                </form>

            </div>


            {{-- ========================================================
                SECURITY NOTE
            ========================================================= --}}
            <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 p-4">

                <div class="flex items-start gap-3">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100">

                        <svg
                            class="h-4 w-4 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-5a2 2 0 00-2-2H6a2 2 0 00-2 2v5a2 2 0 002 2zm10-9V7a4 4 0 00-8 0v3h8z"
                            />
                        </svg>

                    </div>

                    <div>

                        <p class="text-xs font-semibold text-blue-800">
                            SMTP Security
                        </p>

                        <p class="mt-1 text-xs leading-5 text-blue-700">
                            SMTP passwords are not displayed here. Leave the password field blank when you only want to update other email settings.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
