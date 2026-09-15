<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Inventory Imported Successfully</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">

                    @include('emails.partials.header')

                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding:10px 40px 0 40px;">
                            <h2 style="margin:0;color:#222;font-weight:600;">
                                Inventory Imported Successfully 🎉
                            </h2>

                            <p style="margin-top:8px;color:#666;font-size:14px;">
                                Hello {{ $batch->vendor->name }},
                            </p>
                        </td>
                    </tr>

                    <!-- Intro -->
                    <tr>
                        <td style="padding:20px 40px 10px 40px;font-size:14px;color:#444;line-height:22px;">

                            <p>
                                Great news!
                            </p>

                            <p>
                                An administrator has successfully imported inventory into your
                                <strong>OOHAPP Vendor Account</strong>.
                            </p>

                            <p>
                                Your imported inventory is now available for review inside your dashboard.
                            </p>

                        </td>
                    </tr>

                    <!-- Details -->
                    <tr>
                        <td style="padding:0 40px;">

                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="border:1px solid #e5e7eb;">

                                <tr style="background:#f3f4f6;">
                                    <th align="left" style="padding:10px;font-size:13px;">Field</th>
                                    <th align="left" style="padding:10px;font-size:13px;">Details</th>
                                </tr>

                                <tr>
                                    <td style="padding:10px;"><strong>Vendor</strong></td>
                                    <td style="padding:10px;">{{ $batch->vendor->name }}</td>
                                </tr>

                                <tr>
                                    <td style="padding:10px;"><strong>Imported By</strong></td>
                                    <td style="padding:10px;">{{ $adminName }}</td>
                                </tr>

                                <tr>
                                    <td style="padding:10px;"><strong>Media Type</strong></td>
                                    <td style="padding:10px;">
                                        {{ strtoupper($batch->media_type) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px;"><strong>Total Imported Inventory</strong></td>
                                    <td style="padding:10px;">
                                        {{ $batch->valid_rows }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px;"><strong>Import Date</strong></td>
                                    <td style="padding:10px;">
                                        {{ now()->format('d M Y h:i A') }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:10px;"><strong>Login URL</strong></td>
                                    <td style="padding:10px;">
                                        <a href="https://staging.oohapp.io/" target="_blank">
                                            https://staging.oohapp.io/
                                        </a>
                                    </td>
                                </tr>

                            </table>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 40px;">

                            <h3 style="margin-bottom:15px;">
                                Imported Inventory Preview
                            </h3>

                            <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">

                                <tr style="background:#f3f4f6;">
                                    <th style="border:1px solid #e5e7eb;">Image</th>
                                    <th style="border:1px solid #e5e7eb;">Inventory Code</th>
                                    <th style="border:1px solid #e5e7eb;">City</th>
                                    <th style="border:1px solid #e5e7eb;">Type</th>
                                </tr>

                                @foreach($inventories as $inventory)

                                    @php
                                        $extra = is_array($inventory->extra_attributes)
                                            ? $inventory->extra_attributes
                                            : json_decode($inventory->extra_attributes, true);

                                        $imageUrl = $extra['image_public_url'] ?? null;
                                    @endphp

                                    <tr>

                                        <td style="border:1px solid #e5e7eb; text-align:center;">

                                            @if($imageUrl)
                                                <img src="{{ $imageUrl }}" width="80" height="50"
                                                    style="border-radius:4px; object-fit:cover;">
                                            @else
                                                No Image
                                            @endif

                                        </td>

                                        <td style="border:1px solid #e5e7eb;">
                                            {{ $inventory->code }}
                                        </td>

                                        <td style="border:1px solid #e5e7eb;">
                                            {{ $inventory->city ?? 'N/A' }}
                                        </td>

                                        <td style="border:1px solid #e5e7eb;">
                                            {{ strtoupper($inventory->media_type) }}
                                        </td>

                                    </tr>

                                @endforeach

                            </table>

                            @if($batch->valid_rows > 10)

                                <p style="margin-top:15px; text-align:center;">
                                    Showing first 10 inventories out of
                                    <strong>{{ $batch->valid_rows }}</strong>
                                </p>

                                <div style="text-align:center; margin-top:15px;">

                                    <a href="https://staging.oohapp.io/vendor/inventories" style="
                                                background:#16a34a;
                                                color:#ffffff;
                                                padding:12px 24px;
                                                text-decoration:none;
                                                border-radius:6px;
                                                display:inline-block;
                                                font-size:14px;
                                                font-weight:600;
                                           ">
                                        View All Inventories
                                    </a>

                                </div>

                            @endif

                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:25px 40px;">

                            <a href="https://staging.oohapp.io/vendor/dashboard" style="background:#2563eb;
                                       color:#fff;
                                       padding:12px 24px;
                                       font-size:14px;
                                       text-decoration:none;
                                       border-radius:6px;
                                       display:inline-block;">

                                Login To Dashboard

                            </a>



                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td style="padding:0 40px 10px 40px;font-size:14px;color:#444;line-height:22px;">

                            <p><strong>What should you do next?</strong></p>

                            <ul style="padding-left:18px;margin:8px 0;">
                                <li>Login to your OOHAPP dashboard.</li>
                                <li>Review all imported inventory.</li>
                                <li>Verify pricing, media details and images.</li>
                                <li>Start accepting advertiser enquiries.</li>
                            </ul>

                            <p>
                                If you notice any issue with the imported inventory,
                                please contact the administrator or the OOHAPP Support Team.
                            </p>

                            <p>
                                Need help?
                                <a href="mailto:support@oohapp.com">
                                    support@oohapp.com
                                </a>
                            </p>

                            <p>
                                Regards,<br>
                                <strong>Team OOHAPP</strong>
                            </p>

                        </td>
                    </tr>

                    <!-- Disclaimer -->
                    <tr>
                        <td style="padding:10px 40px 7px 40px;">

                            <div style="
                                margin-top:25px;
                                padding-top:12px;
                                border-top:1px dashed #ddd;
                                font-size:9px;
                                color:#777;
                                line-height:1.5;">

                                <strong style="color:#555;">Disclaimer:</strong>

                                This notification confirms that inventory has been imported
                                into your OOHAPP account by an authorized administrator.
                                Please review all imported inventory before making it
                                available for advertisers.

                            </div>

                        </td>
                    </tr>

                    @include('emails.partials.footer')

                </table>

            </td>
        </tr>
    </table>

</body>

</html>