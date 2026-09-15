<?php

namespace Modules\Mail;

use App\Models\User;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Modules\Enquiries\Models\Enquiry;

class VendorEnquiryNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Enquiry $enquiry;
    public User $vendor;
    public Collection $items;

    public function __construct(
        Enquiry $enquiry,
        User $vendor,
        Collection $items
    ) {
        $this->enquiry = $enquiry;
        $this->vendor = $vendor;
        $this->items = $items;
    }

    public function build()
    {
        $firstItem = $this->items->first();

        $email = app(EmailTemplateService::class)->render(
            'vendor_enquiry_notification',
            [
                'vendor_name' => $this->vendor->name,

                'enquiry_number' => $this->enquiry->formatted_id
                    ?? $this->enquiry->id,

                'customer_name' => $this->enquiry->customer->name
                    ?? $this->enquiry->name
                    ?? 'Not provided',

                'customer_email' => $this->enquiry->customer->email
                    ?? $this->enquiry->email
                    ?? 'Not provided',

                'customer_phone' => $this->enquiry->contact_number
                    ?? $this->enquiry->formatted_phone
                    ?? 'Not provided',

                'preferred_start_date' => $firstItem?->preferred_start_date
                    ? \Carbon\Carbon::parse(
                        $firstItem->preferred_start_date
                    )->format('d M Y')
                    : 'Not specified',

                'hoarding_count' => $this->items->count(),

                'hoarding_details' => new HtmlString(
                    $this->buildHoardingDetails()
                ),

                'customer_message' => $this->enquiry->customer_note
                    ?? 'No message provided',

                'action_url' => url('/vendor/notifications'),

                'dashboard_url' => url('/vendor/dashboard'),
            ]
        );

        return $this
            ->subject($email['subject'])
            ->view('emails.dynamic')
            ->with([
                'emailSubject' => $email['subject'],
                'body' => $email['body'],
            ]);
    }

    protected function buildHoardingDetails(): string
    {
        $html = '';

        foreach ($this->items as $item) {
            $title = e(
                $item->hoarding->title
                ?? 'Hoarding #' . $item->hoarding_id
            );

            $location = e(
                $item->hoarding->display_location
                ?? 'Location not specified'
            );

            $type = e(
                strtoupper($item->hoarding_type ?? 'N/A')
            );

            $package = '';

            if (($item->package_type ?? null) === 'package') {
                $packageLabel = e(
                    $item->package_label ?? 'Standard Package'
                );

                $package = "
                    <tr>
                        <td style=\"padding:8px; font-size:13px;\">
                            <strong>Package</strong>
                        </td>
                        <td style=\"padding:8px; font-size:13px;\">
                            {$packageLabel}
                        </td>
                    </tr>
                ";
            }

            $duration = '';

            if (!empty($item->expected_duration)) {
                $expectedDuration = e($item->expected_duration);

                $duration = "
                    <tr>
                        <td style=\"padding:8px; font-size:13px;\">
                            <strong>Expected Duration</strong>
                        </td>
                        <td style=\"padding:8px; font-size:13px;\">
                            {$expectedDuration}
                        </td>
                    </tr>
                ";
            }

            $doohDetails = '';

            if (
                ($item->hoarding_type ?? null) === 'dooh'
                && isset($item->meta['dooh_specs'])
            ) {
                $specs = $item->meta['dooh_specs'];

                $videoDuration = e(
                    $specs['video_duration'] ?? 15
                );

                $slotsPerDay = e(
                    $specs['slots_per_day'] ?? 120
                );

                $totalDays = e(
                    $specs['total_days'] ?? 0
                );

                $doohDetails = "
                    <tr>
                        <td style=\"padding:8px; font-size:13px;\">
                            <strong>DOOH Video</strong>
                        </td>
                        <td style=\"padding:8px; font-size:13px;\">
                            {$videoDuration} sec
                        </td>
                    </tr>

                    <tr>
                        <td style=\"padding:8px; font-size:13px;\">
                            <strong>Slots / Day</strong>
                        </td>
                        <td style=\"padding:8px; font-size:13px;\">
                            {$slotsPerDay}
                        </td>
                    </tr>

                    <tr>
                        <td style=\"padding:8px; font-size:13px;\">
                            <strong>Total Days</strong>
                        </td>
                        <td style=\"padding:8px; font-size:13px;\">
                            {$totalDays}
                        </td>
                    </tr>
                ";
            }

            $html .= "
                <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\"
                    border=\"0\"
                    style=\"
                        margin-bottom:20px;
                        border:1px solid #e5e7eb;
                        border-radius:6px;
                        overflow:hidden;
                    \">

                    <tr style=\"background:#f3f4f6;\">
                        <td colspan=\"2\"
                            style=\"
                                padding:10px;
                                font-size:14px;
                                font-weight:bold;
                                color:#222;
                            \">
                            {$title}
                        </td>
                    </tr>

                    <tr>
                        <td style=\"padding:8px; font-size:13px;\">
                            <strong>Location</strong>
                        </td>
                        <td style=\"padding:8px; font-size:13px;\">
                            {$location}
                        </td>
                    </tr>

                    <tr>
                        <td style=\"padding:8px; font-size:13px;\">
                            <strong>Type</strong>
                        </td>
                        <td style=\"padding:8px; font-size:13px;\">
                            {$type}
                        </td>
                    </tr>

                    {$package}

                    {$duration}

                    {$doohDetails}

                </table>
            ";
        }

        return $html ?: '
            <p style="color:#666; font-size:13px;">
                No hoarding details available.
            </p>
        ';
    }
}