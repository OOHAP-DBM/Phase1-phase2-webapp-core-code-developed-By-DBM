<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Resources\OfferListResource;
use App\Models\Offer;
use App\Models\Hoarding;
use App\Models\OfferActivityLog;
use App\Services\OfferBookingService;
use App\Services\OfferVersionDiffService;
use App\Http\Controllers\Api\Vendor\OfferController as VendorApiOfferController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(name="Customer Offers", description="Endpoints for customers to view, accept, reject, or modify offers received from vendors")
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
     *     path="/offers/customer",
     *     tags={"Customer Offers"},
     *     summary="List offers received by the authenticated customer",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string"), description="Search by offer number or vendor name/email/phone"),
     *     @OA\Parameter(name="status[]", in="query", @OA\Schema(type="array", @OA\Items(type="string", enum={"all","draft","sent","accepted","rejected","expired"}))),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Response(response=200, description="Paginated list of offers",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OfferListItem")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
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
     *     path="/offers/customer/{offer}",
     *     tags={"Customer Offers"},
     *     summary="Get a single offer with its current version, items, and full negotiation history",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Offer detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="offer", ref="#/components/schemas/Offer"),
     *                 @OA\Property(property="version_history", type="array", @OA\Items(ref="#/components/schemas/OfferVersionDiff")),
     *                 @OA\Property(property="can_accept", type="boolean", description="True when it's the customer's turn to accept/modify/reject"),
     *                 @OA\Property(property="awaiting_vendor", type="boolean", description="True when the customer already submitted changes and it's now the vendor's turn")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Offer does not belong to the authenticated customer")
     * )
     */
    public function show(Offer $offer): JsonResponse
    {
        abort_unless($offer->customer_id === Auth::id(), 403);

        $offer->load(['currentVersion.items.hoarding.doohScreen', 'vendor', 'activityLogs.actor']);

        return response()->json([
            'success' => true,
            'data' => [
                'offer' => new OfferResource($offer),
                'version_history' => app(VendorApiOfferController::class)->formatDiffsPublic($this->diffService->build($offer)),
                'can_accept'      => $offer->canAccept() && $offer->wasLastModifiedByVendor(),
                'awaiting_vendor' => $offer->canAccept() && $offer->wasLastModifiedByCustomer(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/offers/customer/{offer}/accept",
     *     tags={"Customer Offers"},
     *     summary="Accept an offer; auto-rejects any other sent offers for the same enquiry",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Accepted", @OA\JsonContent(@OA\Property(property="success", type="boolean"), @OA\Property(property="message", type="string"))),
     *     @OA\Response(response=422, description="Offer can no longer be accepted"),
     *     @OA\Response(response=403, description="Not this customer's offer")
     * )
     */
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

        return response()->json(['success' => true, 'message' => 'Offer accepted successfully']);
    }

    /**
     * @OA\Post(
     *     path="/offers/customer/{offer}/reject",
     *     tags={"Customer Offers"},
     *     summary="Reject an offer, ending the negotiation",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(@OA\Property(property="reason", type="string", nullable=true, maxLength=500))),
     *     @OA\Response(response=200, description="Rejected", @OA\JsonContent(@OA\Property(property="success", type="boolean"), @OA\Property(property="message", type="string"))),
     *     @OA\Response(response=403, description="Not this customer's offer")
     * )
     */
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

        return response()->json(['success' => true, 'message' => 'Offer rejected']);
    }

    /**
     * @OA\Get(
     *     path="/offers/customer/{offer}/modify-context",
     *     tags={"Customer Offers"},
     *     summary="Get seed data (vendor details + current items) to render the customer's modify builder",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Seed data",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="vendor", type="object"),
     *                 @OA\Property(property="seed_items", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Offer is not currently open for modification"),
     *     @OA\Response(response=403, description="Not this customer's offer")
     * )
     */
    public function modifyContext(Offer $offer): JsonResponse
    {
        abort_unless($offer->customer_id === Auth::id(), 403);

        if (!$offer->canAccept() || !$offer->wasLastModifiedByVendor()) {
            return response()->json(['success' => false, 'message' => 'This offer is not currently open for modification.'], 422);
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
                'hoarding_type'       => $vi->hoarding_type,
                'price_per_month'     => (float) ($vi->unit_price / max(1, $vi->duration_months)),
                'start_date'          => optional($vi->start_date)->format('Y-m-d'),
                'end_date'            => optional($vi->end_date)->format('Y-m-d'),
                'total_slots_per_day' => $h->doohScreen->total_slots_per_day ?? 300,
                'source'              => $vi->enquiry_item_id ? 'enquiry' : 'added',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'vendor' => ['name' => $offer->vendor->name, 'email' => $offer->vendor->email, 'phone' => $offer->vendor->phone],
                'seed_items' => $seed,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/offers/customer/{offer}/hoardings",
     *     tags={"Customer Offers"},
     *     summary="Browse the offer's vendor's active hoarding catalogue, for adding hoardings during a modification",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", default=12)),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", default=1)),
     *     @OA\Response(response=200, description="Paginated hoarding list",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Not this customer's offer"),
     *     @OA\Response(response=500, description="Failed to fetch hoardings")
     * )
     */
    public function getHoardings(Request $request, Offer $offer): JsonResponse
    {
        abort_unless($offer->customer_id === Auth::id(), 403);

        try {
            $query = Hoarding::query()->where('vendor_id', $offer->vendor_id)->where('status', 'active');

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
                    'hero_image'          => $hoarding->heroImage(),
                    'city'                => $hoarding->city,
                    'address'             => $hoarding->address,
                    'type'                => $hoarding->hoarding_type,
                    'price_per_month'     => $pricePerMonth,
                    'total_slots_per_day' => $hoarding->doohScreen->total_slots_per_day ?? 300,
                ];
            });

            return response()->json([
                'success'      => true,
                'data'         => $data,
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('API: error fetching vendor hoardings for customer modify', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to fetch hoardings'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/offers/customer/{offer}/modify",
     *     tags={"Customer Offers"},
     *     summary="Customer submits their own modified version of an open offer",
     *     description="Only valid when the offer is still open AND the current version was last submitted by the vendor. Creates a new version tagged created_by_type=customer; the vendor then sees an 'Accept Customer's Offer' action.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="offer", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/OfferModifyRequest")),
     *     @OA\Response(response=201, description="Modification sent to vendor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Offer")
     *         )
     *     ),
     *     @OA\Response(response=403, description="One or more hoardings do not belong to the offer's vendor, or offer isn't this customer's"),
     *     @OA\Response(response=409, description="Offer is already being updated concurrently"),
     *     @OA\Response(response=422, description="Offer not open for modification, or hoardings unavailable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="unavailable_hoardings", type="array", @OA\Items(ref="#/components/schemas/UnavailableHoarding"))
     *         )
     *     )
     * )
     */
    public function storeModification(Request $request, Offer $offer): JsonResponse
    {
        abort_unless($offer->customer_id === Auth::id(), 403);

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

        $offer->load(['currentVersion.items.hoarding', 'vendor']);

        return response()->json([
            'success' => true,
            'message' => 'Your changes have been sent to the vendor',
            'data'    => new OfferResource($offer),
        ], 201);
    }
}
