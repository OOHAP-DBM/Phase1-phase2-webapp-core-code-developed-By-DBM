<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'OOHAPP' }}</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f8;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
        style="width:100%; background:#f4f6f8; margin:0; padding:0;">
        <tr>
            <td align="center" style="padding:20px 0;">

                <table width="600" cellpadding="0" cellspacing="0" border="0" style="
                        width:600px;
                        max-width:600px;
                        background:#ffffff;
                        border-radius:8px;
                        overflow:hidden;
                        box-shadow:0 2px 8px rgba(0,0,0,0.08);
                    ">

                    {{-- GLOBAL HEADER --}}
                    @include('emails.partials.header')

                    {{-- TEMPLATE BODY --}}
                    <tr>
                        <td style="
                                padding:30px 40px;
                                font-family:Arial,Helvetica,sans-serif;
                                font-size:14px;
                                line-height:1.6;
                                color:#222;
                            ">
                            {!! $body !!}
                        </td>
                    </tr>

                    {{-- GLOBAL FOOTER --}}
                    @include('emails.partials.footer')

                </table>

            </td>
        </tr>
    </table>

</body>

</html>