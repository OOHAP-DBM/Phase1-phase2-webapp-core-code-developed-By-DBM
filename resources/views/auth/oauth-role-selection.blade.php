{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Select Account Type</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body>

<div class="container">

    <div class="row justify-content-center mt-5">

        <div class="col-md-6">

            <div class="card shadow-sm">

                <div class="card-body p-4">

                    <h3 class="text-center mb-3">
                        Create Your Account
                    </h3>

                    <p class="text-center text-muted mb-4">
                        Please select how you want to use OOHAPP.
                    </p>


                    @if(session('error'))

                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>

                    @endif


                    @if($errors->any())

                        <div class="alert alert-danger">

                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach

                        </div>

                    @endif


                    <form
                        method="POST"
                        action="{{ route('oauth.select-role.submit') }}"
                    >

                        @csrf


                        <!-- Customer -->

                        <div class="form-check border rounded p-3 mb-3">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="role"
                                id="customer"
                                value="customer"
                            >

                            <label
                                class="form-check-label ml-2"
                                for="customer"
                            >

                                <strong>Customer</strong>

                                <div class="text-muted small">
                                    Find hoardings, send enquiries
                                    and manage your bookings.
                                </div>

                            </label>

                        </div>


                        <!-- Vendor -->

                        <div class="form-check border rounded p-3 mb-4">

                            <input
                                class="form-check-input"
                                type="radio"
                                name="role"
                                id="vendor"
                                value="vendor"
                            >

                            <label
                                class="form-check-label ml-2"
                                for="vendor"
                            >

                                <strong>Vendor</strong>

                                <div class="text-muted small">
                                    Manage hoardings, offers,
                                    enquiries and bookings.
                                </div>

                            </label>

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary btn-block"
                        >
                            Continue
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html> --}}

@extends('layouts.guest')

@section('title', 'Select Account Type - OOHAPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    html,
    body {
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .auth-wrapper {
        width: 100vw;
        height: 100vh;
    }

    /* LEFT IMAGE */
    .auth-left {
        background: #000;
        padding: 0;
        height: 100vh;
    }

    .auth-left img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* RIGHT SIDE */
    .auth-right {
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        padding: 30px;
    }

    /* MAIN BOX */
    .role-box {
        width: 100%;
        max-width: 430px;
    }

    .role-box h3 {
        font-weight: 600;
        font-size: 28px;
        color: #111827;
        margin-bottom: 8px;
    }

    .role-description {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 30px;
    }

    /* LOGO */
    .logo-box {
        text-align: center;
        margin-bottom: 25px;
    }

    .logo-box img {
        max-width: 150px;
        max-height: 48px;
        object-fit: contain;
    }

    /* ROLE CARD */
    .role-option {
        position: relative;
        margin-bottom: 15px;
    }

    .role-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .role-card {
        display: flex;
        align-items: center;
        gap: 15px;
        width: 100%;
        padding: 18px 20px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
        margin: 0;
    }

    .role-card:hover {
        border-color: #2bb57c;
        background: #f9fffc;
    }

    .role-option input[type="radio"]:checked + .role-card {
        border: 2px solid #2bb57c;
        background: #f0fdf8;
    }

    /* ICON */
    .role-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 10px;
        background: #ecfdf5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2bb57c;
        font-size: 20px;
    }

    .role-content {
        flex: 1;
    }

    .role-title {
        font-size: 16px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 4px;
    }

    .role-text {
        font-size: 13px;
        line-height: 1.5;
        color: #6b7280;
        margin: 0;
    }

    /* RADIO INDICATOR */
    .role-radio {
        width: 20px;
        height: 20px;
        min-width: 20px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .role-option input[type="radio"]:checked + .role-card .role-radio {
        border-color: #2bb57c;
    }

    .role-option input[type="radio"]:checked + .role-card .role-radio::after {
        content: '';
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #2bb57c;
    }

    /* CONTINUE BUTTON */
    .btn-continue {
        width: 100%;
        height: 46px;
        border: none;
        border-radius: 8px;
        background: #e5e7eb;
        color: #9ca3af;
        font-weight: 500;
        cursor: not-allowed;
        transition: all 0.2s ease;
        margin-top: 10px;
    }

    .btn-continue.active {
        background: #2bb57c;
        color: #fff;
        cursor: pointer;
    }

    .btn-continue.active:hover {
        background: #239b6b;
    }

    /* FOOTER */
    .footer-text {
        margin-top: 30px;
        text-align: center;
        font-size: 13px;
        color: #6b7280;
    }

    .footer-text a {
        color: #2bb57c;
        text-decoration: none;
        font-weight: 600;
    }

    /* ALERT */
    .alert {
        font-size: 13px;
        border-radius: 8px;
    }

    /* MOBILE */
    @media (max-width: 767.98px) {
        html,
        body {
            overflow: auto;
        }

        .auth-wrapper {
            height: 100vh;
            min-height: 100vh;
        }

        .auth-right {
            height: 100vh;
            min-height: 100vh;
            padding: 25px 20px;
        }

        .role-box {
            max-width: 400px;
        }

        .logo-box {
            display: block;
        }

        .role-box h3 {
            font-size: 25px;
        }
    }

    @media (min-width: 768px) {
        .logo-box {
            display: none;
        }
    }
</style>
@endpush


@section('content')

<div class="container-fluid auth-wrapper">
    <div class="row h-100">

        {{-- ================= LEFT IMAGE ================= --}}
        <div class="col-md-5 d-none d-md-block auth-left">

            <a href="{{ route('home') }}">

                <x-optimized-image
                    src="assets/images/login/login_image.jpeg"

                    :webp-srcset="
                        asset('assets/images/login/login_image-390.webp') . ' 390w, ' .
                        asset('assets/images/login/login_image-780.webp') . ' 780w, ' .
                        asset('assets/images/login/login_image.webp') . ' 1250w'
                    "

                    :srcset="
                        asset('assets/images/login/login_image-390.jpeg') . ' 390w, ' .
                        asset('assets/images/login/login_image-780.jpeg') . ' 780w, ' .
                        asset('assets/images/login/login_image.jpeg') . ' 1250w'
                    "

                    sizes="(min-width: 768px) 42vw, 100vw"

                    alt="OOHAPP"

                    width="1250"
                    height="1600"

                    loading="eager"
                    fetchpriority="high"
                />

            </a>

        </div>


        {{-- ================= RIGHT SIDE ================= --}}
        <div class="col-md-7 col-12 auth-right">

            <div class="role-box">

                {{-- MOBILE LOGO --}}
                <div class="logo-box">

                    <a href="{{ route('home') }}">

                        <x-optimized-image
                            :src="route('brand.oohapp-logo')"
                            alt="OOHAPP company logo"
                            width="150"
                            height="48"
                            style="display:block; margin:0 auto; max-height:48px; object-fit:contain;"
                        />

                    </a>

                </div>


                {{-- HEADING --}}
                <div class="text-center">

                    <h3>Create Your Account</h3>

                    <p class="role-description">
                        Please select how you want to use OOHAPP.
                    </p>

                </div>


                {{-- ================= SESSION ERROR ================= --}}
                @if(session('error'))

                    <div class="alert alert-danger text-start">
                        <i class="fa-solid fa-circle-exclamation me-2"></i>
                        {{ session('error') }}
                    </div>

                @endif


                {{-- ================= VALIDATION ERRORS ================= --}}
                @if($errors->any())

                    <div class="alert alert-danger text-start">

                        <ul class="mb-0 ps-3">

                            @foreach($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                {{-- ================= ROLE FORM ================= --}}
                <form
                    method="POST"
                    action="{{ route('oauth.select-role.submit') }}"
                    id="roleForm"
                >

                    @csrf


                    {{-- CUSTOMER --}}
                    <div class="role-option">

                        <input
                            type="radio"
                            name="role"
                            id="customer"
                            value="customer"
                        >

                        <label
                            for="customer"
                            class="role-card"
                        >

                            <div class="role-icon">

                                <i class="fa-solid fa-user"></i>

                            </div>

                            <div class="role-content">

                                <div class="role-title">
                                    Customer
                                </div>

                                <p class="role-text">
                                    Find hoardings, send enquiries
                                    and manage your bookings.
                                </p>

                            </div>

                            <div class="role-radio"></div>

                        </label>

                    </div>


                    {{-- VENDOR --}}
                    <div class="role-option">

                        <input
                            type="radio"
                            name="role"
                            id="vendor"
                            value="vendor"
                        >

                        <label
                            for="vendor"
                            class="role-card"
                        >

                            <div class="role-icon">

                                <i class="fa-solid fa-store"></i>

                            </div>

                            <div class="role-content">

                                <div class="role-title">
                                    Vendor
                                </div>

                                <p class="role-text">
                                    Manage hoardings, offers,
                                    enquiries and bookings.
                                </p>

                            </div>

                            <div class="role-radio"></div>

                        </label>

                    </div>


                    {{-- CONTINUE --}}
                    <button
                        type="submit"
                        class="btn-continue"
                        id="continueBtn"
                        disabled
                    >
                        Continue
                    </button>

                </form>


                {{-- FOOTER --}}
                {{-- <div class="footer-text">

                    Already Have an Account?

                    <a href="{{ route('login') }}">
                        Login
                    </a>

                    <br>

                    <small class="d-block mt-3">

                        By continuing, you agree to the

                        <a
                            href="{{ route('terms') }}"
                            class="text-dark"
                        >
                            Terms & Conditions
                        </a>

                        and

                        <a
                            href="{{ route('privacy') }}"
                            class="text-dark"
                        >
                            Privacy Policy
                        </a>

                        of OOHAPP.

                    </small>

                </div> --}}

            </div>

        </div>

    </div>
</div>

@endsection


@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const roleInputs = document.querySelectorAll(
            'input[name="role"]'
        );

        const continueBtn = document.getElementById(
            'continueBtn'
        );


        roleInputs.forEach(function (input) {

            input.addEventListener('change', function () {

                if (document.querySelector(
                    'input[name="role"]:checked'
                )) {

                    continueBtn.disabled = false;

                    continueBtn.classList.add('active');

                } else {

                    continueBtn.disabled = true;

                    continueBtn.classList.remove('active');

                }

            });

        });

    });
</script>

@endpush


