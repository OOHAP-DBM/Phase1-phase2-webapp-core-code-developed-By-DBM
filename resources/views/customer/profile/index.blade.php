@extends('layouts.customer')

@section('title', 'Personal Information')
<style>
    /* =====================================================
       PERSONAL INFORMATION PAGE
       Consistent spacing + alignment
       ===================================================== */

    .profile-form {
        width: 100%;
    }

    /* -----------------------------
       Main form spacing
    ----------------------------- */

    .profile-form .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 32px;
        row-gap: 0;
        width: 100%;
    }

    .profile-form .profile-column {
        min-width: 0;
        width: 100%;
    }

    /* -----------------------------
       Section heading
    ----------------------------- */

    .profile-form .profile-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 20px;
        margin-bottom: 22px;
    }

    .profile-form .profile-section-title .section-bar {
        width: 5px;
        height: 19px;
        flex: 0 0 5px;
        border-radius: 999px;
        background: #22c55e;
    }

    .profile-form .profile-section-title h3 {
        margin: 0;
        font-size: 14px;
        line-height: 20px;
        font-weight: 600;
        color: #1f2937;
    }

    /* -----------------------------
       Every field
    ----------------------------- */

    .profile-form .profile-field {
        width: 100%;
        margin-bottom: 22px;
    }

    .profile-form .profile-field:last-child {
        margin-bottom: 0;
    }

    .profile-form .profile-field > label {
        display: block;
        margin: 0 0 8px 0;
        font-size: 13px;
        line-height: 18px;
        font-weight: 500;
        color: #374151;
    }

    /* -----------------------------
       Inputs
    ----------------------------- */

    .profile-form input[type="text"],
    .profile-form input[type="email"],
    .profile-form input[type="password"] {
        width: 100%;
        height: 44px;
        min-height: 44px;
        padding: 0 12px;
        box-sizing: border-box;

        border: 1px solid #e5e7eb;
        border-radius: 8px;

        background: #fff;
        color: #1f2937;

        font-size: 13px;
        line-height: 20px;

        outline: none;
        transition:
            border-color .2s ease,
            box-shadow .2s ease,
            background-color .2s ease;
    }

    .profile-form input::placeholder {
        color: #9ca3af;
        opacity: 1;
    }

    .profile-form input:focus {
        border-color: #22c55e !important;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .08);
        outline: none !important;
    }

    .profile-form input[readonly] {
        background: #f9fafb;
        color: #6b7280;
    }

    .profile-form input:disabled {
        background: #f8fafc;
        color: #6b7280;
        cursor: not-allowed;
    }

    /* -----------------------------
       Input + Verify row
    ----------------------------- */

    .profile-form .verify-row {
        display: flex;
        align-items: center;
        gap: 14px;
        width: 100%;
    }

    .profile-form .verify-input {
        flex: 1;
        min-width: 0;
    }

    .profile-form .verify-action {
        width: 82px;
        min-width: 82px;
        flex: 0 0 82px;

        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .profile-form .verify-action a {
        font-size: 13px;
        line-height: 18px;
        font-weight: 500;
        white-space: nowrap;
    }

    /* -----------------------------
       Verified icon
    ----------------------------- */

    .profile-form .verified-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        background: #ecfdf5;
        color: #009a5c;
    }

    /* -----------------------------
       Password row
    ----------------------------- */

    .profile-form .password-row {
        display: flex;
        align-items: center;
        gap: 14px;
        width: 100%;
    }

    .profile-form .password-input {
        flex: 1;
        min-width: 0;
    }

    .profile-form .password-action {
        width: 120px;
        min-width: 120px;
        flex: 0 0 120px;

        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .profile-form .password-action a {
        font-size: 13px;
        line-height: 18px;
        font-weight: 500;
        white-space: nowrap;
    }

    /* -----------------------------
       Profile picture
    ----------------------------- */

    .profile-form .avatar-box {
        min-height: 84px;
        width: 100%;

        display: flex;
        align-items: center;

        gap: 16px;

        padding: 14px 16px;
        box-sizing: border-box;

        border: 1px solid #e5e7eb;
        border-radius: 8px;

        background: #fafafa;
    }

    .profile-form .avatar-preview {
        width: 56px;
        height: 56px;
        min-width: 56px;

        position: relative;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;

        border: 1px solid #d1d5db;
        border-radius: 50%;

        background: #fff;
    }

    .profile-form .avatar-actions {
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .profile-form .avatar-links {
        display: flex;
        align-items: center;
        gap: 12px;
        height: 18px;
    }

    .profile-form .avatar-links a,
    .profile-form .avatar-links label {
        font-size: 12px;
        line-height: 18px;
        font-weight: 500;
    }

    .profile-form .avatar-filename {
        display: block;
        max-width: 220px;
        margin-top: 4px;

        font-size: 12px;
        line-height: 17px;
        color: #6b7280;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .profile-form .avatar-help {
        margin-top: 2px;

        font-size: 11px;
        line-height: 16px;
        color: #9ca3af;
    }

    /* -----------------------------
       Footer
    ----------------------------- */

    .profile-form .profile-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 20px;

        margin-top: 30px;
        padding-top: 20px;

        border-top: 1px solid #f0f1f3;
    }

    .profile-form .profile-footer p {
        margin: 0;
        font-size: 11px;
        line-height: 17px;
        color: #9ca3af;
    }

    /* -----------------------------
       Modal spacing
    ----------------------------- */

    .modal-container {
        box-sizing: border-box;
    }

    .modal-container .password-toggle {
        position: absolute;
        right: 10px;
        top: 30px;

        width: 30px;
        height: 30px;

        display: flex;
        align-items: center;
        justify-content: center;

        color: #6b7280;
        background: transparent;
        border: 0;
        cursor: pointer;
    }

    /* -----------------------------
       Responsive
    ----------------------------- */

    @media (max-width: 900px) {
        .profile-form .profile-grid {
            grid-template-columns: 1fr;
            row-gap: 28px;
        }

        .profile-form .profile-column {
            width: 100%;
        }
    }

    @media (max-width: 640px) {
        .profile-form {
            padding: 20px !important;
        }

        .profile-form .profile-grid {
            row-gap: 24px;
        }

        .profile-form .verify-row,
        .profile-form .password-row {
            gap: 8px;
        }

        .profile-form .verify-action {
            width: auto;
            min-width: auto;
            flex: 0 0 auto;
        }

        .profile-form .password-action {
            width: auto;
            min-width: auto;
            flex: 0 0 auto;
        }

        .profile-form .profile-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .profile-form .profile-footer button {
            width: 100%;
        }
    }
</style>


@section('content')
<div class="flex justify-center">

    {{-- Breadcrumb --}}
    <div class="w-full max-w-none">
        <div class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="text-decoration-none">Home</a>
        >
        <a href="{{ route('customer.dashboard') }}" class="text-decoration-none">My Account</a>
        >
        Profile
        >
        <span class="text-gray-800">Personal Information</span>
    </div>
    {{-- Card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm w-full overflow-hidden">

    {{-- ================= HEADER ================= --}}
    <div class="px-6 py-5 border-b border-gray-100">

        <h2 class="text-lg font-semibold text-gray-800">
            Personal Information
        </h2>

        <p class="text-sm text-gray-500 mt-1">
            Hey! Please provide your information to create a customized OOHAPP experience.
        </p>

    </div>


    <form action="{{ route('customer.profile.update') }}"
      method="POST"
      enctype="multipart/form-data"
      class="profile-form p-6">


        @csrf
        @method('PUT')


        {{-- ================= PROFILE PICTURE ================= --}}
        
<div class="profile-field mb-7">

            <label class="block text-sm font-medium text-gray-700 mb-2">
                Profile picture
            </label>

           <div class="avatar-box">


                @php
                    $avatar = auth()->user()->avatar;
                    $avatarUrl = $avatar ? asset('storage/'.$avatar).'?v='.time() : null;
                @endphp


                {{-- Avatar --}}
                <div id="avatarWrapper"
                     class="avatar-preview">

                    <img id="avatarPreviewImg"
                         src="{{ $avatarUrl }}"
                         class="w-full h-full object-cover absolute inset-0 {{ $avatar ? '' : 'hidden' }}"
                         alt="avatar"
                    />

                    <div id="avatarFallback"
                         class="absolute inset-0 flex items-center justify-center {{ $avatar ? 'hidden' : '' }}">

                        <svg class="w-8 h-8 text-gray-400"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="1.7"
                                  d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804
                                  M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>

                        </svg>

                    </div>
                </div>


                {{-- Avatar Actions --}}
                <div class="min-w-0 avatar-actions">

                    <div class="flex items-center gap-3">

                        @if(auth()->user()->avatar)

                            <a href="javascript:void(0)"
                               onclick="removeAvatar()"
                               class="text-xs font-medium text-red-500 hover:text-red-600 hover:underline">
                                Remove
                            </a>

                            <span class="text-gray-300">|</span>

                        @endif


                        <label for="profileImage"
                               class="text-xs font-medium text-blue-600
                                      hover:text-blue-700 hover:underline cursor-pointer">

                            {{ auth()->user()->avatar ? 'Change' : 'Upload' }}

                        </label>

                    </div>


                    <span id="avatarFileName"
                          class="block text-xs text-gray-500 truncate max-w-[220px] mt-1">
                    </span>


                    <p class="text-[11px] text-gray-400 mt-1">
                        JPG, PNG or WEBP
                    </p>

                </div>


                <input type="file"
                       id="profileImage"
                       name="avatar"
                       accept="image/*"
                       class="hidden">

            </div>

        </div>


        {{-- ================= TWO COLUMN CONTENT ================= --}}
        <div class="profile-grid">



            {{-- ================= LEFT COLUMN ================= --}}
            <div class="profile-column">
                {{-- Section Heading --}}
                <div class="profile-section-title">

    <span class="section-bar"></span>

    <h3>Basic Information</h3>

</div>



                {{-- Full Name --}}
                
<div class="profile-field">

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', auth()->user()->name) }}"
                        class="w-full h-11 px-3 rounded-lg
                               border border-gray-200
                               bg-white
                               text-sm text-gray-800
                               focus:border-green-500
                               focus:ring-2 focus:ring-green-500/10
                               focus:outline-none
                               transition"
                        required
                    >

                </div>


                {{-- Mobile --}}
                <div class="mb-5">

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Mobile Number
                    </label>

                    <div class="verify-row">

                        <div class="verify-input">

                            <input
                                type="text"
                                id="phoneInput"
                                name="phone"
                                value="{{ auth()->user()->phone }}"
                                {{ auth()->user()->phone_verified_at ? 'readonly' : '' }}
                                placeholder="Enter mobile number"
                                class="w-full h-11 px-3 rounded-lg
                                       border border-gray-200
                                       {{ auth()->user()->phone_verified_at ? 'bg-gray-50 text-gray-500' : 'bg-white text-gray-800' }}
                                       text-sm
                                       focus:bg-white
                                       focus:border-green-500
                                       focus:ring-2 focus:ring-green-500/10
                                       focus:outline-none
                                       transition"
                            >

                        </div>


                        {{-- Verification --}}
                        <div class="w-[82px] flex justify-end flex-shrink-0">

                            @if(auth()->user()->phone_verified_at)

                                <span class="inline-flex items-center justify-center
                                             w-9 h-9 rounded-full
                                             bg-green-50 text-green-600">

                                    <svg width="18"
                                         height="19"
                                         viewBox="0 0 18 19"
                                         fill="none"
                                         xmlns="http://www.w3.org/2000/svg">

                                        <path fill-rule="evenodd"
                                              clip-rule="evenodd"
                                              d="M13.4291 0.802899C13.2504 0.501414 12.9871 0.266256 12.6758 0.13016C12.3645 -0.00593544 12.0208 -0.0361985 11.6924 0.0435849L9.36216 0.608624C9.12335 0.666566 8.87519 0.666566 8.63638 0.608624L6.3061 0.0435849C5.97776 -0.0361985 5.63403 -0.00593544 5.32276 0.13016C5.01148 0.266256 4.74814 0.501414 4.5694 0.802899L3.29928 2.94266C3.16967 3.16156 2.99471 3.34626 2.78734 3.48444L0.760332 4.82521C0.475225 5.01373 0.252786 5.29126 0.123889 5.61929C-0.00500796 5.94732 -0.0339682 6.30958 0.0410279 6.65577L0.576293 9.11841C0.630984 9.37007 0.630984 9.63154 0.576293 9.8832L0.0410279 12.3445C-0.0342597 12.6909 -0.00544518 13.0534 0.123466 13.3817C0.252376 13.71 0.474979 13.9878 0.760332 14.1764L2.78734 15.5172C2.99471 15.654 3.16967 15.8387 3.30057 16.0576L4.5707 18.1973C4.93618 18.8144 5.63345 19.1195 6.3061 18.9567L8.63638 18.3916C8.87519 18.3337 9.12335 18.3337 9.36216 18.3916L11.6937 18.9567C12.0219 19.0361 12.3653 19.0057 12.6763 18.8696C12.9874 18.7335 13.2505 18.4986 13.4291 18.1973L14.6993 16.0576C14.8289 15.8387 15.0038 15.654 15.2112 15.5172L17.2395 14.1764C17.5249 13.9875 17.7474 13.7095 17.8761 13.3809C18.0047 13.0523 18.0332 12.6896 17.9575 12.3431L17.4235 9.8832C17.3687 9.6311 17.3687 9.36914 17.4235 9.11704L17.9588 6.65577C18.0342 6.30952 18.0056 5.94707 17.8769 5.61877C17.7483 5.29047 17.5259 5.01263 17.2408 4.82384L15.2125 3.48307C15.0054 3.34601 14.8304 3.16126 14.7006 2.94266L13.4291 0.802899ZM12.7772 6.44918C12.8574 6.29358 12.8773 6.11122 12.8326 5.94047C12.788 5.76973 12.6824 5.6239 12.538 5.53369C12.3936 5.44347 12.2217 5.4159 12.0584 5.45676C11.8952 5.49763 11.7533 5.60375 11.6626 5.7528L8.27349 11.8082L6.22704 9.73954C6.16633 9.67374 6.09368 9.62151 6.01344 9.58599C5.93319 9.55046 5.84699 9.53236 5.75998 9.53276C5.67296 9.53316 5.58691 9.55205 5.50696 9.58832C5.42702 9.62458 5.35481 9.67747 5.29464 9.74383C5.23448 9.81019 5.18759 9.88867 5.15677 9.97457C5.12596 10.0605 5.11185 10.1521 5.11529 10.2438C5.11872 10.3356 5.13964 10.4257 5.17678 10.5088C5.21392 10.5919 5.26653 10.6662 5.33147 10.7273L7.96762 13.3938C8.03818 13.465 8.12306 13.5185 8.21594 13.5502C8.30881 13.582 8.40728 13.5911 8.50398 13.5771C8.60068 13.563 8.69312 13.526 8.7744 13.469C8.85568 13.4119 8.92369 13.3362 8.97335 13.2474L12.7772 6.44918Z"
                                              fill="#009A5C"/>

                                    </svg>

                                </span>

                            @else

                                <a href="javascript:void(0)"
                                   onclick="sendOtp('phone')"
                                   class="text-sm font-medium text-blue-600
                                          hover:text-blue-700
                                          hover:underline
                                          whitespace-nowrap">
                                    Verify Now
                                </a>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- Email --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>

                    <div class="flex items-center gap-3">

                        <div class="flex-1 min-w-0">

                            <input
                                type="email"
                                id="emailInput"
                                name="email"
                                value="{{ auth()->user()->email }}"
                                {{ auth()->user()->email_verified_at ? 'readonly' : '' }}
                                placeholder="Enter email address"
                                class="w-full h-11 px-3 rounded-lg
                                       border border-gray-200
                                       {{ auth()->user()->email_verified_at ? 'bg-gray-50 text-gray-500' : 'bg-white text-gray-800' }}
                                       text-sm
                                       focus:bg-white
                                       focus:border-green-500
                                       focus:ring-2 focus:ring-green-500/10
                                       focus:outline-none
                                       transition"
                            >

                        </div>


                        <div class="w-[82px] flex justify-end flex-shrink-0">

                            @if(auth()->user()->email_verified_at)

                                <span class="inline-flex items-center justify-center
                                             w-9 h-9 rounded-full
                                             bg-green-50 text-green-600">

                                    <svg width="18"
                                         height="19"
                                         viewBox="0 0 18 19"
                                         fill="none"
                                         xmlns="http://www.w3.org/2000/svg">

                                        <path fill-rule="evenodd"
                                              clip-rule="evenodd"
                                              d="M13.4291 0.802899C13.2504 0.501414 12.9871 0.266256 12.6758 0.13016C12.3645 -0.00593544 12.0208 -0.0361985 11.6924 0.0435849L9.36216 0.608624C9.12335 0.666566 8.87519 0.666566 8.63638 0.608624L6.3061 0.0435849C5.97776 -0.0361985 5.63403 -0.00593544 5.32276 0.13016C5.01148 0.266256 4.74814 0.501414 4.5694 0.802899L3.29928 2.94266C3.16967 3.16156 2.99471 3.34626 2.78734 3.48444L0.760332 4.82521C0.475225 5.01373 0.252786 5.29126 0.123889 5.61929C-0.00500796 5.94732 -0.0339682 6.30958 0.0410279 6.65577L0.576293 9.11841C0.630984 9.37007 0.630984 9.63154 0.576293 9.8832L0.0410279 12.3445C-0.0342597 12.6909 -0.00544518 13.0534 0.123466 13.3817C0.252376 13.71 0.474979 13.9878 0.760332 14.1764L2.78734 15.5172C2.99471 15.654 3.16967 15.8387 3.30057 16.0576L4.5707 18.1973C4.93618 18.8144 5.63345 19.1195 6.3061 18.9567L8.63638 18.3916C8.87519 18.3337 9.12335 18.3337 9.36216 18.3916L11.6937 18.9567C12.0219 19.0361 12.3653 19.0057 12.6763 18.8696C12.9874 18.7335 13.2505 18.4986 13.4291 18.1973L14.6993 16.0576C14.8289 15.8387 15.0038 15.654 15.2112 15.5172L17.2395 14.1764C17.5249 13.9875 17.7474 13.7095 17.8761 13.3809C18.0047 13.0523 18.0332 12.6896 17.9575 12.3431L17.4235 9.8832C17.3687 9.6311 17.3687 9.36914 17.4235 9.11704L17.9588 6.65577C18.0342 6.30952 18.0056 5.94707 17.8769 5.61877C17.7483 5.29047 17.5259 5.01263 17.2408 4.82384L15.2125 3.48307C15.0054 3.34601 14.8304 3.16126 14.7006 2.94266L13.4291 0.802899ZM12.7772 6.44918C12.8574 6.29358 12.8773 6.11122 12.8326 5.94047C12.788 5.76973 12.6824 5.6239 12.538 5.53369C12.3936 5.44347 12.2217 5.4159 12.0584 5.45676C11.8952 5.49763 11.7533 5.60375 11.6626 5.7528L8.27349 11.8082L6.22704 9.73954C6.16633 9.67374 6.09368 9.62151 6.01344 9.58599C5.93319 9.55046 5.84699 9.53236 5.75998 9.53276C5.67296 9.53316 5.58691 9.55205 5.50696 9.58832C5.42702 9.62458 5.35481 9.67747 5.29464 9.74383C5.23448 9.81019 5.18759 9.88867 5.15677 9.97457C5.12596 10.0605 5.11185 10.1521 5.11529 10.2438C5.11872 10.3356 5.13964 10.4257 5.17678 10.5088C5.21392 10.5919 5.26653 10.6662 5.33147 10.7273L7.96762 13.3938C8.03818 13.465 8.12306 13.5185 8.21594 13.5502C8.30881 13.582 8.40728 13.5911 8.50398 13.5771C8.60068 13.563 8.69312 13.526 8.7744 13.469C8.85568 13.4119 8.92369 13.3362 8.97335 13.2474L12.7772 6.44918Z"
                                              fill="#009A5C"/>

                                    </svg>

                                </span>

                            @else

                                <a href="javascript:void(0)"
                                   onclick="sendOtp('email')"
                                   class="text-sm font-medium text-blue-600
                                          hover:text-blue-700
                                          hover:underline
                                          whitespace-nowrap">
                                    Verify Now
                                </a>

                            @endif

                        </div>

                    </div>

                </div>

            </div>


            {{-- ================= RIGHT COLUMN ================= --}}
           <div class="profile-column">

                {{-- Section Heading --}}
                <div class="flex items-center gap-2 mb-5">

                    <span class="w-1.5 h-5 bg-green-500 rounded-full"></span>

                    <h3 class="text-sm font-semibold text-gray-800">
                        Account & Business
                    </h3>

                </div>


                {{-- Password --}}
                <div class="mb-5">

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>


                    @if(auth()->user()->password)

                       <div class="password-row">

                            <div class="password-input">

                                <input
                                    type="text"
                                    value="***********"
                                    disabled
                                    class="w-full h-11 px-3 rounded-lg
                                           border border-gray-200
                                           bg-gray-50
                                           text-sm text-gray-500"
                                >

                            </div>


                            <div class="w-[120px] flex justify-end flex-shrink-0">

                                <a href="javascript:void(0)"
                                   onclick="openChangePasswordModal()"
                                   class="text-sm font-medium text-blue-600
                                          hover:text-blue-700
                                          hover:underline
                                          whitespace-nowrap">
                                    Change Password
                                </a>

                            </div>

                        </div>

                    @else

                        <input
                            type="password"
                            name="password"
                            placeholder="Enter Password"
                            class="w-full h-11 px-3 rounded-lg
                                   border border-gray-200
                                   bg-white
                                   text-sm text-gray-800
                                   placeholder-gray-400
                                   focus:border-green-500
                                   focus:ring-2 focus:ring-green-500/10
                                   focus:outline-none
                                   transition"
                        >

                    @endif

                </div>


                {{-- Company Name --}}
                <div class="mb-5">

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Company Name
                    </label>

                    <input
                        type="text"
                        name="company_name"
                        value="{{ old('company_name', auth()->user()->company_name) }}"
                        placeholder="Enter Company Name"
                        class="w-full h-11 px-3 rounded-lg
                               border border-gray-200
                               bg-white
                               text-sm text-gray-800
                               placeholder-gray-400
                               focus:border-green-500
                               focus:ring-2 focus:ring-green-500/10
                               focus:outline-none
                               transition"
                    >

                </div>


                {{-- GSTIN --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        GSTIN Number
                    </label>

                    <input
                        type="text"
                        name="gstin"
                        value="{{ old('gstin', auth()->user()->gstin) }}"
                        placeholder="Enter GSTIN"
                        class="w-full h-11 px-3 rounded-lg
                               border border-gray-200
                               bg-white
                               text-sm text-gray-800
                               placeholder-gray-400
                               focus:border-green-500
                               focus:ring-2 focus:ring-green-500/10
                               focus:outline-none
                               transition"
                        style="text-transform: uppercase;"
                    >

                </div>

            </div>

        </div>


        {{-- ================= FOOTER ================= --}}
        <div class="profile-footer">

            <p class="text-xs text-gray-400">
                Keep your information up to date for a better experience.
            </p>


            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2
                       bg-green-500 hover:bg-green-600
                       active:bg-green-700
                       text-white px-7 py-2.5
                       rounded-lg text-sm font-medium
                       shadow-sm hover:shadow
                       transition-all duration-200
                       cursor-pointer">

                <svg class="w-4 h-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M5 13l4 4L19 7"/>

                </svg>

                Update

            </button>

        </div>

    </form>

</div>

</div>
{{-- Change Password Modal --}}
<div id="changePasswordModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 px-4 md:px-0">
    <div class="bg-white rounded-lg w-100 max-w-lg p-6 relative border border-gray-300 modal-container">

        <button onclick="closeChangePasswordModal()"
                class="absolute cursor-pointer top-2 right-3 text-gray-800 text-xl hover:text-gray-500">×</button>

        <h3 class="text-lg font-semibold mb-4">Change Password</h3>

        <div class="space-y-4">
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Current Password
                </label>
                <input
                    type="password"
                    id="currentPassword"
                    class="w-full border border-gray-300 rounded py-2 px-3 pr-10 focus:border-green-500 focus:outline-none"
                    placeholder="Enter current password"
                >
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('currentPassword'); return false;">
                    <i id="currentPasswordIcon" class="bi bi-eye mt-6"></i>
                </button>
            </div>

            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    New Password
                </label>
                <input
                    type="password"
                    id="newPassword"
                    class="w-full border border-gray-300 rounded py-2 px-3 pr-10 focus:border-green-500 focus:outline-none"
                    placeholder="Enter new password"
                >
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('newPassword'); return false;">
                    <i id="newPasswordIcon" class="bi bi-eye  mt-6"></i>
                </button>
            </div>

            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm Password
                </label>
                <input
                    type="password"
                    id="confirmPassword"
                    class="w-full border border-gray-300 rounded py-2 px-3 pr-10 focus:border-green-500 focus:outline-none"
                    placeholder="Confirm new password"
                >
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirmPassword'); return false;">
                    <i id="confirmPasswordIcon" class="bi bi-eye  mt-6"></i>
                </button>
            </div>

            <div id="passwordError" class="text-red-500 text-sm hidden"></div>
        </div>

        <div class="flex gap-3 mt-6">
            <button
                onclick="closeChangePasswordModal()"
                class="flex-1 border border-gray-300 text-gray-700 py-2 rounded hover:bg-gray-50 cursor-pointer">
                Cancel
            </button>
            <button
                onclick="submitPasswordChange()"
                class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 rounded cursor-pointer">
                Update Password
            </button>
        </div>
    </div>
</div>
{{-- Otp Verify Modal --}}
<div id="otpModal" class="fixed inset-0 bg-black/50 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg w-96 p-6 relative border border-gray-300 modal-container">

        <button onclick="closeOtpModal()"
                class="absolute top-2 right-3 text-gray-500 text-xl cursor-pointer">×</button>

        <h3 class="text-lg font-semibold mb-2">Verify</h3>

        <p class="text-sm text-gray-500 mb-4">
            Enter the 4-digit code sent to
            <span id="otpTarget" class="font-medium"></span>
        </p>

        <input
            type="text"
            id="otpInput"
            maxlength="4"
            class="w-full text-center tracking-widest text-lg
                   border border-gray-300 rounded py-2 mb-4"
            placeholder="____"
        >

        <button
            onclick="verifyOtp()"
            class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded cursor-pointer">
            Verify
        </button>

        <p class="text-center text-xs text-gray-400 mt-3">
            Didn’t receive code?
            <a href="#" onclick="resendOtp()" class="text-green-600">Resend</a>
        </p>
    </div>
</div>
@endsection
<script>
function showToast(type, message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}
</script>
<script>
    /* ===============================
    GLOBAL STATE
    ================================ */
    let currentIdentifier = '';
    let otpTimer = null;

    /* ===============================
    INPUT VALIDATIONS
    ================================ */

    // only numbers in phone & otp
    document.addEventListener('input', function (e) {
        if (e.target.id === 'phoneInput') {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
        }

        if (e.target.id === 'otpInput') {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
        }
    });

    /* ===============================
    SEND OTP (FIXED)
    ================================ */
    function sendOtp(type) {

        let identifier = '';

        if (type === 'phone') {
            identifier = document.getElementById('phoneInput').value.trim();

            if (!identifier) {
                showToast('warning', 'Enter mobile number');
                return;
            }

            if (identifier.length !== 10 || isNaN(identifier)) {
                showToast('error', 'Enter valid 10 digit mobile number');
                return;
            }
        }

        if (type === 'email') {
            identifier = document.getElementById('emailInput').value.trim();

            if (!identifier) {
                showToast('warning', 'Enter email address');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(identifier)) {
                showToast('error', 'Invalid email address');
                return;
            }
        }

        fetch("{{ route('customer.profile.send-otp') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ identifier })
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                showToast('error', res.message);
                return;
            }

            showToast('success', 'OTP sent successfully');
            openOtpModal(identifier);
        })
        .catch(() => {
            showToast('error', 'Something went wrong. Please try again.');
        });
    }

    /* ===============================
    OTP MODAL
    ================================ */
    function openOtpModal(identifier) {
        currentIdentifier = identifier;

        document.getElementById('otpTarget').innerText = identifier;
        document.getElementById('otpInput').value = '';

        const modal = document.getElementById('otpModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Add blur effect to backdrop
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);

        document.getElementById('otpInput').focus();
    }

    function closeOtpModal() {
        const modal = document.getElementById('otpModal');
        modal.classList.remove('active');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    /* ===============================
    VERIFY OTP
    ================================ */
    function verifyOtp() {

        const otp = document.getElementById('otpInput').value.trim();

        if (otp.length !== 4) {
            showToast('warning', 'Enter 4 digit OTP');
            return;
        }

        fetch("{{ route('customer.profile.verify-otp') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                identifier: currentIdentifier,
                otp: otp
            })
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                showToast('error', res.message);
                return;
            }

            showToast('success', 'OTP verified successfully');
            closeOtpModal();

            setTimeout(() => location.reload(), 800);
        })
        .catch(() => {
            showToast('error', 'Something went wrong');
        });
    }

    /* ===============================
    RESEND OTP
    ================================ */
    function resendOtp() {
        sendOtp(
            currentIdentifier.includes('@') ? 'email' : 'phone'
        );
    }
    function removeAvatar() {

        Swal.fire({
            title: 'Remove profile picture?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn-color'
            }
        }).then((result) => {

            if (!result.isConfirmed) return;

            fetch("{{ route('customer.profile.avatar.remove') }}", {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(res => {
                if (!res.success) {
                    showToast('error', res.message || 'Something went wrong');
                    return;
                }

                showToast('success', 'Profile picture removed');
                setTimeout(() => location.reload(), 800);
            })
            .catch(() => {
                showToast('error', 'Server error');
            });
        });
    }

    /* ===============================
    CHANGE PASSWORD MODAL
    ================================ */
    function openChangePasswordModal() {
        // Clear form fields
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
        document.getElementById('passwordError').classList.add('hidden');
        
        const modal = document.getElementById('changePasswordModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Add blur effect to backdrop
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
        
        document.getElementById('currentPassword').focus();
    }

    function closeChangePasswordModal() {
        const modal = document.getElementById('changePasswordModal');
        modal.classList.remove('active');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
        
        // Clear form
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
    }

    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const iconId = fieldId + 'Icon';
        const icon = document.getElementById(iconId);
        
        if (field.type === 'password') {
            field.type = 'text';
            if (icon) {
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        } else {
            field.type = 'password';
            if (icon) {
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    }

    function submitPasswordChange() {

        const currentPassword = document.getElementById('currentPassword').value.trim();
        const newPassword = document.getElementById('newPassword').value.trim();
        const confirmPassword = document.getElementById('confirmPassword').value.trim();
        const errorDiv = document.getElementById('passwordError');

        errorDiv.classList.add('hidden');

        if (!currentPassword) {
            errorDiv.innerText = 'Current password is required';
            errorDiv.classList.remove('hidden');
            return;
        }

        if (!newPassword) {
            errorDiv.innerText = 'New password is required';
            errorDiv.classList.remove('hidden');
            return;
        }

        if (newPassword.length < 6) {
            errorDiv.innerText = 'Password must be at least 6 characters';
            errorDiv.classList.remove('hidden');
            return;
        }

        if (newPassword !== confirmPassword) {
            errorDiv.innerText = 'Passwords do not match';
            errorDiv.classList.remove('hidden');
            return;
        }

        if (currentPassword === newPassword) {
            errorDiv.innerText = 'New password must be different from current password';
            errorDiv.classList.remove('hidden');
            return;
        }

        fetch("{{ route('customer.profile.change-password') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: confirmPassword
            })
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                errorDiv.innerText = res.message || 'Failed to update password';
                errorDiv.classList.remove('hidden');
                return;
            }

            showToast('success', 'Password updated successfully');
            closeChangePasswordModal();
        })
        .catch(() => {
            showToast('error', 'Server error. Please try again.');
        });
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('profileImage');
    const img = document.getElementById('avatarPreviewImg');
    const fallback = document.getElementById('avatarFallback');
    const fileName = document.getElementById('avatarFileName');

    if (!input || !img || !fallback) return;

    /* ------------------------------
       PAGE LOAD STATE (existing avatar)
    -------------------------------*/
    function setInitialState() {
        const src = img.getAttribute('src');

        if (src && src.trim() !== '') {
            // existing uploaded avatar
            img.classList.remove('hidden');
            fallback.classList.add('hidden');
        } else {
            // no avatar → svg
            img.classList.add('hidden');
            fallback.classList.remove('hidden');
        }
    }

    setInitialState();

    /* ------------------------------
       FILE SELECT
    -------------------------------*/
    input.addEventListener('change', function () {

        const file = this.files[0];

        // if user cancelled file picker
        if (!file) {
            setInitialState();
            fileName.textContent = '';
            return;
        }

        /* ------------------------------
           VALIDATION
        -------------------------------*/
        if (!file.type.startsWith('image/')) {
            showToast('error', 'Please select a valid image (JPG, PNG)');
            input.value = '';
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            showToast('error', 'Image size must be less than 2MB');
            input.value = '';
            return;
        }

        fileName.textContent = file.name;

        /* ------------------------------
           IMPORTANT FIX (THE REAL BUG)
        -------------------------------*/

        // browser caching + repaint fix
        img.src = '';
        img.classList.add('hidden');

        const reader = new FileReader();

        reader.onload = function (e) {

            // force new render
            img.onload = function () {
                img.classList.remove('hidden');
                fallback.classList.add('hidden');
            };

            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });

});
</script>