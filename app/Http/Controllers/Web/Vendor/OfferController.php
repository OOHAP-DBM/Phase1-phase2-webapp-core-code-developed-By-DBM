<?php

namespace App\Http\Controllers\Web\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Enquiries\Models\Enquiry;
use Modules\Enquiries\Models\EnquiryItem;
use Modules\Offers\Services\OfferService;
use Modules\Hoardings\Services\HoardingAvailabilityService;
use App\Models\Offer;
use App\Models\OfferVersion;
use App\Models\OfferVersionItem;
use App\Models\OfferActivityLog;
use App\Models\Hoarding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\OfferVersionDiffService;
use App\Services\OfferBookingService;
use App\Notifications\Offers\OfferAcceptedByVendorNotification;
use App\Notifications\Offers\OfferRejectedByVendorNotification;
use App\Notifications\Offers\OfferReminderNotification;
use App\Mail\OfferModifiedMail; // add import




class OfferController extends Controller
{
    protected HoardingAvailabilityService $availabilityService;
    protected OfferVersionDiffService $diffService;
    protected OfferBookingService $bookingService;


    // public function __construct(HoardingAvailabilityService $availabilityService)
        public function __construct(HoardingAvailabilityService $availabilityService, OfferVersionDiffService $diffService, OfferBookingService $bookingService)

    {
        $this->availabilityService = $availabilityService;
        $this->diffService = $diffService;
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $vendor = Auth::user();

        $query = Offer::with(['customer', 'currentVersion.items.hoarding'])
            ->where('vendor_id', $vendor->id);
           //  ->notArchived();

        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('offer_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $statuses = array_filter((array) $request->get('status', []));
        if (!empty($statuses) && !in_array('all', $statuses, true)) {
            $query->whereIn('status', $statuses);
        }

        $datePreset = $request->get('date_preset', 'all');
        $from = match ($datePreset) {
            'last_week'  => now()->subWeek(),
            'last_month' => now()->subMonth(),
            'last_year'  => now()->subYear(),
            default      => null,
        };
        if ($from) $query->where('created_at', '>=', $from);
        if ($datePreset === 'custom') {
            if ($request->filled('from_date')) $query->whereDate('created_at', '>=', $request->get('from_date'));
            if ($request->filled('to_date'))   $query->whereDate('created_at', '<=', $request->get('to_date'));
        }

        $perPage = (int) $request->get('per_page', 5);
        $offers = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        // $archivedCount = Offer::where('vendor_id', $vendor->id)->archived()->count();
                $archivedCount = Offer::where('vendor_id', $vendor->id)->count();


        // if ($request->wantsJson() || $request->ajax()) {

        //     return response()->json([
        //         'success' => true,
        //         'html'    => view('vendor.offers.partials.table', compact('offers'))->render(),
        //         'pagination_html' => view('vendor.offers.partials.pagination', compact('offers'))->render(),
        //         'archived_count' => $archivedCount,
        //     ]);
        // }

if ($request->wantsJson() || $request->ajax()) {
    $partial = $request->boolean('archived') ? 'vendor.offers.partials.archived-table' : 'vendor.offers.partials.table';

    return response()->json([
        'success' => true,
        'html'    => view($partial, compact('offers'))->render(),
        'pagination' => [
            'current_page' => $offers->currentPage(),
            'last_page'    => $offers->lastPage(),
            'per_page'     => $offers->perPage(),
            'total'        => $offers->total(),
            'from'         => $offers->firstItem(),
            'to'           => $offers->lastItem(),
        ],
        'archived_count' => $archivedCount,
    ]);
}

        return view('vendor.offers.index', compact('offers', 'archivedCount'));
    }

    // public function create(Request $request)
    // {
    //     $enquiry = null;
    //     $editingOffer = null;
    //     $seedFromOffer = [];

    //     if ($request->filled('offer_id')) {
    //         $editingOffer = Offer::with(['enquiry.customer', 'currentVersion.items.hoarding.doohScreen'])
    //             ->where('vendor_id', Auth::id())
    //             ->findOrFail($request->input('offer_id'));

    //         $enquiry = $editingOffer->enquiry()->with([
    //             'customer', 'items', 'items.hoarding', 'items.hoarding.vendor',
    //             'items.hoarding.doohScreen', 'items.package',
    //         ])->first();
    //         $enquiry->getEnquiryDetails();

    //         foreach ($editingOffer->currentVersion->items as $vi) {
    //             $h = $vi->hoarding;
    //             if (!$h) continue;
    //             $seedFromOffer[] = [
    //                 'hoarding_id'         => $h->id,
    //                 'enquiry_item_id'     => $vi->enquiry_item_id,
    //                 'title'               => $h->title ?? $h->address,
    //                 'city'                => $h->city,
    //                 'location'            => $h->address,
    //                 'hoarding_type'       => $vi->hoarding_type,
    //                 'price_per_month'     => (float) ($vi->unit_price / max(1, $vi->duration_months)),
    //                 'image_url'           => null,
    //                 'startDate'           => optional($vi->start_date)->format('Y-m-d'),
    //                 'endDate'             => optional($vi->end_date)->format('Y-m-d'),
    //                 'total_slots_per_day' => $h->doohScreen->total_slots_per_day ?? 300,
    //                 'source'              => $vi->enquiry_item_id ? 'enquiry' : 'added',
    //             ];
    //         }
    //     } elseif ($request->filled('enquiry_id')) {
    //         $enquiry = Enquiry::with([
    //             'customer', 'items', 'items.hoarding', 'items.hoarding.vendor',
    //             'items.hoarding.doohScreen', 'items.package',
    //         ])->findOrFail($request->input('enquiry_id'));

    //         $enquiry->getEnquiryDetails();

    //         $existing = Offer::where('enquiry_id', $enquiry->id)
    //             ->where('vendor_id', Auth::id())
    //             ->whereNull('archived_at')
    //             ->whereNotIn('status', [Offer::STATUS_CANCELLED])
    //             ->latest('id')
    //             ->first();

    //         if ($existing) {
    //             return redirect()->route('vendor.offers.create', ['offer_id' => $existing->id]);
    //         }
    //     }

    //     return view('vendor.offers.create', compact('enquiry', 'editingOffer', 'seedFromOffer'));
    // }
// app/Http/Controllers/Web/Vendor/OfferController.php — create()

public function create(Request $request)
{
    $enquiry = null;
    $editingOffer = null;
    $seedFromOffer = [];

    if ($request->filled('offer_id')) {
        $editingOffer = Offer::with(['enquiry.customer', 'currentVersion.items.hoarding.doohScreen'])
            ->where('vendor_id', Auth::id())
            ->findOrFail($request->input('offer_id'));

        // FIX: previously nothing stopped a vendor from opening the modify
        // screen for an already-accepted/rejected offer via a stale link —
        // the page rendered fine and, on submit, store() would silently spin
        // up a brand-new version on a "closed" offer.
        if (!$editingOffer->isNegotiable()) {
            return redirect()->route('vendor.offers.show', $editingOffer->id)
                ->with('error', 'This offer has already been ' . $editingOffer->status . ' and can no longer be modified.');
        }

        $enquiry = $editingOffer->enquiry()->with([
            'customer', 'items', 'items.hoarding', 'items.hoarding.vendor',
            'items.hoarding.doohScreen', 'items.package',
        ])->first();
        $enquiry->getEnquiryDetails();

        foreach ($editingOffer->currentVersion->items as $vi) {
            $h = $vi->hoarding;
            if (!$h) continue;
            $seedFromOffer[] = [
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
    } elseif ($request->filled('enquiry_id')) {
        $enquiry = Enquiry::with([
            'customer', 'items', 'items.hoarding', 'items.hoarding.vendor',
            'items.hoarding.doohScreen', 'items.package',
        ])->findOrFail($request->input('enquiry_id'));

        $enquiry->getEnquiryDetails();

        $existing = Offer::where('enquiry_id', $enquiry->id)
            ->where('vendor_id', Auth::id())
            ->whereNull('archived_at')
            ->whereNotIn('status', [Offer::STATUS_CANCELLED])
            ->latest('id')
            ->first();

        if ($existing) {
            // FIX: same guard on the auto-redirect path — don't bounce into
            // a modify screen for an offer that's already closed.
            if (!$existing->isNegotiable()) {
                return redirect()->route('vendor.offers.show', $existing->id)
                    ->with('error', 'This offer has already been ' . $existing->status . '. Create a new offer if you want to make a fresh proposal.');
            }
            return redirect()->route('vendor.offers.create', ['offer_id' => $existing->id]);
        }
    }

    return view('vendor.offers.create', compact('enquiry', 'editingOffer', 'seedFromOffer'));
}

    public function store(Request $request): JsonResponse
{
    $vendor = Auth::user();

    $validated = $request->validate([
        'enquiry_id'                => 'required|exists:enquiries,id',
        'offer_id'                  => 'nullable|integer|exists:offers,id',
        'price_type'                => 'nullable|in:total,monthly,weekly,daily',
        'description'                => 'nullable|string|max:1000',
        'valid_until'                => 'nullable|date|after_or_equal:today',
        'send_email'                 => 'nullable|boolean',
        'send_whatsapp'              => 'nullable|boolean',
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
    if (!$request->boolean('send_email') && !$request->boolean('send_whatsapp')) {
            return response()->json(['success' => false, 'message' => 'Please select at least one sending option (Email or WhatsApp).'], 422);
        }
        $existingOffer = null;
        if (!empty($validated['offer_id'])) {
            $existingOffer = Offer::where('id', $validated['offer_id'])->where('vendor_id', $vendor->id)->first();

            // FIX: if a modify-URL was hit right as the customer accepted/rejected,
            // this stops the vendor's submit from reopening a closed offer.
            if ($existingOffer && !$existingOffer->isNegotiable()) {
                return response()->json(['success' => false, 'message' => 'This offer has already been ' . $existingOffer->status . ' and can no longer be modified.'], 422);
            }
        } else {
            $existingOffer = Offer::where('enquiry_id', $validated['enquiry_id'])
                ->where('vendor_id', $vendor->id)
                ->whereNull('archived_at')
                ->whereNotIn('status', [Offer::STATUS_CANCELLED])
                ->latest('id')
                ->first();
        }

        $failure = $this->bookingService->validateItems($validated['items'], $vendor->id);
        if ($failure) {
            return response()->json(['success' => false, 'message' => $failure['message'], 'unavailable_hoardings' => $failure['unavailable_hoardings'] ?? null], $failure['status']);
        }

        try {
            $offer = $this->bookingService->createOrModifyByVendor($validated, $vendor);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }

        return response()->json([
            'success' => true,
            'message' => $offer->wasRecentlyCreated ? 'Offer created and sent successfully' : 'Offer modified and sent successfully',
            'data'    => [
                'id'                => $offer->id,
                'offer_number'      => $offer->offer_number,
                'redirect'          => route('vendor.offers.show', $offer->id),
                'manage_offers_url' => route('vendor.offers.index'),
            ],
        ], 201);
    }


    // if (!$request->boolean('send_email') && !$request->boolean('send_whatsapp')) {
    //     return response()->json(['success' => false, 'message' => 'Please select at least one sending option (Email or WhatsApp).'], 422);
    // }

    // // ── Idempotency guard ──────────────────────────────────────────────
    // // A double-click (or a JS bug that fires the submit handler twice) sends two
    // // near-simultaneous POSTs for the same enquiry. Without this lock, request #2
    // // can read the offer request #1 just created and treat it as a "modify",
    // // spawning a spurious version 2 on what the vendor experiences as one click.
    // // Serializing on vendor+enquiry makes that race structurally impossible.
    // $lockKey = "offer-store:{$vendor->id}:{$validated['enquiry_id']}";
    // $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 15);

    // if (!$lock->get()) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'This offer is already being submitted. Please wait a moment and refresh Manage Offers.',
    //     ], 409);
    // }

    // try {
    //     $enquiry = Enquiry::with('customer')->findOrFail($validated['enquiry_id']);

    //     $hoardingIds = collect($validated['items'])->pluck('hoarding_id')->unique()->values();
    //     $ownedCount  = Hoarding::whereIn('id', $hoardingIds)->where('vendor_id', $vendor->id)->count();
    //     if ($ownedCount !== $hoardingIds->count()) {
    //         return response()->json(['success' => false, 'message' => 'One or more selected hoardings do not belong to you.'], 403);
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

    //     $existingOffer = null;
    //     if (!empty($validated['offer_id'])) {
    //         $existingOffer = Offer::where('id', $validated['offer_id'])->where('vendor_id', $vendor->id)->first();
    //     } else {
    //         $existingOffer = Offer::where('enquiry_id', $enquiry->id)
    //             ->where('vendor_id', $vendor->id)
    //             ->whereNull('archived_at')
    //             ->whereNotIn('status', [Offer::STATUS_CANCELLED])
    //             ->latest('id')
    //             ->first();
    //     }

    //     $version = null;
    //     $offer = DB::transaction(function () use ($validated, $vendor, $enquiry, $existingOffer, &$version) {
    //         $subtotal = collect($validated['items'])->sum('unit_price');
    //         $discount = collect($validated['items'])->sum(fn ($i) => $i['discount_amount'] ?? 0);
    //         $total    = collect($validated['items'])->sum('final_price');

    //         if ($existingOffer) {
    //             $offer = $existingOffer;
    //             $nextVersionNumber = ($offer->getLatestVersion()?->version_number ?? 0) + 1;

    //             $version = OfferVersion::create([
    //                 'offer_id'        => $offer->id,
    //                 'version_number'  => $nextVersionNumber,
    //                 'created_by'      => $vendor->id,
    //                 'created_by_type' => 'vendor',
    //                 'status'          => 'draft',
    //                 'subtotal'        => $subtotal,
    //                 'discount_amount' => $discount,
    //                 'tax_amount'      => 0,
    //                 'total_amount'    => $total,
    //             ]);

    //             $offer->update([
    //                 'current_version_id' => $version->id,
    //                 'price'               => $total,
    //                 'price_type'          => $validated['price_type'] ?? $offer->price_type,
    //                 'price_snapshot'      => ['items' => $validated['items']],
    //                 'description'         => $validated['description'] ?? $offer->description,
    //                 'valid_until'         => $validated['valid_until'] ?? $offer->valid_until,
    //                 'version'             => $nextVersionNumber,
    //                 'status'              => Offer::STATUS_DRAFT,
    //                 'accepted_at'         => null,
    //                 'rejected_at'         => null,
    //                 'modification_notes'  => null,
    //             ]);

    //             OfferActivityLog::record($offer, 'modified', "Offer modified — version {$nextVersionNumber} created");
    //         } else {
    //             $offer = Offer::create([
    //                 'offer_number'   => 'OFR-' . strtoupper(uniqid()),
    //                 'enquiry_id'     => $enquiry->id,
    //                 'vendor_id'      => $vendor->id,
    //                 'customer_id'    => $enquiry->customer_id,
    //                 'price'          => $total,
    //                 'price_type'     => $validated['price_type'] ?? 'total',
    //                 'price_snapshot' => ['items' => $validated['items']],
    //                 'description'    => $validated['description'] ?? null,
    //                 'valid_until'    => $validated['valid_until'] ?? null,
    //                 'status'         => Offer::STATUS_DRAFT,
    //                 'version'        => 1,
    //             ]);

    //             $version = OfferVersion::create([
    //                 'offer_id'        => $offer->id,
    //                 'version_number'  => 1,
    //                 'created_by'      => $vendor->id,
    //                 'created_by_type' => 'vendor',
    //                 'status'          => 'draft',
    //                 'subtotal'        => $subtotal,
    //                 'discount_amount' => $discount,
    //                 'tax_amount'      => 0,
    //                 'total_amount'    => $total,
    //             ]);

    //             $offer->update(['current_version_id' => $version->id]);
    //             OfferActivityLog::record($offer, 'created', 'Offer created');
    //         }

    //         foreach ($validated['items'] as $item) {
    //             $start = Carbon::parse($item['start_date']);
    //             $end   = Carbon::parse($item['end_date']);

    //             OfferVersionItem::create([
    //                 'offer_version_id' => $version->id,
    //                 'enquiry_item_id'  => $item['enquiry_item_id'] ?? null,
    //                 'hoarding_id'      => $item['hoarding_id'],
    //                 'hoarding_type'    => $item['hoarding_type'],
    //                 'start_date'       => $start,
    //                 'end_date'         => $end,
    //                 'duration_months'  => max(1, (int) ceil(($end->diffInDays($start) + 1) / 30)),
    //                 'unit_price'       => $item['unit_price'],
    //                 'discount_amount'  => $item['discount_amount'] ?? 0,
    //                 'tax_amount'       => 0,
    //                 'final_price'      => $item['final_price'],
    //                 'meta'             => ['source' => !empty($item['enquiry_item_id']) ? 'enquiry' : 'added'],
    //             ]);
    //         }

    //         $enquiryItemIds = collect($validated['items'])->pluck('enquiry_item_id')->filter();
    //         if ($enquiryItemIds->isNotEmpty()) {
    //             EnquiryItem::whereIn('id', $enquiryItemIds)->update(['status' => 'offer_send']);
    //         }

    //         return $offer;
    //     });

    //     try {
    //         if ($request->boolean('send_email') && $enquiry->customer?->email) {
    //             \Mail::to($enquiry->customer->email)->queue(new \App\Mail\OfferSentMail($offer->fresh(['currentVersion.items.hoarding.doohScreen', 'customer', 'vendor'])));
    //         }
    //         if ($request->boolean('send_whatsapp') && $enquiry->contact_number) {
    //             $whatsapp = app(\App\Services\Whatsapp\TwilioWhatsappService::class);
    //             $phone = preg_replace('/\D+/', '', $enquiry->contact_number);
    //             if (!str_starts_with($phone, '91')) $phone = '91' . ltrim($phone, '0');
    //             $whatsapp->send('+' . $phone, $this->buildOfferWhatsappMessage($offer, $enquiry));
    //         }
    //         $offer->update(['status' => Offer::STATUS_SENT, 'sent_at' => now()]);
    //         OfferActivityLog::record($offer, 'sent', 'Offer sent to customer', [
    //             'email' => $request->boolean('send_email'), 'whatsapp' => $request->boolean('send_whatsapp'),
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::warning('Offer send failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => $existingOffer ? 'Offer modified and sent successfully' : 'Offer created and sent successfully',
    //         'data'    => [
    //             'id'                => $offer->id,
    //             'offer_number'      => $offer->offer_number,
    //             'redirect'          => route('vendor.offers.show', $offer->id),
    //             'manage_offers_url' => route('vendor.offers.index'),
    //         ],
    //     ], 201);
    // } finally {
    //     $lock->release();
    // }
//}

    // public function show(Offer $offer): jsonResponse
    // {
    //   //  dd($offer->load('currentVersion.items.hoarding', 'customer', 'activityLogs'));
    //     abort_unless($offer->vendor_id === Auth::id(), 403);
    //     $offer->load('currentVersion.items.hoarding', 'customer', 'activityLogs');
    //     return view('vendor.offers.show', compact('offer'));
    // }
    // app/Http/Controllers/Web/Vendor/OfferController.php

// public function show(Offer $offer)
// {
//     abort_unless($offer->vendor_id === Auth::id(), 403);

//     $offer->load([
//         'currentVersion.items.hoarding.doohScreen',
//         'customer',
//         'activityLogs.actor',
//     ]);

//     $versionDiffs = $this->buildVersionDiffs($offer);

//     return view('vendor.offers.show', compact('offer', 'versionDiffs'));
// }
  public function show(Offer $offer)
    {
        abort_unless($offer->vendor_id === Auth::id(), 403);

        $offer->load([
            'currentVersion.items.hoarding.doohScreen',
            'customer',
            'activityLogs.actor',
        ]);

        $versionDiffs = $this->diffService->build($offer);

 return view('vendor.offers.show', compact('offer', 'versionDiffs') + ['isVendorView' => true]);
    }


/**
 * Walks every OfferVersion in order and diffs its hoarding set against the
 * previous version, so the UI can show exactly which hoardings were added
 * or removed at each step, and who (vendor/customer) made that change.
 */
// app/Http/Controllers/Web/Vendor/OfferController.php

protected function buildVersionDiffs(Offer $offer): array
{
    $versions = $offer->versions()
        ->with('items.hoarding.doohScreen')
        ->orderBy('version_number')
        ->get();

    $diffs = [];
    $previousItems = collect(); // hoarding_id => OfferVersionItem, from the previous version

    foreach ($versions as $version) {
        $currentItems = $version->items->keyBy('hoarding_id');

        // FIX: force plain arrays of ids before handing them to ->only()/->except().
        // $currentItems is an Eloquent Collection, so ->keys()/->diff() on it return
        // *Eloquent* Collections of scalar ids (not models) — passing one of those
        // straight into ->only() makes Eloquent Collection think it received a
        // collection of models and call ->modelKeys() on plain integers, which
        // blows up. ->all() sidesteps the ambiguity entirely.
        $addedIds   = $currentItems->keys()->diff($previousItems->keys())->all();
        $removedIds = $previousItems->keys()->diff($currentItems->keys())->all();

        $changedIds = $currentItems->keys()
            ->intersect($previousItems->keys())
            ->filter(function ($id) use ($currentItems, $previousItems) {
                $cur = $currentItems->get($id);
                $prev = $previousItems->get($id);
                return $cur->start_date?->format('Y-m-d') !== $prev->start_date?->format('Y-m-d')
                    || $cur->end_date?->format('Y-m-d') !== $prev->end_date?->format('Y-m-d')
                    || (float) $cur->final_price !== (float) $prev->final_price;
            })
            ->all();

        $diffs[] = [
            'version'      => $version,
            'is_initial'   => $version->version_number === 1,
            'actor_type'   => $version->created_by_type,
            'added'        => $currentItems->only($addedIds)->values(),
            'removed'      => $previousItems->only($removedIds)->values(),
            'changed'      => $currentItems->only($changedIds)->map(function ($item) use ($previousItems) {
                return ['current' => $item, 'previous' => $previousItems->get($item->hoarding_id)];
            })->values(),
            'total_amount' => $version->total_amount,
        ];

        $previousItems = $currentItems;
    }

    return $diffs;
}

/**
 * Vendor declines to continue negotiating this offer — distinct from
 * customer rejection, but uses the same terminal status.
 */
public function vendorReject(Request $request, Offer $offer): JsonResponse
{
    abort_unless($offer->vendor_id === Auth::id(), 403);

    if (!in_array($offer->status, [Offer::STATUS_DRAFT, Offer::STATUS_SENT], true)) {
        return response()->json(['success' => false, 'message' => 'This offer can no longer be rejected.'], 422);
    }

    $validated = $request->validate(['reason' => 'nullable|string|max:500']);

    $offer->update(['status' => Offer::STATUS_REJECTED, 'rejected_at' => now()]);
    OfferActivityLog::record($offer, 'rejected_by_vendor', 'Offer rejected by vendor', ['reason' => $validated['reason'] ?? null]);

    try {
        if ($offer->customer?->email) {
            \Mail::to($offer->customer->email)->queue(new \App\Mail\OfferRejectedByVendorMail($offer, $validated['reason'] ?? null));
        }
    } catch (\Exception $e) {
        Log::warning('Vendor-reject notification failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
    }
       if ($offer->customer) {
        $offer->customer->notify(new OfferRejectedByVendorNotification($offer->fresh(['customer', 'vendor']), $validated['reason'] ?? null));
    }

    return response()->json(['success' => true, 'message' => 'Offer rejected']);
}

    public function archive(Offer $offer): JsonResponse
    {
        abort_unless($offer->vendor_id === Auth::id(), 403);
        $offer->archive();
        OfferActivityLog::record($offer, 'archived', 'Offer archived by vendor');
        return response()->json(['success' => true]);
    }

    public function unarchive(Offer $offer): JsonResponse
    {
        abort_unless($offer->vendor_id === Auth::id(), 403);
        $offer->unarchive();
        OfferActivityLog::record($offer, 'unarchived', 'Offer restored from archive');
        return response()->json(['success' => true]);
    }

    // public function sendReminder(Offer $offer): JsonResponse
    // {
    //     abort_unless($offer->vendor_id === Auth::id(), 403);
    //     try {
    //         if ($offer->customer?->email) {
    //             \Mail::to($offer->customer->email)->queue(new \App\Mail\OfferSentMail($offer->fresh(['currentVersion.items.hoarding.doohScreen', 'customer', 'vendor'])));
    //         }
    //         OfferActivityLog::record($offer, 'reminder_sent', 'Reminder sent to customer');
    //          if ($offer->customer) {
    //            $fresh = $offer->fresh(['currentVersion.items.hoarding.doohScreen', 'customer', 'vendor']);
    //         $offer->customer->notify(new OfferReminderNotification($fresh));
    //     }

    //         return response()->json(['success' => true, 'message' => 'Reminder sent successfully']);
    //     } catch (\Exception $e) {
    //         Log::warning('Offer reminder failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Failed to send reminder'], 500);
    //     }
    // }
    public function sendReminder(Offer $offer): JsonResponse
{
    abort_unless($offer->vendor_id === Auth::id(), 403);
    try {
        $fresh = $offer->fresh(['currentVersion.items.hoarding.doohScreen', 'customer', 'vendor']);

        if ($offer->customer?->email) {
            // A reminder is neither a fresh "New Offer" nor really a
            // "modification" — reuse OfferSentMail's layout since nothing
            // actually changed, it's just being resurfaced.
            \Mail::to($offer->customer->email)->queue(new \App\Mail\OfferSentMail($fresh));
        }
        OfferActivityLog::record($offer, 'reminder_sent', 'Reminder sent to customer');

        if ($offer->customer) {
            $offer->customer->notify(new \App\Notifications\Offers\OfferReminderNotification($fresh));
        }

        return response()->json(['success' => true, 'message' => 'Reminder sent successfully']);
    } catch (\Exception $e) {
        Log::warning('Offer reminder failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Failed to send reminder'], 500);
    }
}

    protected function buildOfferWhatsappMessage(Offer $offer, $enquiry): string
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
    // app/Http/Controllers/Web/Vendor/OfferController.php — add this method

public function acceptCustomerModification(Offer $offer): JsonResponse
{
    abort_unless($offer->vendor_id === Auth::id(), 403);

    if ($offer->status !== Offer::STATUS_SENT || !$offer->wasLastModifiedByCustomer()) {
        return response()->json(['success' => false, 'message' => 'There is no pending customer modification to accept on this offer.'], 422);
    }

    $offer->update(['status' => Offer::STATUS_ACCEPTED, 'accepted_at' => now()]);
    OfferActivityLog::record($offer, 'accepted', "Vendor accepted the customer's modified offer (version {$offer->version})");

    Offer::where('enquiry_id', $offer->enquiry_id)
        ->where('id', '!=', $offer->id)
        ->where('status', Offer::STATUS_SENT)
        ->get()
        ->each(function ($sibling) {
            $sibling->update(['status' => Offer::STATUS_REJECTED, 'rejected_at' => now()]);
            OfferActivityLog::record($sibling, 'auto_rejected', 'Auto-rejected: vendor accepted a different offer for this enquiry');
        });

    try {
        if ($offer->customer?->email) {
            \Mail::to($offer->customer->email)->queue(new \App\Mail\OfferAcceptedByVendorMail($offer->fresh(['currentVersion.items.hoarding', 'customer', 'vendor'])));
        }
    } catch (\Exception $e) {
        Log::warning('Vendor-accept notification failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
    }
     if ($offer->customer) {
        $fresh = $offer->fresh(['currentVersion.items.hoarding', 'customer', 'vendor']);
        $offer->customer->notify(new OfferAcceptedByVendorNotification($fresh));
    }

    return response()->json(['success' => true, 'message' => 'Offer accepted successfully']);
}
}
