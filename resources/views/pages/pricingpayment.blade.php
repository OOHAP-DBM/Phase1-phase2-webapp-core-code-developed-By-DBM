@extends('layouts.app')

@section('content')

@include('components.customer.navbar', ['hideSearch' => true])

<style>
    .policy-content {
        color: #1f2937;
        font-size: 14px;
        line-height: 1.6;
    }

    .policy-content h1,
    .policy-content h2,
    .policy-content h3,
    .policy-content h4 {
        color: #111827;
        font-weight: 700;
    }

    .policy-content h1 {
        font-size: 24px;
        margin-bottom: 20px;
    }

    .policy-content h2 {
        font-size: 18px;
        margin-top: 24px;
        margin-bottom: 10px;
    }

    .policy-content h3 {
        font-size: 16px;
        margin-top: 20px;
        margin-bottom: 8px;
    }

    .policy-content p {
        margin-bottom: 10px;
    }

    /* UL BULLETS */
    .policy-content ul {
        list-style-type: disc !important;
        padding-left: 30px !important;
        margin-top: 8px;
        margin-bottom: 16px;
    }

    /* OL NUMBERS */
    .policy-content ol {
        list-style-type: decimal !important;
        padding-left: 30px !important;
        margin-top: 8px;
        margin-bottom: 16px;
    }

    .policy-content li {
        display: list-item !important;
        margin-bottom: 5px;
    }

    .policy-content ul ul {
        list-style-type: circle !important;
        margin-top: 5px;
        margin-bottom: 5px;
    }

    .policy-content hr {
        border: 0;
        border-top: 1px solid #e5e7eb;
        margin: 25px 0;
    }

    .policy-content strong {
        font-weight: 700;
    }

    .policy-content a {
        text-decoration: underline;
    }
</style>

<div class="bg-gray-100 py-12 px-4">

    <div class="max-w-6xl mx-auto bg-white p-8 md:p-10 rounded-lg shadow">

        @if($data && $data->content)

            <div class="policy-content">
                {!! $data->content !!}
            </div>

        @else

            <div class="text-center py-8">
                <p class="text-gray-500">
                    Pricing Payment information content is not available.
                </p>
            </div>

        @endif

    </div>

</div>

@endsection
