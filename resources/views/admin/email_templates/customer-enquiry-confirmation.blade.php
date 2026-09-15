<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Email' }}</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f8;">

    <div style="width:100%; padding:20px 0;">

        <div style="
                max-width:600px;
                margin:0 auto;
                background:#ffffff;
                overflow:hidden;
                border-radius:8px;
                box-shadow:0 2px 8px rgba(0,0,0,0.08);
            ">

            {{-- Global Header --}}
            <div style="width:100%; overflow:hidden;">
                @include('emails.partials.header')
            </div>

            {{-- Template Body --}}
            <div style="width:100%; overflow:hidden;">
                {!! $body !!}
            </div>

            {{-- Global Footer --}}
            <div style="width:100%; overflow:hidden;">
                @include('emails.partials.footer')
            </div>

        </div>

    </div>

</body>

</html>