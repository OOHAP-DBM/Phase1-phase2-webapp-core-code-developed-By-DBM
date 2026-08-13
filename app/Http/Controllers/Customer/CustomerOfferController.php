<?php
// app/Http/Controllers/Customer/CustomerOfferController.php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferVersion;
use App\Models\OfferVersionItem;
use App\Models\OfferActivityLog;
use App\Models\Hoarding;
use Modules\Hoardings\Services\HoardingAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\OfferVersionDiffService;
use App\Services\OfferBookingService;
use App\Notifications\Offers\OfferAcceptedByCustomerNotification;
use App\Notifications\Offers\OfferRejectedByCustomerNotification;


use Carbon\Carbon;

class CustomerOfferController extends Controller
{
    protected HoardingAvailabilityService $availabilityService;
    protected OfferVersionDiffService $diffService;
    protected OfferBookingService $bookingService;



    // public function __construct(HoardingAvailabilityService $availabilityService, OfferVersionDiffService $diffService)
    public function __construct(HoardingAvailabilityService $availabilityService, OfferVersionDiffService $diffService, OfferBookingService $bookingService)
    {
        $this->availabilityService = $availabilityService;
        $this->diffService = $diffService;
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $customer = Auth::user();

        $query = Offer::with(['vendor', 'currentVersion.items.hoarding'])
            ->where('customer_id', $customer->id);

        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('offer_number', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($vq) use ($search) {
                        $vq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $statuses = array_filter((array) $request->get('status', []));
        if (!empty($statuses) && !in_array('all', $statuses, true)) {
            $query->whereIn('status', $statuses);
        }

        $perPage = (int) $request->get('per_page', 5);
        $offers = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html'    => view('customer.offers.partials.table', compact('offers'))->render(),
                'pagination_html' => view('customer.offers.partials.pagination', compact('offers'))->render(),
            ]);
        }

        return view('customer.offers.index', compact('offers'));
    }

    // public function show(Offer $offer)
    // {
    //     abort_unless($offer->customer_id === Auth::id(), 403);
    //     $offer->load('currentVersion.items.hoarding.doohScreen', 'vendor', 'activityLogs');
    //     return view('customer.offers.show', compact('offer'));
    // }
      public function show(Offer $offer)
    {
        abort_unless($offer->customer_id === Auth::id(), 403);

        $offer->load([
            'currentVersion.items.hoarding.doohScreen',
            'vendor',
            'activityLogs.actor',
        ]);

       $versionDiffs = $this->diffService->build($offer);


return view('customer.offers.show', compact('offer', 'versionDiffs') + ['isVendorView' => false]);    }

    public function accept(Offer $offer): JsonResponse
    {
        abort_unless($offer->customer_id === Auth::id(), 403);
        if (!$offer->canAccept()) {
            return response()->json(['success' => false, 'message' => 'This offer can no longer be accepted.'], 422);
        }

        $offer->update(['status' => Offer::STATUS_ACCEPTED, 'accepted_at' => now()]);
        OfferActivityLog::record($offer, 'accepted', 'Offer accepted by customer');

        Offer::where('enquiry_id', $offer->enquiry_id)
            ->where('id', '!=', $offer->id)
            ->where('status', Offer::STATUS_SENT)
            ->get()
            ->each(function ($sibling) {
                $sibling->update(['status' => Offer::STATUS_REJECTED, 'rejected_at' => now()]);
                OfferActivityLog::record($sibling, 'auto_rejected', 'Auto-rejected: customer accepted a different offer for this enquiry');
            });

        try {
            \Mail::to($offer->vendor->email)->queue(new \App\Mail\OfferAcceptedMail($offer));
        } catch (\Exception $e) {
            Log::warning('Offer-accepted notification failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
        }
           if ($offer->vendor) {
        $offer->vendor->notify(new OfferAcceptedByCustomerNotification($offer->fresh(['customer', 'vendor'])));
    }

        return response()->json(['success' => true, 'message' => 'Offer accepted successfully']);
    }

    public function reject(Request $request, Offer $offer): JsonResponse
    {
        abort_unless($offer->customer_id === Auth::id(), 403);
        $request->validate(['reason' => 'nullable|string|max:500']);

        $offer->update(['status' => Offer::STATUS_REJECTED, 'rejected_at' => now()]);
        OfferActivityLog::record($offer, 'rejected', 'Offer rejected by customer', ['reason' => $request->get('reason')]);

        try {
            \Mail::to($offer->vendor->email)->queue(new \App\Mail\OfferRejectedMail($offer, $request->get('reason')));
        } catch (\Exception $e) {
            Log::warning('Offer-rejected notification failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
        }
         if ($offer->vendor) {
        $offer->vendor->notify(new OfferRejectedByCustomerNotification($offer->fresh(['customer', 'vendor']), $request->get('reason')));
    }

        return response()->json(['success' => true, 'message' => 'Offer rejected']);
    }

    /**
     * Show the builder — same shape as the vendor's create/edit screen, but
     * scoped to this specific offer's vendor and seeded from the current version.
     */
    // public function modify(Offer $offer)
    // {
    //     abort_unless($offer->customer_id === Auth::id(), 403);

    //     if (!$offer->canAccept() || !$offer->wasLastModifiedByVendor()) {
    //         return redirect()->route('customer.offers.show', $offer->id)
    //             ->with('error', 'This offer is not currently open for modification.');
    //     }

    //     $offer->load('currentVersion.items.hoarding.doohScreen', 'vendor');

    //     $seed = [];
    //     foreach ($offer->currentVersion->items as $vi) {
    //         $h = $vi->hoarding;
    //         if (!$h) continue;
    //         $seed[] = [
    //             'hoarding_id'         => $h->id,
    //             'enquiry_item_id'     => $vi->enquiry_item_id,
    //             'title'               => $h->title ?? $h->address,
    //             'city'                => $h->city,
    //             'location'            => $h->address,
    //             'hoarding_type'       => $vi->hoarding_type,
    //             'price_per_month'     => (float) ($vi->unit_price / max(1, $vi->duration_months)),
    //             'image_url'           => null,
    //             'startDate'           => optional($vi->start_date)->format('Y-m-d'),
    //             'endDate'             => optional($vi->end_date)->format('Y-m-d'),
    //             'total_slots_per_day' => $h->doohScreen->total_slots_per_day ?? 300,
    //             'source'              => $vi->enquiry_item_id ? 'enquiry' : 'added',
    //         ];
    //     }

    //     return view('customer.offers.modify', compact('offer', 'seed'));
    // }
// app/Http/Controllers/Customer/CustomerOfferController.php — modify()

public function modify(Offer $offer)
{
    abort_unless($offer->customer_id === Auth::id(), 403);

    // FIX: was checking canAccept() + wasLastModifiedByVendor() only —
    // canAccept() already implies isNegotiable() internally, so this was
    // technically already correct, but making it explicit here so the same
    // guard reads identically to the vendor side above.
    if (!$offer->isNegotiable() || !$offer->wasLastModifiedByVendor()) {
        return redirect()->route('customer.offers.show', $offer->id)
            ->with('error', 'This offer is not currently open for modification.');
    }

    $offer->load('currentVersion.items.hoarding.doohScreen', 'vendor');

    $seed = [];
    foreach ($offer->currentVersion->items as $vi) {
        $h = $vi->hoarding;
        if (!$h) continue;
        $seed[] = [
            'hoarding_id'         => $h->id,
            'enquiry_item_id'     => $vi->enquiry_item_id,
            'title'               => $h->title ?? $h->address,
            'city'                => $h->city,
            'location'            => $h->address,
            'hoarding_type'       => $vi->hoarding_type,
            'price_per_month'     => (float) ($vi->unit_price / max(1, $vi->duration_months)),
            'image_url'           => null,
            'startDate'           => optional($vi->start_date)->format('Y-m-d'),
            'endDate'             => optional($vi->end_date)->format('Y-m-d'),
            'total_slots_per_day' => $h->doohScreen->total_slots_per_day ?? 300,
            'source'              => $vi->enquiry_item_id ? 'enquiry' : 'added',
        ];
    }

    return view('customer.offers.modify', compact('offer', 'seed'));
}
    /**
     * Vendor's hoarding inventory, scoped by the offer's vendor_id — NOT the
     * currently authenticated user, since the customer is browsing the vendor's
     * catalogue, not their own.
     */
    public function getHoardings(Request $request, Offer $offer): JsonResponse
    {
        abort_unless($offer->customer_id === Auth::id(), 403);

        try {
            $query = Hoarding::query()
                ->where('vendor_id', $offer->vendor_id)
                ->where('status', 'active');

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            }

            $perPage = (int) $request->get('per_page', 12);
            $paginator = $query
                ->select(['id', 'title', 'address', 'city', 'state', 'hoarding_type', 'category', 'base_monthly_price', 'monthly_price'])
                ->orderBy('title')
                ->paginate($perPage);

            $data = $paginator->getCollection()->map(function ($hoarding) {
                $pricePerMonth = isset($hoarding->monthly_price) ? (float) $hoarding->monthly_price : null;
                if (!$pricePerMonth || $pricePerMonth <= 0) {
                    $pricePerMonth = $hoarding->base_monthly_price ?? 0;
                }

                return [
                    'id'                  => $hoarding->id,
                    'title'               => $hoarding->title,
                    'location_city'       => $hoarding->city,
                    'location_address'    => $hoarding->address,
                    'type'                => $hoarding->hoarding_type,
                    'price_per_month'     => $pricePerMonth,
                    'image_url'           => $this->getHoardingImageUrl($hoarding),
                    'total_slots_per_day' => $hoarding->doohScreen->total_slots_per_day ?? 300,
                ];
            });

            return response()->json([
                'success'     => true,
                'data'        => $data,
                'count'       => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page'   => $paginator->lastPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching vendor hoardings for customer modify', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch hoardings'], 500);
        }
    }

    protected function getHoardingImageUrl(Hoarding $hoarding): ?string
    {
        try {
            if ($hoarding->hoarding_type === 'ooh') {
                $media = \Modules\Hoardings\Models\HoardingMedia::where('hoarding_id', $hoarding->id)
                    ->orderByDesc('is_primary')->orderBy('sort_order')->first();
                return $media ? asset('storage/' . ltrim($media->file_path, '/')) : null;
            }
            if ($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen) {
                if (method_exists($hoarding->doohScreen, 'getFirstMedia')) {
                    $media = $hoarding->doohScreen->getFirstMedia('hero_image') ?? $hoarding->doohScreen->getFirstMedia('gallery');
                    if ($media) return $media->getUrl();
                }
                $media = $hoarding->doohScreen->media()->orderByDesc('is_primary')->orderBy('sort_order')->first();
                return $media && $media->file_path ? asset('storage/' . ltrim($media->file_path, '/')) : null;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Customer submits their modified offer — creates a new OfferVersion with
     * created_by_type = 'customer'. Offer stays 'sent'; wasLastModifiedByCustomer()
     * is what flips the vendor's Manage Offers action to "Accept Customer's Offer".
     */
    // public function storeModification(Request $request, Offer $offer): JsonResponse
    // {
    //     abort_unless($offer->customer_id === Auth::id(), 403);

    //     if (!$offer->canAccept() || !$offer->wasLastModifiedByVendor()) {
    //         return response()->json(['success' => false, 'message' => 'This offer is not currently open for modification.'], 422);
    //     }

    //     $validated = $request->validate([
    //         'items'                             => 'required|array|min:1',
    //         'items.*.hoarding_id'               => 'required|integer|exists:hoardings,id',
    //         'items.*.enquiry_item_id'           => 'nullable|integer|exists:enquiry_items,id',
    //         'items.*.hoarding_type'             => 'required|in:ooh,dooh',
    //         'items.*.start_date'                => 'required|date',
    //         'items.*.end_date'                  => 'required|date|after_or_equal:items.*.start_date',
    //         'items.*.unit_price'                => 'required|numeric|min:0',
    //         'items.*.discount_amount'           => 'nullable|numeric|min:0',
    //         'items.*.final_price'               => 'required|numeric|min:0',
    //     ]);

    //     $hoardingIds = collect($validated['items'])->pluck('hoarding_id')->unique()->values();
    //     $ownedCount  = Hoarding::whereIn('id', $hoardingIds)->where('vendor_id', $offer->vendor_id)->count();
    //     if ($ownedCount !== $hoardingIds->count()) {
    //         return response()->json(['success' => false, 'message' => 'One or more hoardings do not belong to this vendor.'], 403);
    //     }

    //     $conflicts = [];
    //     foreach ($validated['items'] as $item) {
    //         $result = $this->availabilityService->checkMultipleDates($item['hoarding_id'], [$item['start_date'], $item['end_date']]);
    //         $bad = collect($result)->pluck('status')->filter(fn ($s) => !in_array($s, ['available', 'blocked']))->unique();
    //         if ($bad->isNotEmpty()) {
    //             $h = Hoarding::find($item['hoarding_id']);
    //             $conflicts[] = ['hoarding_id' => $item['hoarding_id'], 'hoarding_name' => $h->title ?? "Hoarding #{$item['hoarding_id']}", 'reasons' => $bad->values()];
    //         }
    //     }
    //     if (!empty($conflicts)) {
    //         return response()->json(['success' => false, 'message' => 'Some hoardings are no longer available for the selected dates.', 'unavailable_hoardings' => $conflicts], 422);
    //     }

    //     $lock = Cache::lock("offer-modify:{$offer->id}", 15);
    //     if (!$lock->get()) {
    //         return response()->json(['success' => false, 'message' => 'This offer is already being updated. Please try again shortly.'], 409);
    //     }

    //     try {
    //         $customer = Auth::user();

    //         $offer = DB::transaction(function () use ($validated, $customer, $offer) {
    //             $subtotal = collect($validated['items'])->sum('unit_price');
    //             $discount = collect($validated['items'])->sum(fn ($i) => $i['discount_amount'] ?? 0);
    //             $total    = collect($validated['items'])->sum('final_price');

    //             $nextVersionNumber = ($offer->getLatestVersion()?->version_number ?? 0) + 1;

    //             $version = OfferVersion::create([
    //                 'offer_id'        => $offer->id,
    //                 'version_number'  => $nextVersionNumber,
    //                 'created_by'      => $customer->id,
    //                 'created_by_type' => 'customer',
    //                 'status'          => 'sent',
    //                 'subtotal'        => $subtotal,
    //                 'discount_amount' => $discount,
    //                 'tax_amount'      => 0,
    //                 'total_amount'    => $total,
    //             ]);

    //             $offer->update([
    //                 'current_version_id' => $version->id,
    //                 'price'               => $total,
    //                 'price_snapshot'      => ['items' => $validated['items']],
    //                 'version'             => $nextVersionNumber,
    //                 // status stays STATUS_SENT — it's still an open offer, just the
    //                 // vendor's turn to respond now instead of the customer's
    //             ]);

    //             foreach ($validated['items'] as $item) {
    //                 $start = Carbon::parse($item['start_date']);
    //                 $end   = Carbon::parse($item['end_date']);

    //                 OfferVersionItem::create([
    //                     'offer_version_id' => $version->id,
    //                     'enquiry_item_id'  => $item['enquiry_item_id'] ?? null,
    //                     'hoarding_id'      => $item['hoarding_id'],
    //                     'hoarding_type'    => $item['hoarding_type'],
    //                     'start_date'       => $start,
    //                     'end_date'         => $end,
    //                     'duration_months'  => max(1, (int) ceil(($end->diffInDays($start) + 1) / 30)),
    //                     'unit_price'       => $item['unit_price'],
    //                     'discount_amount'  => $item['discount_amount'] ?? 0,
    //                     'tax_amount'       => 0,
    //                     'final_price'      => $item['final_price'],
    //                     'meta'             => ['source' => !empty($item['enquiry_item_id']) ? 'enquiry' : 'added'],
    //                 ]);
    //             }

    //             OfferActivityLog::record($offer, 'customer_modified', "Customer modified the offer — version {$nextVersionNumber} created");

    //             return $offer;
    //         });

    //         try {
    //             \Mail::to($offer->vendor->email)->queue(
    //                 new \App\Mail\OfferModifiedByCustomerMail($offer->fresh(['currentVersion.items.hoarding.doohScreen', 'customer', 'vendor']))
    //             );
    //         } catch (\Exception $e) {
    //             Log::warning('Customer-modification notification failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Your changes have been sent to the vendor',
    //             'data'    => ['redirect' => route('customer.offers.show', $offer->id)],
    //         ], 201);
    //     } finally {
    //         $lock->release();
    //     }
    // }
    // app/Http/Controllers/Customer/CustomerOfferController.php

public function storeModification(Request $request, Offer $offer): JsonResponse
{
    abort_unless($offer->customer_id === Auth::id(), 403);

    if (!$offer->isNegotiable() || !$offer->wasLastModifiedByVendor()) {
        return response()->json(['success' => false, 'message' => 'This offer is not currently open for modification.'], 422);
    }
    if (!$offer->canAccept() || !$offer->wasLastModifiedByVendor()) {
        return response()->json(['success' => false, 'message' => 'This offer is not currently open for modification.'], 422);
    }

    $validated = $request->validate([
        'items'                             => 'required|array|min:1',
        'items.*.hoarding_id'               => 'required|integer|exists:hoardings,id',
        'items.*.enquiry_item_id'           => 'nullable|integer|exists:enquiry_items,id',
        'items.*.hoarding_type'             => 'required|in:ooh,dooh',
        'items.*.start_date'                => 'required|date',
        'items.*.end_date'                  => 'required|date|after_or_equal:items.*.start_date',
        'items.*.unit_price'                => 'required|numeric|min:0',
        'items.*.discount_amount'           => 'nullable|numeric|min:0',
        'items.*.final_price'               => 'required|numeric|min:0',
    ]);

    $failure = $this->bookingService->validateItems($validated['items'], $offer->vendor_id);
    if ($failure) {
        return response()->json(['success' => false, 'message' => $failure['message'], 'unavailable_hoardings' => $failure['unavailable_hoardings'] ?? null], $failure['status']);
    }

    try {
        $offer = $this->bookingService->submitCustomerModification($offer, $validated, Auth::user());
    } catch (\RuntimeException $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
    }

    return response()->json([
        'success' => true,
        'message' => 'Your changes have been sent to the vendor',
        'data'    => ['redirect' => route('customer.offers.show', $offer->id)],
    ], 201);
}
}
