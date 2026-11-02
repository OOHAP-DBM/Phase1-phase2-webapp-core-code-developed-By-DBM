<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $emailSubject }}</title>
</head>

<body style="
    margin:0;
    padding:0;
    background-color:#f4f6f8;
    font-family:Arial, Helvetica, sans-serif;
">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="
            background-color:#f4f6f8;
            padding:40px 0;
        ">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0" border="0" style="
                        width:600px;
                        max-width:600px;
                        background:#ffffff;
                        border-radius:8px;
                        overflow:hidden;
                        box-shadow:0 2px 8px rgba(0,0,0,0.08);
                    ">

                    @include('emails.partials.header')

                    <tr>
                        <td style="
                            padding:30px 40px;
                            font-size:14px;
                            color:#444444;
                            line-height:22px;
                        ">
                            {!! $body !!}
                        </td>
                    </tr>

                    @include('emails.partials.footer')

                </table>

            </td>
        </tr>
    </table>
</body>

</html>