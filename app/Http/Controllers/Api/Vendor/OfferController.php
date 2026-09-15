<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Resources\OfferListResource;
use App\Http\Resources\OfferVersionItemResource;
use App\Models\Offer;
use App\Models\OfferActivityLog;
use App\Services\OfferBookingService;
use App\Services\OfferVersionDiffService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(name="Vendor Offers", description="Endpoints for vendors to create, send, modify, and manage offers sent to customers")
 */
class OfferController extends Controller
{
    protected OfferBookingService $bookingService;
    protected OfferVersionDiffService $diffService;

    public function __construct(OfferBookingService $bookingService, OfferVersionDiffService $diffService)
    {
        $this->bookingService = $bookingService;
        $this->diffService = $diffService;
    }

    /**
     * @OA\Get(
     *     path="/offers/vendor",
     *     tags={"Vendor Offers"},
     *     summary="List the authenticated vendor's offers",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string"), description="Search by offer number or customer name/email/phone"),
     *     @OA\Parameter(name="status[]", in="query", @OA\Schema(type="array", @OA\Items(type="string", enum={"all","draft","sent","accepted","rejected","expired"}))),
     *     @OA\Parameter(name="archived", in="query", @OA\Schema(type="boolean"), description="Return archived offers instead of active ones"),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Response(response=200, description="Paginated list of offers",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OfferListItem")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="per_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): JsonResponse
    {

        $vendor = Auth::user();

        $query = Offer::with(['customer', 'currentVersion.items.hoarding'])
            ->where('vendor_id', $vendor->id);
           // ->notArchived();

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

        if ($request->boolean('archived')) {
            $query = Offer::with(['customer', 'currentVersion.items.hoarding'])
                ->where('vendor_id', $vendor->id)
                ->archived();
        }

        $perPage = (int) $request->get('per_page', 15);
        $offers = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => OfferListResource::collection($offers),
            'meta'    => [
                'current_page' => $offers->currentPage(),
                'last_page'    => $offers->lastPage(),
                'total'        => $offers->total(),
                'per_page'     => $offers->perPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/offers/vendor/{offer}",
     *     tags={"Vendor Offers"},
     *     summary="Get a single offer with its current version, items, and full negotiation history",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Offer detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="offer", ref="#/components/schemas/Offer"),
     *                 @OA\Property(property="version_history", type="array", @OA\Items(ref="#/components/schemas/OfferVersionDiff"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Offer does not belong to the authenticated vendor"),
     *     @OA\Response(response=404, description="Offer not found")
     * )
     */
    public function show(Offer $offer): JsonResponse
    {
        abort_unless($offer->vendor_id === Auth::id(), 403);

        $offer->load(['currentVersion.items.hoarding.doohScreen', 'customer', 'activityLogs.actor']);

        return response()->json([
            'success' => true,
            'data' => [
                'offer' => new OfferResource($offer),
                'version_history' => $this->formatDiffs($this->diffService->build($offer)),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/offers/vendor",
     *     tags={"Vendor Offers"},
     *     summary="Create a new offer, or add a new version to an existing one for the same enquiry",
     *     description="Pass `offer_id` to explicitly target an existing offer for a new version (modify flow). Omitting it auto-detects any existing non-archived offer already on that enquiry and versions it instead of creating a duplicate.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/OfferStoreRequest")),
     *     @OA\Response(response=201, description="Offer created/modified and sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Offer created and sent successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Offer")
     *         )
     *     ),
     *     @OA\Response(response=403, description="One or more hoardings do not belong to this vendor"),
     *     @OA\Response(response=409, description="This offer is already being submitted (concurrent-submit lock)"),
     *     @OA\Response(response=422, description="Validation failed, or hoardings unavailable for the selected dates",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="unavailable_hoardings", type="array", @OA\Items(ref="#/components/schemas/UnavailableHoarding"))
     *         )
     *     )
     * )
     */
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

        $failure = $this->bookingService->validateItems($validated['items'], $vendor->id);
        if ($failure) {
            return response()->json(['success' => false, 'message' => $failure['message'], 'unavailable_hoardings' => $failure['unavailable_hoardings'] ?? null], $failure['status']);
        }

        try {
            $offer = $this->bookingService->createOrModifyByVendor($validated, $vendor);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }

        $offer->load(['currentVersion.items.hoarding', 'customer']);

        return response()->json([
            'success' => true,
            'message' => $offer->wasRecentlyCreated ? 'Offer created and sent successfully' : 'Offer modified and sent successfully',
            'data'    => new OfferResource($offer),
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/offers/vendor/{offer}/archive",
     *     tags={"Vendor Offers"},
     *     summary="Archive an offer (removes it from the active Manage Offers list)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Archived", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true))),
     *     @OA\Response(response=403, description="Not this vendor's offer")
     * )
     */
    public function archive(Offer $offer): JsonResponse
    {
        abort_unless($offer->vendor_id === Auth::id(), 403);
        $offer->archive();
        OfferActivityLog::record($offer, 'archived', 'Offer archived by vendor');
        return response()->json(['success' => true]);
    }

    /**
     * @OA\Post(
     *     path="/offers/vendor/{offer}/unarchive",
     *     tags={"Vendor Offers"},
     *     summary="Restore an archived offer back to the active list",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Unarchived", @OA\JsonContent(@OA\Property(property="success", type="boolean", example=true))),
     *     @OA\Response(response=403, description="Not this vendor's offer")
     * )
     */
    public function unarchive(Offer $offer): JsonResponse
    {
        abort_unless($offer->vendor_id === Auth::id(), 403);
        $offer->unarchive();
        OfferActivityLog::record($offer, 'unarchived', 'Offer restored from archive');
        return response()->json(['success' => true]);
    }

    /**
     * @OA\Post(
     *     path="/offers/vendor/{offer}/remind",
     *     tags={"Vendor Offers"},
     *     summary="Re-send the offer email to the customer as a reminder",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Reminder sent", @OA\JsonContent(@OA\Property(property="success", type="boolean"), @OA\Property(property="message", type="string"))),
     *     @OA\Response(response=403, description="Not this vendor's offer"),
     *     @OA\Response(response=500, description="Reminder email failed to send")
     * )
     */
    public function sendReminder(Offer $offer): JsonResponse
    {
        abort_unless($offer->vendor_id === Auth::id(), 403);
        try {
            if ($offer->customer?->email) {
                \Mail::to($offer->customer->email)->queue(
                    new \App\Mail\OfferSentMail($offer->fresh(['currentVersion.items.hoarding.doohScreen', 'customer', 'vendor']))
                );
            }
            OfferActivityLog::record($offer, 'reminder_sent', 'Reminder sent to customer');
            return response()->json(['success' => true, 'message' => 'Reminder sent successfully']);
        } catch (\Exception $e) {
            Log::warning('Offer reminder failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to send reminder'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/offers/vendor/{offer}/vendor-reject",
     *     tags={"Vendor Offers"},
     *     summary="Vendor withdraws/rejects an offer, ending the negotiation",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(@OA\Property(property="reason", type="string", nullable=true, maxLength=500))),
     *     @OA\Response(response=200, description="Rejected", @OA\JsonContent(@OA\Property(property="success", type="boolean"), @OA\Property(property="message", type="string"))),
     *     @OA\Response(response=422, description="Offer is not in a rejectable state (already accepted/rejected/expired)"),
     *     @OA\Response(response=403, description="Not this vendor's offer")
     * )
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

        return response()->json(['success' => true, 'message' => 'Offer rejected']);
    }

    /**
     * @OA\Post(
     *     path="/offers/vendor/{offer}/accept-customer-modification",
     *     tags={"Vendor Offers"},
     *     summary="Vendor accepts the customer's most recently submitted modified version",
     *     description="Only valid when the offer's current version was created by the customer (i.e. it's the vendor's turn to respond). Auto-rejects sibling offers on the same enquiry, same as a direct accept.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Accepted", @OA\JsonContent(@OA\Property(property="success", type="boolean"), @OA\Property(property="message", type="string"))),
     *     @OA\Response(response=422, description="No pending customer modification to accept"),
     *     @OA\Response(response=403, description="Not this vendor's offer")
     * )
     */
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
                \Mail::to($offer->customer->email)->queue(
                    new \App\Mail\OfferAcceptedByVendorMail($offer->fresh(['currentVersion.items.hoarding', 'customer', 'vendor']))
                );
            }
        } catch (\Exception $e) {
            Log::warning('Vendor-accept notification failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Offer accepted successfully']);
    }

    /**
     * @OA\Get(
     *     path="/offers/vendor/create-context",
     *     tags={"Vendor Offers"},
     *     summary="Get the seed data needed to render the offer builder (enquiry details, or existing offer's items to modify)",
     *     description="Pass exactly one of `enquiry_id` (new offer) or `offer_id` (modify existing). If `enquiry_id` is passed but an offer already exists for it, the response transparently returns that offer's seed data instead, matching the auto-redirect behavior of the web flow.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="enquiry_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="offer_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Seed data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="enquiry_id", type="integer", nullable=true),
     *                 @OA\Property(property="offer_id", type="integer", nullable=true),
     *                 @OA\Property(property="customer", type="object", nullable=true),
     *                 @OA\Property(property="seed_items", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     )
     * )
     */
    public function createContext(Request $request): JsonResponse
    {
        $vendor = Auth::user();

        $enquiry = null;
        $editingOffer = null;
        $seed = [];

        if ($request->filled('offer_id')) {
            $editingOffer = Offer::with(['enquiry.customer', 'currentVersion.items.hoarding.doohScreen'])
                ->where('vendor_id', $vendor->id)
                ->findOrFail($request->input('offer_id'));

            $enquiry = $editingOffer->enquiry;

            foreach ($editingOffer->currentVersion->items as $vi) {
                $h = $vi->hoarding;
                if (!$h) continue;
                $seed[] = [
                    'hoarding_id'         => $h->id,
                    'enquiry_item_id'     => $vi->enquiry_item_id,
                    'title'               => $h->title ?? $h->address,
                    'city'                => $h->city,
                    'hoarding_type'       => $vi->hoarding_type,
                    'price_per_month'     => (float) ($vi->unit_price / max(1, $vi->duration_months)),
                    'start_date'          => optional($vi->start_date)->format('Y-m-d'),
                    'end_date'            => optional($vi->end_date)->format('Y-m-d'),
                    'total_slots_per_day' => $h->doohScreen->total_slots_per_day ?? 300,
                    'source'              => $vi->enquiry_item_id ? 'enquiry' : 'added',
                ];
            }
        } elseif ($request->filled('enquiry_id')) {
            $enquiry = \Modules\Enquiries\Models\Enquiry::with(['customer', 'items.hoarding.doohScreen'])
                ->findOrFail($request->input('enquiry_id'));

            $existing = Offer::where('enquiry_id', $enquiry->id)
                ->where('vendor_id', $vendor->id)
                ->whereNull('archived_at')
                ->whereNotIn('status', [Offer::STATUS_CANCELLED])
                ->latest('id')
                ->first();

            if ($existing) {
                return $this->createContext(new Request(['offer_id' => $existing->id]));
            }

            foreach ($enquiry->items as $it) {
                $h = $it->hoarding;
                if (!$h) continue;
                $seed[] = [
                    'hoarding_id'         => $h->id,
                    'enquiry_item_id'     => $it->id,
                    'title'               => $h->title ?? $h->address,
                    'city'                => $h->city,
                    'hoarding_type'       => $it->hoarding_type,
                    'price_per_month'     => (float) ($it->meta['pricing_display']['price'] ?? 0),
                    'start_date'          => optional($it->preferred_start_date)->format('Y-m-d'),
                    'end_date'            => optional($it->preferred_end_date)->format('Y-m-d'),
                    'total_slots_per_day' => $h->doohScreen->total_slots_per_day ?? 300,
                    'source'              => 'enquiry',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'enquiry_id'   => $enquiry?->id,
                'offer_id'     => $editingOffer?->id,
                'customer'     => $enquiry?->customer ? [
                    'name' => $enquiry->customer->name, 'email' => $enquiry->customer->email,
                    'phone' => $enquiry->contact_number,
                ] : null,
                'seed_items'   => $seed,
            ],
        ]);
    }

    /**
     * Reusable diff formatter — public wrapper so the customer API controller
     * can share this without duplicating the mapping logic.
     */
    public function formatDiffsPublic(array $diffs): array
    {
        return $this->formatDiffs($diffs);
    }

    protected function formatDiffs(array $diffs): array
    {
        return array_map(function ($d) {
            return [
                'version_number'  => $d['version']->version_number,
                'actor_type'      => $d['actor_type'],
                'created_at'      => $d['version']->created_at->toIso8601String(),
                'total_amount'    => (float) $d['total_amount'],
                'item_count'      => $d['item_count'],
                'is_initial'      => $d['is_initial'],
                'has_any_change'  => $d['has_any_change'],
                'added'           => array_map(fn ($i) => (new OfferVersionItemResource($i))->resolve(), $d['added']),
                'removed'         => array_map(fn ($i) => (new OfferVersionItemResource($i))->resolve(), $d['removed']),
                'changed'         => array_map(fn ($c) => [
                    'current'  => (new OfferVersionItemResource($c['current']))->resolve(),
                    'previous' => (new OfferVersionItemResource($c['previous']))->resolve(),
                ], $d['changed']),
                'unchanged'       => array_map(fn ($i) => (new OfferVersionItemResource($i))->resolve(), $d['unchanged']),
            ];
        }, $diffs);
    }
}

