<!DOCTYPE html>
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

</html>
