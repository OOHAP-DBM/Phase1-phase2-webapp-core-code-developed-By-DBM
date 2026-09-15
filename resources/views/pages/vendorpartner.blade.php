@extends('layouts.app')

@section('content')

@include('components.customer.navbar', ['hideSearch' => true])

<style>
    .pricing-payment-section {
        color: #1f2937;
        font-size: 14px;
        line-height: 1.6;
    }

    .pricing-payment-section h2 {
        font-size: 18px;
        font-weight: 700;
        margin-top: 24px;
        margin-bottom: 10px;
        color: #111827;
    }

    .pricing-payment-section p {
        margin-bottom: 10px;
    }

    /* FIX BULLET LIST */
    .pricing-payment-section ul {
        list-style-type: disc !important;
        padding-left: 30px !important;
        margin-top: 8px;
        margin-bottom: 16px;
    }

    .pricing-payment-section ol {
        list-style-type: decimal !important;
        padding-left: 30px !important;
    }

    .pricing-payment-section li {
        display: list-item !important;
        margin-bottom: 5px;
    }

    .pricing-payment-section hr {
        border: 0;
        border-top: 1px solid #e5e7eb;
        margin: 25px 0;
    }

    .pricing-payment-section strong {
        font-weight: 700;
    }
</style>

<div class="bg-gray-100 py-12 px-4">
    <div class="max-w-6xl mx-auto bg-white p-10 rounded-lg shadow">

        @if($data && $data->content)

            <div class="pricing-payment-section">
                {!! $data->content !!}
            </div>

        @else

            <div class="text-center py-8">
                <p class="text-gray-500">
                    Vendor Partner information content is not available.
                </p>
            </div>

        @endif

    </div>
</div>

@endsection
