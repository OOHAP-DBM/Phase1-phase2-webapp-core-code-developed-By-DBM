<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\OfferVersion;
use App\Models\OfferVersionItem;
use App\Models\OfferActivityLog;
use App\Models\Hoarding;
use Modules\Enquiries\Models\Enquiry;
use Modules\Enquiries\Models\EnquiryItem;
use Modules\Hoardings\Services\HoardingAvailabilityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Notifications\Offers\OfferCreatedNotification;
use App\Notifications\Offers\OfferModifiedByVendorNotification;
use App\Notifications\Offers\OfferModifiedByCustomerNotification;

class OfferBookingService
{

    protected HoardingAvailabilityService $availabilityService;

    public function __construct(HoardingAvailabilityService $availabilityService)
    {


        $this->availabilityService = $availabilityService;
    }

    /**
     * Validates ownership + availability for a set of proposed items.
     * Returns null on success, or an array describing the failure to return
     * as a 4xx response.
     */
    public function validateItems(array $items, int $vendorId): ?array
    {


        $hoardingIds = collect($items)->pluck('hoarding_id')->unique()->values();
        $ownedCount = Hoarding::whereIn('id', $hoardingIds)->where('vendor_id', $vendorId)->count();

        if ($ownedCount !== $hoardingIds->count()) {
            return ['status' => 403, 'message' => 'One or more selected hoardings do not belong to this vendor.'];
        }

        $conflicts = [];
        foreach ($items as $item) {
            $result = $this->availabilityService->checkMultipleDates($item['hoarding_id'], [$item['start_date'], $item['end_date']]);
            $bad = collect($result)->pluck('status')->filter(fn ($s) => !in_array($s, ['available', 'blocked']))->unique();
            if ($bad->isNotEmpty()) {
                $h = Hoarding::find($item['hoarding_id']);
                $conflicts[] = ['hoarding_id' => $item['hoarding_id'], 'hoarding_name' => $h->title ?? "Hoarding #{$item['hoarding_id']}", 'reasons' => $bad->values()];
            }
        }

        if (!empty($conflicts)) {
            return ['status' => 422, 'message' => 'Some hoardings are no longer available for the selected dates.', 'unavailable_hoardings' => $conflicts];
        }

        return null;
    }

    /**
     * Vendor creates a new offer, or adds a new version to an existing one
     * for the same enquiry, then sends it via email/WhatsApp.
     *
     * @throws \RuntimeException on lock contention — caller decides the HTTP status
     */
    public function createOrModifyByVendor(array $validated, $vendor): Offer
    {
        $enquiry = Enquiry::with('customer')->findOrFail($validated['enquiry_id']);

        $existingOffer = null;
        if (!empty($validated['offer_id'])) {
            $existingOffer = Offer::where('id', $validated['offer_id'])->where('vendor_id', $vendor->id)->first();
        } else {
            $existingOffer = Offer::where('enquiry_id', $enquiry->id)
                ->where('vendor_id', $vendor->id)
                ->whereNull('archived_at')
                ->whereNotIn('status', [Offer::STATUS_CANCELLED])
                ->latest('id')
                ->first();
        }

        $lockKey = "offer-store:{$vendor->id}:{$validated['enquiry_id']}";
        $lock = Cache::lock($lockKey, 15);

        if (!$lock->get()) {
            throw new \RuntimeException('This offer is already being submitted. Please wait a moment and refresh.');
        }

        try {
            $offer = DB::transaction(function () use ($validated, $vendor, $enquiry, $existingOffer) {
                $subtotal = collect($validated['items'])->sum('unit_price');
                $discount = collect($validated['items'])->sum(fn ($i) => $i['discount_amount'] ?? 0);
                $total    = collect($validated['items'])->sum('final_price');

                if ($existingOffer) {
                    $offer = $existingOffer;
                    $nextVersionNumber = ($offer->getLatestVersion()?->version_number ?? 0) + 1;

                    $version = OfferVersion::create([
                        'offer_id'        => $offer->id,
                        'version_number'  => $nextVersionNumber,
                        'created_by'      => $vendor->id,
                        'created_by_type' => 'vendor',
                        'status'          => 'draft',
                        'subtotal'        => $subtotal,
                        'discount_amount' => $discount,
                        'tax_amount'      => 0,
                        'total_amount'    => $total,
                    ]);

                    $offer->update([
                        'current_version_id' => $version->id,
                        'price'               => $total,
                        'price_type'          => $validated['price_type'] ?? $offer->price_type,
                        'price_snapshot'      => ['items' => $validated['items']],
                        'description'         => $validated['description'] ?? $offer->description,
                        'valid_until'         => $validated['valid_until'] ?? $offer->valid_until,
                        'version'             => $nextVersionNumber,
                        'status'              => Offer::STATUS_DRAFT,
                        'accepted_at'         => null,
                        'rejected_at'         => null,
                        'modification_notes'  => null,
                    ]);

                    OfferActivityLog::record($offer, 'modified', "Offer modified — version {$nextVersionNumber} created");
                } else {
                    $offer = Offer::create([
                        'offer_number'   => 'OFR-' . strtoupper(uniqid()),
                        'enquiry_id'     => $enquiry->id,
                        'vendor_id'      => $vendor->id,
                        'customer_id'    => $enquiry->customer_id,
                        'price'          => $total,
                        'price_type'     => $validated['price_type'] ?? 'total',
                        'price_snapshot' => ['items' => $validated['items']],
                        'description'    => $validated['description'] ?? null,
                        'valid_until'    => $validated['valid_until'] ?? null,
                        'status'         => Offer::STATUS_DRAFT,
                        'version'        => 1,
                    ]);

                    $version = OfferVersion::create([
                        'offer_id'        => $offer->id,
                        'version_number'  => 1,
                        'created_by'      => $vendor->id,
                        'created_by_type' => 'vendor',
                        'status'          => 'draft',
                        'subtotal'        => $subtotal,
                        'discount_amount' => $discount,
                        'tax_amount'      => 0,
                        'total_amount'    => $total,
                    ]);

                    $offer->update(['current_version_id' => $version->id]);
                    OfferActivityLog::record($offer, 'created', 'Offer created');
                }

                $this->createVersionItems($version, $validated['items']);

                $enquiryItemIds = collect($validated['items'])->pluck('enquiry_item_id')->filter();
                if ($enquiryItemIds->isNotEmpty()) {
                    EnquiryItem::whereIn('id', $enquiryItemIds)->update(['status' => 'offer_send']);
                }

                return $offer;
            });

            $this->dispatchVendorSendNotifications($offer, $enquiry, $validated);

            return $offer;
        } finally {
            $lock->release();
        }
    }

    /**
     * Customer submits their own modified version of an open offer.
     *
     * @throws \RuntimeException on lock contention
     */
    public function submitCustomerModification(Offer $offer, array $validated, $customer): Offer
    {
        $lock = Cache::lock("offer-modify:{$offer->id}", 15);
        if (!$lock->get()) {
            throw new \RuntimeException('This offer is already being updated. Please try again shortly.');
        }

        try {
            $offer = DB::transaction(function () use ($validated, $customer, $offer) {
                $subtotal = collect($validated['items'])->sum('unit_price');
                $discount = collect($validated['items'])->sum(fn ($i) => $i['discount_amount'] ?? 0);
                $total    = collect($validated['items'])->sum('final_price');

                $nextVersionNumber = ($offer->getLatestVersion()?->version_number ?? 0) + 1;

                $version = OfferVersion::create([
                    'offer_id'        => $offer->id,
                    'version_number'  => $nextVersionNumber,
                    'created_by'      => $customer->id,
                    'created_by_type' => 'customer',
                    'status'          => 'sent',
                    'subtotal'        => $subtotal,
                    'discount_amount' => $discount,
                    'tax_amount'      => 0,
                    'total_amount'    => $total,
                ]);

                $offer->update([
                    'current_version_id' => $version->id,
                    'price'               => $total,
                    'price_snapshot'      => ['items' => $validated['items']],
                    'version'             => $nextVersionNumber,
                ]);

                $this->createVersionItems($version, $validated['items']);

                OfferActivityLog::record($offer, 'customer_modified', "Customer modified the offer — version {$nextVersionNumber} created");

                return $offer;
            });

            try {
                \Mail::to($offer->vendor->email)->queue(
                    new \App\Mail\OfferModifiedByCustomerMail($offer->fresh(['currentVersion.items.hoarding.doohScreen', 'customer', 'vendor']))
                );
            } catch (\Exception $e) {
                Log::warning('Customer-modification notification failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
            }
  // In-app notification for the vendor
        if ($offer->vendor) {
            $offer->vendor->notify(new OfferModifiedByCustomerNotification($offer));
        }
            return $offer;
        } finally {
            $lock->release();
        }
    }

    protected function createVersionItems(OfferVersion $version, array $items): void
    {
        foreach ($items as $item) {
            $start = Carbon::parse($item['start_date']);
            $end   = Carbon::parse($item['end_date']);

            OfferVersionItem::create([
                'offer_version_id' => $version->id,
                'enquiry_item_id'  => $item['enquiry_item_id'] ?? null,
                'hoarding_id'      => $item['hoarding_id'],
                'hoarding_type'    => $item['hoarding_type'],
                'start_date'       => $start,
                'end_date'         => $end,
                'duration_months'  => max(1, (int) ceil(($end->diffInDays($start) + 1) / 30)),
                'unit_price'       => $item['unit_price'],
                'discount_amount'  => $item['discount_amount'] ?? 0,
                'tax_amount'       => 0,
                'final_price'      => $item['final_price'],
                'meta'             => ['source' => !empty($item['enquiry_item_id']) ? 'enquiry' : 'added'],
            ]);
        }
    }

    protected function dispatchVendorSendNotifications(Offer $offer, Enquiry $enquiry, array $validated): void
    {
        try {
            if (!empty($validated['send_email']) && $enquiry->customer?->email) {
                \Mail::to($enquiry->customer->email)->queue(
                    new \App\Mail\OfferSentMail($offer->fresh(['currentVersion.items.hoarding.doohScreen', 'customer', 'vendor']))
                );
            }
            if (!empty($validated['send_whatsapp']) && $enquiry->contact_number) {
                $whatsapp = app(\App\Services\Whatsapp\TwilioWhatsappService::class);
                $phone = preg_replace('/\D+/', '', $enquiry->contact_number);
                if (!str_starts_with($phone, '91')) $phone = '91' . ltrim($phone, '0');
                $whatsapp->send('+' . $phone, $this->buildOfferWhatsappMessage($offer, $enquiry));
            }
            $offer->update(['status' => Offer::STATUS_SENT, 'sent_at' => now()]);
            OfferActivityLog::record($offer, 'sent', 'Offer sent to customer', [
                'email' => !empty($validated['send_email']), 'whatsapp' => !empty($validated['send_whatsapp']),
            ]);
            if ($enquiry->customer) {
            $freshOffer = $offer->fresh(['currentVersion.items', 'customer', 'vendor']);
            $enquiry->customer->notify(
                $offer->wasRecentlyCreated
                    ? new OfferCreatedNotification($freshOffer)
                    : new OfferModifiedByVendorNotification($freshOffer)
            );
        }
        } catch (\Exception $e) {
            Log::warning('Offer send failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
        }
    }


    protected function buildOfferWhatsappMessage(Offer $offer, Enquiry $enquiry): string
    {
        $total = number_format((float) $offer->price, 2);
        $items = $offer->currentVersion?->items ?? collect();
        $lines = $items->map(function ($i) {
            $h = $i->hoarding;
            return "• " . ($h->title ?? "Hoarding #{$i->hoarding_id}") . " (" . strtoupper($i->hoarding_type) . ") — "
                . optional($i->start_date)->format('d M') . " to " . optional($i->end_date)->format('d M Y')
                . " — ₹" . number_format((float) $i->final_price, 2);
        })->implode("\n");

        return "🎯 *New Offer Received!*\n\n"
            . "Hello *{$enquiry->customer->name}*,\n\n"
            . "You've received an offer *#{$offer->offer_number}* for {$items->count()} hoarding(s).\n\n"
            . "{$lines}\n\n"
            . "*Total: ₹{$total}*\n\n"
            . "Please log in to review and respond to the offer.";
    }
}
