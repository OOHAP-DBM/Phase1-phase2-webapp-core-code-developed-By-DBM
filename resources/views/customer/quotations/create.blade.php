@extends('layouts.vendor')

@section('title', 'Create Quotation')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Create Quotation</h4>

            <a href="{{ route('vendor.quotations.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>

        <form action="{{ route('vendor.quotations.store') }}" method="POST">
            @csrf

            <input type="hidden" name="offer_id" value="{{ $offer->id }}">

            <div class="card mb-4">
                <div class="card-header">
                    Customer Information
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Customer Name</label>
                            <input type="text" class="form-control" value="{{ $offer->customer?->name }}" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="text" class="form-control" value="{{ $offer->customer?->email }}" readonly>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Selected Hoardings
                </div>

                <div class="card-body">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Hoarding</th>
                                <th>Location</th>
                                <th>Price</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($offer->currentVersion?->items ?? [] as $item)
                                <tr>
                                    <td>
                                        {{ $item->hoarding?->title ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->hoarding?->city ?? '-' }}
                                    </td>

                                    <td>
                                        ₹{{ number_format($item->final_price ?? 0, 2) }}
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>

                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    Pricing Details
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-4">
                            <label>Subtotal</label>
                            <input type="number" step="0.01" name="subtotal" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>Discount</label>
                            <input type="number" step="0.01" name="discount" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>GST (%)</label>
                            <input type="number" step="0.01" name="gst_percentage" class="form-control" value="18">
                        </div>

                    </div>

                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    Create Quotation
                </button>
            </div>

        </form>

    </div>
@endsection