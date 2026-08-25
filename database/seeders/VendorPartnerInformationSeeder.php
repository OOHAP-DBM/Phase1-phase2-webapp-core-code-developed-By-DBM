<?php

namespace Database\Seeders;

use App\Models\VendorPartnerInformation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VendorPartnerInformationSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        VendorPartnerInformation::truncate();

        VendorPartnerInformation::create([
            'title' => 'Partner With OOHAPP',
            'content' => <<<'HTML'

<div class="vendor-partner-section">

<h2><strong>Partner With OOHAPP</strong></h2>

<div class="section-content">
<p><strong>Grow Your Outdoor Advertising Business With OOHAPP</strong></p>

<p>
OOHAPP is a digital platform that connects advertisers and businesses with outdoor advertising
inventory providers, media owners, and advertising partners.
</p>

<p>
If you own or manage billboards, hoardings, digital screens, kiosks, transit advertising spaces,
street furniture, or other outdoor advertising media, you can partner with OOHAPP to showcase your
advertising inventory and receive booking opportunities from customers.
</p>
</div>

<hr>

<h2><strong>Who Can Become an OOHAPP Partner?</strong></h2>

<div class="section-content">
<p>OOHAPP welcomes eligible:</p>

<ul>
    <li>Billboard and hoarding owners</li>
    <li>Outdoor advertising agencies</li>
    <li>Digital screen and LED display owners</li>
    <li>Transit advertising operators</li>
    <li>Mall and commercial-property advertising partners</li>
    <li>Local outdoor media owners</li>
    <li>Advertising media agencies</li>
    <li>Other businesses legally authorized to provide outdoor advertising inventory</li>
</ul>

<p>
Partners must have the necessary rights, permissions, licenses, or authorizations required to offer
the advertising space listed on the OOHAPP platform.
</p>
</div>

<hr>

<h2><strong>How OOHAPP Works for Partners</strong></h2>

<div class="section-content">

<h3>1. Register Your Business</h3>
<p>
Create your partner/vendor account and provide the required business and verification information.
</p>

<h3>2. Add Your Advertising Inventory</h3>
<p>List your available advertising spaces with relevant information such as:</p>

<ul>
    <li>Location</li>
    <li>Advertising format</li>
    <li>Display size</li>
    <li>Availability</li>
    <li>Campaign duration</li>
    <li>Pricing</li>
    <li>Photos and media</li>
    <li>Installation or display specifications</li>
    <li>Applicable terms and conditions</li>
</ul>

<h3>3. Receive Customer Bookings</h3>
<p>
Customers can discover available advertising opportunities on OOHAPP and submit bookings through
the platform.
</p>

<h3>4. Booking &amp; Payment</h3>
<p>
Once a customer completes the booking and payment process, OOHAPP processes the transaction through
its payment infrastructure.
</p>

<h3>5. Campaign Fulfilment</h3>
<p>
Partners are responsible for providing the booked advertising space and fulfilling the campaign
according to the agreed booking details.
</p>

<h3>6. Partner Payout</h3>
<p>
After applicable platform fees, commissions, adjustments, refunds, or other applicable deductions,
eligible partner funds are settled according to OOHAPP's payout terms.
</p>

</div>

<hr>

<h2><strong>Partner Responsibilities</strong></h2>

<div class="section-content">
<p>As an OOHAPP partner, you are responsible for:</p>

<ul>
    <li>Providing accurate information about your advertising inventory.</li>
    <li>Maintaining the availability information of listed advertising spaces.</li>
    <li>Having the legal right or authorization to offer the advertising inventory.</li>
    <li>Complying with applicable laws, regulations, municipal requirements, and advertising guidelines.</li>
    <li>Delivering the advertising space as booked by the customer.</li>
    <li>Maintaining the agreed quality and specifications of the advertising placement.</li>
    <li>Providing required campaign-related information or proof of display when requested.</li>
    <li>
        Informing OOHAPP promptly about changes to availability, pricing, ownership, permissions,
        or other relevant information.
    </li>
</ul>
</div>

<hr>

<h2><strong>Verification &amp; Compliance</strong></h2>

<div class="section-content">
<p>
OOHAPP may request business, identity, tax, ownership, authorization, bank account, or other
documentation from partners before approving or activating an account or advertising inventory.
</p>

<p>
OOHAPP reserves the right to review, suspend, reject, or remove listings that do not meet its platform
requirements or applicable legal and compliance standards.
</p>
</div>

<hr>

<h2><strong>Partner Payouts</strong></h2>

<div class="section-content">
<p>
Partner payouts are processed according to OOHAPP's applicable settlement and payout policies.
</p>

<p>The amount payable to a partner may be affected by:</p>

<ul>
    <li>OOHAPP platform/service fees</li>
    <li>Applicable commissions</li>
    <li>Taxes and statutory deductions</li>
    <li>Refunds or cancellations</li>
    <li>Payment processing adjustments</li>
    <li>Disputed transactions</li>
    <li>Other amounts contractually or legally payable</li>
</ul>

<p>
The applicable commercial terms will be communicated to the partner during onboarding or before the
relevant transaction.
</p>
</div>

<hr>

<h2><strong>Become an OOHAPP Partner</strong></h2>

<div class="section-content">
<p>
If you own or manage outdoor advertising inventory and want to reach more advertisers, join OOHAPP
as a partner.
</p>

<p>
Partner with OOHAPP and make your advertising inventory easier to discover, book, and manage.
</p>

<p>
For partnership or vendor-related enquiries, please contact the OOHAPP team through the contact
information provided on our website.
</p>
</div>

</div>

HTML,
            'is_active' => 1,
        ]);
    }
}
