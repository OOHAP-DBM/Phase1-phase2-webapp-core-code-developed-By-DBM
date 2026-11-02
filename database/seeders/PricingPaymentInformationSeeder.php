<?php

namespace Database\Seeders;

use App\Models\PricingPaymentInformation;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PricingPaymentInformationSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        PricingPaymentInformation::truncate();

        PricingPaymentInformation::create([
            'title' => 'Pricing & Payments',
            'content' => <<<'HTML'

<div class="pricing-payment-section">

<h2><strong>Pricing &amp; Payments</strong></h2>

<div class="section-content">
<p><strong>Transparent Pricing for Outdoor Advertising</strong></p>

<p>
OOHAPP provides a marketplace that enables customers to discover and book outdoor advertising
opportunities offered by participating advertising partners and media owners.
</p>

<p>
Pricing may vary depending on the advertising location, media type, size, duration, availability,
campaign requirements, and individual partner pricing.
</p>

<p>
The applicable price for a booking will be displayed to the customer before the customer confirms
and pays for the booking.
</p>
</div>

<hr>

<h2><strong>How Pricing Works</strong></h2>

<div class="section-content">
<p>The price of an advertising booking may include:</p>

<ul>
    <li>Advertising/media space charges</li>
    <li>Campaign duration charges</li>
    <li>Applicable OOHAPP platform or service fees</li>
    <li>Payment processing charges, where applicable</li>
    <li>Applicable taxes</li>
    <li>
        Installation, production, printing, or other campaign-related charges,
        where applicable
    </li>
</ul>

<p>
The exact charges applicable to a particular booking will be displayed during the booking and
checkout process.
</p>
</div>

<hr>

<h2><strong>Customer Payments</strong></h2>

<div class="section-content">
<p>
Customers can make payments through the payment methods made available by OOHAPP through its
payment service providers.
</p>

<p>Depending on availability, payment methods may include:</p>

<ul>
    <li>Credit cards</li>
    <li>Debit cards</li>
    <li>Net banking</li>
    <li>UPI</li>
    <li>Other supported digital payment methods</li>
</ul>

<p>
Payment availability may vary depending on the customer's location, payment provider, transaction
value, and other applicable factors.
</p>
</div>

<hr>

<h2><strong>Payment Processing</strong></h2>

<div class="section-content">
<p>
Payments made through OOHAPP are processed using authorized third-party payment service providers.
</p>

<p>
OOHAPP may facilitate the collection and settlement of customer payments between customers and
participating advertising partners in accordance with the applicable booking and partner terms.
</p>

<p>
A successful payment does not by itself change the booking terms, campaign requirements,
cancellation policy, or other conditions applicable to the booking.
</p>
</div>

<hr>

<h2><strong>Vendor / Partner Settlement</strong></h2>

<div class="section-content">
<p>
For bookings involving an OOHAPP partner, the applicable partner amount may be settled after
accounting for OOHAPP's applicable fees, commissions, taxes, refunds, cancellations, disputes,
and other applicable adjustments.
</p>

<p>
Partner settlement timelines may vary depending on the booking terms, campaign completion,
payment settlement status, verification requirements, and applicable payment-provider processes.
</p>
</div>

<hr>

<h2><strong>OOHAPP Fees</strong></h2>

<div class="section-content">
<p>
OOHAPP may charge platform, service, commission, or other applicable fees for facilitating
transactions and providing marketplace services.
</p>

<p>
Where applicable, the relevant fee or charge will be disclosed to the customer or partner before
the transaction is completed or as part of the applicable commercial agreement.
</p>

<p>
OOHAPP does not charge undisclosed fees after a customer has completed a transaction, except where
the additional charge is required by law, arises from a customer-requested change, or is otherwise
permitted under the applicable terms.
</p>
</div>

<hr>

<h2><strong>Taxes</strong></h2>

<div class="section-content">
<p>
Applicable taxes, including GST or other statutory charges, may apply to transactions and services
provided through OOHAPP.
</p>

<p>
Where applicable, taxes will be displayed separately or incorporated into the applicable price in
accordance with applicable law.
</p>

<p>
Customers and partners are responsible for complying with their respective tax obligations.
</p>
</div>

<hr>

<h2><strong>Cancellations &amp; Refunds</strong></h2>

<div class="section-content">
<p>
Cancellation and refund eligibility depends on the terms applicable to the particular advertising
booking.
</p>

<p>
Where a booking is eligible for a refund, the refund will generally be processed through the
original payment method, subject to payment-provider processing timelines and applicable terms.
</p>

<p>Refund amounts may be affected by:</p>

<ul>
    <li>Cancellation timing</li>
    <li>Booking-specific cancellation terms</li>
    <li>Campaign commencement status</li>
    <li>Services already provided</li>
    <li>Production or installation costs</li>
    <li>Applicable platform or processing charges</li>
    <li>Other deductions permitted under the applicable terms</li>
</ul>

<p>
Customers should review the cancellation and refund terms applicable to their booking before
completing payment.
</p>
</div>

<hr>

<h2><strong>Failed or Pending Payments</strong></h2>

<div class="section-content">
<p>
If a payment is unsuccessful, pending, or interrupted, customers should check their payment status
before attempting another payment.
</p>

<p>
If an amount has been debited but the booking has not been confirmed, the customer should contact
OOHAPP support with the relevant transaction or booking details.
</p>

<p>
OOHAPP will coordinate with its payment service provider to verify the transaction status and
process any eligible refund or resolution.
</p>
</div>

<hr>

<h2><strong>Payment Security</strong></h2>

<div class="section-content">
<p>
OOHAPP uses third-party payment service providers to process online payments. Payment credentials
such as card details and other sensitive payment information are handled through the applicable
payment-processing infrastructure and are not intended to be stored directly by OOHAPP unless
otherwise stated in its Privacy Policy.
</p>

<p>
Customers should never share their OTP, PIN, CVV, password, or other confidential payment
credentials with anyone claiming to represent OOHAPP.
</p>
</div>

<hr>

<h2><strong>Need Help With a Payment?</strong></h2>

<div class="section-content">
<p>
For payment, booking, refund, or transaction-related assistance, please contact OOHAPP through the
customer support/contact details provided on our website.
</p>

<p>
When contacting support, please provide your booking ID or transaction reference so that our team
can investigate the issue efficiently.
</p>
</div>

</div>

HTML,
            'is_active' => 1,
        ]);
    }
}
