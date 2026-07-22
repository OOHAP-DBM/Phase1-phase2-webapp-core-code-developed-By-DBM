<?php

namespace Modules\Cart\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cart\Services\CartService;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {
        $this->middleware('auth:sanctum')->except(['list', 'count']);
    }

    /* =====================================================
     | ADD / UPDATE CART
     ===================================================== */
    /**
     * @OA\Post(
     *     path="/cart/add",
     *     tags={"Cart"},
     *     summary="Add or update hoarding in cart with vendor validation",
     *     description="Add a hoarding to the cart or update an existing cart item with package details. Validates that the hoarding belongs to the specified vendor. Cart can only contain hoardings from one vendor at a time.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Cart item details with vendor validation",
     *         @OA\JsonContent(
     *             required={"hoarding_id", "vendor_id"},
     *             @OA\Property(
     *                 property="hoarding_id",
     *                 type="integer",
     *                 description="ID of the hoarding to add",
     *                 example=746
     *             ),
     *             @OA\Property(
     *                 property="vendor_id",
     *                 type="integer",
     *                 description="ID of the vendor who owns this hoarding (must match hoarding's vendor_id)",
     *                 example=5
     *             ),
     *             @OA\Property(
     *                 property="package_id",
     *                 type="integer",
     *                 nullable=true,
     *                 description="ID of the package (optional)",
     *                 example=null
     *             ),
     *             @OA\Property(
     *                 property="package_source",
     *                 type="string",
     *                 nullable=true,
     *                 description="Source of the package (optional)",
     *                 example=null
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hoarding added/updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="added"),
     *             @OA\Property(property="message", type="string", example="Added to cart"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="in_cart", type="boolean", example=true),
     *                 @OA\Property(property="final_price", type="number", format="float", example=43686),
     *                 @OA\Property(property="cart_count", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated - User must be logged in",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="string", example="login_required"),
     *             @OA\Property(property="message", type="string", example="Please login to add item to cart")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed - vendor mismatch, conflict, or invalid data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error_scenarios",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="vendor_mismatch",
     *                         type="object",
     *                         @OA\Property(property="success", type="boolean", example=false),
     *                         @OA\Property(property="status", type="string", example="vendor_mismatch"),
     *                         @OA\Property(property="message", type="string", example="This hoarding does not belong to the specified vendor")
     *                     )
     *                 ),
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="vendor_conflict",
     *                         type="object",
     *                         @OA\Property(property="success", type="boolean", example=false),
     *                         @OA\Property(property="status", type="string", example="vendor_conflict"),
     *                         @OA\Property(property="message", type="string", example="Your cart already contains hoardings from a different vendor. Please remove the existing hoardings before adding hoardings from another vendor.")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Hoarding not found or inactive"
     *     )
     * )
     */
    public function add(Request $request)
    {
        $request->validate([
            'hoarding_id'    => 'required|integer',
            'vendor_id'      => 'required|integer',
            'package_id'     => 'nullable|integer',
            'package_source' => 'nullable|string',
        ]);

        $result = $this->cartService->add(
            $request->hoarding_id,
            $request->vendor_id,
            $request->package_id,
            $request->package_source
        );

        return $this->apiResponse(
            success: true,
            status: $result['status'],
            message: $result['message'],
            data: [
                'in_cart'     => $result['in_cart'],
                'final_price' => $result['final_price'] ?? null,
                'cart_count'  => $this->cartCount(),
            ]
        );
    }

    /* =====================================================
     | REMOVE FROM CART
     ===================================================== */
    /**
     * @OA\Delete(
     *     path="/cart/remove/{hoardingId}",
     *     tags={"Cart"},
     *     summary="Remove hoarding from cart",
     *     description="Remove a hoarding from the user's cart",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="hoardingId",
     *         in="path",
     *         required=true,
     *         description="ID of the hoarding to remove",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hoarding removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="removed"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="in_cart", type="boolean", example=false),
     *                 @OA\Property(property="cart_count", type="integer", example=0)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Hoarding not found in cart")
     * )
     */
    public function remove(int $hoardingId)
    {
        $result = $this->cartService->remove($hoardingId);

        return $this->apiResponse(
            success: true,
            status: $result['status'],
            message: $result['message'],
            data: [
                'in_cart'    => false,
                'cart_count' => $this->cartCount(),
            ]
        );
    }

    /**
     * @OA\Post(
     *     path="/cart/remove-multiple",
     *     tags={"Cart"},
     *     summary="Remove multiple hoardings or clear entire cart",
     *     description="Remove multiple hoardings from cart at once or clear the entire cart by passing 'all'",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Hoarding IDs to remove or 'all' to clear cart",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="hoarding_ids",
     *                 oneOf={
     *                     @OA\Schema(type="array", items=@OA\Items(type="integer"), example={1,2,3}),
     *                     @OA\Schema(type="string", example="all")
     *                 },
     *                 description="Array of hoarding IDs to remove or the string 'all' to clear entire cart"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hoardings removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="removed"),
     *             @OA\Property(property="message", type="string", example="Items removed from cart"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="cart_count", type="integer", example=0)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Invalid input")
     * )
     */
    public function removeMultiple(Request $request)
    {
        $request->validate([
            'hoarding_ids' => 'required|array|min:0',
            'hoarding_ids.*' => 'integer',
        ]);

        $result = $this->cartService->removeMultiple($request->hoarding_ids);

        return $this->apiResponse(
            success: true,
            status: $result['status'],
            message: $result['message'],
            data: [
                'cart_count' => $this->cartCount(),
            ]
        );
    }

    /**
     * @OA\Post(
     *     path="/cart/clear",
     *     tags={"Cart"},
     *     summary="Clear entire cart",
     *     description="Remove all hoardings from the cart",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="cleared"),
     *             @OA\Property(property="message", type="string", example="Cart cleared successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="cart_count", type="integer", example=0)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function clear()
    {
        $result = $this->cartService->removeMultiple('all');

        return $this->apiResponse(
            success: true,
            status: $result['status'],
            message: $result['message'],
            data: [
                'cart_count' => $this->cartCount(),
            ]
        );
    }

    /* =====================================================
     | COUNT — logged in + guest dono
     ===================================================== */
    /**
     * @OA\Get(
     *     path="/cart/count",
     *     tags={"Cart"},
     *     summary="Get cart item count",
     *     description="Get total number of items in cart. Works for both authenticated and guest users.",
     *     @OA\Parameter(
     *         name="ids",
     *         in="query",
     *         required=false,
     *         description="Comma-separated hoarding IDs for guest cart (e.g., '20,22,25')",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart count retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="cart_count", type="integer", example=3)
     *             )
     *         )
     *     )
     * )
     */
    public function count()
    {
        $user = $this->resolveUser();

        if ($user) {
            Auth::setUser($user);
            $count = count($this->cartService->getCartHoardingIds());
        } else {
            $count = count($this->guestIds());
        }

        return $this->apiResponse(
            success: true,
            status: 'ok',
            data: ['cart_count' => $count]
        );
    }

    /* =====================================================
     | LIST — logged in + guest dono
     ===================================================== */
    /**
     * @OA\Get(
     *     path="/cart/list",
     *     tags={"Cart"},
     *     summary="Get cart items with details",
     *     description="Retrieve detailed information about all items in the cart. Works for both authenticated and guest users.",
     *     @OA\Parameter(
     *         name="ids",
     *         in="query",
     *         required=false,
     *         description="Comma-separated hoarding IDs for guest cart (e.g., '20,22,25')",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart items retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="string", example="ok"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     description="Array of hoarding items in cart",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="cart_id", type="integer", example=593),
     *                         @OA\Property(property="package_id", type="integer", nullable=true, example=null),
     *                         @OA\Property(property="hoarding_id", type="integer", example=746),
     *                         @OA\Property(property="vendor_id", type="integer", example=5, description="ID of the vendor who owns this hoarding"),
     *                         @OA\Property(property="title", type="string", example="Santiyas Les Gostingsas, Lucknoa - 858 x 380 sqft | OOH Advertising"),
     *                         @OA\Property(property="slug", type="string", example="santiyas-les-gostingsas-santies-9746"),
     *                         @OA\Property(property="city", type="string", example="Lucknoa"),
     *                         @OA\Property(property="state", type="string", example="Utter Pradesh"),
     *                         @OA\Property(property="locality", type="string", example="Santiyas"),
     *                         @OA\Property(property="category", type="string", example="entities"),
     *                         @OA\Property(property="hoarding_type", type="string", example="ooh"),
     *                         @OA\Property(property="monthly_price", type="number", format="float", example=43686),
     *                         @OA\Property(property="base_monthly_price", type="number", format="float", example=48889),
     *                         @OA\Property(property="grace_period_days", type="integer", example=0),
     *                         @OA\Property(
     *                             property="media",
     *                             type="array",
     *                             description="Media files associated with the hoarding",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=640),
     *                                 @OA\Property(property="model_type", type="string", example="App\\Models\\Hoarding"),
     *                                 @OA\Property(property="model_id", type="integer", example=746),
     *                                 @OA\Property(property="uuid", type="string", format="uuid"),
     *                                 @OA\Property(property="collection_name", type="string", example="hoarding_images"),
     *                                 @OA\Property(property="name", type="string"),
     *                                 @OA\Property(property="file_name", type="string"),
     *                                 @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                                 @OA\Property(property="disk", type="string", example="public"),
     *                                 @OA\Property(property="conversions_disk", type="string", example="public"),
     *                                 @OA\Property(property="size", type="integer"),
     *                                 @OA\Property(
     *                                     property="manipulations",
     *                                     type="array",
     *                                     @OA\Items(type="object")
     *                                 ),
     *                                 @OA\Property(
     *                                     property="custom_properties",
     *                                     type="object"
     *                                 ),
     *                                 @OA\Property(
     *                                     property="responsive_images",
     *                                     type="array",
     *                                     @OA\Items(type="object")
     *                                 ),
     *                                 @OA\Property(property="order_column", type="integer"),
     *                                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                                 @OA\Property(property="updated_at", type="string", format="date-time")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function list()
    {
        $user = $this->resolveUser();

        if ($user) {
            Auth::setUser($user);
            $items = $this->cartService->getCartForUI();

            return $this->apiResponse(
                success: true,
                status: 'ok',
                data: [
                    'items'      => $items,
                    'cart_count' => count($items),
                ]
            );
        }

        // ── GUEST: ?ids=20,22,25 se ─────────────────────────────
        $ids = $this->guestIds();

        if (empty($ids)) {
            return $this->apiResponse(
                success: true,
                status: 'ok',
                data: ['items' => [], 'cart_count' => 0]
            );
        }

        // DB se same data lo jo CartService getCartForUI() mein hota hai
        $rows = \Illuminate\Support\Facades\DB::table('carts')
            ->rightJoin('hoardings', function ($join) use ($ids) {
                $join->on('hoardings.id', '=', 'carts.hoarding_id')
                    ->whereIn('hoardings.id', $ids);
            })
            ->whereNull('hoardings.deleted_at')
            ->where('hoardings.status', \App\Models\Hoarding::STATUS_ACTIVE)
            ->whereIn('hoardings.id', $ids)
            ->select(
                \Illuminate\Support\Facades\DB::raw('NULL as cart_id'),
                \Illuminate\Support\Facades\DB::raw('NULL as package_id'),
                'hoardings.id as hoarding_id',
                'hoardings.vendor_id',
                'hoardings.title',
                'hoardings.slug',
                'hoardings.city',
                'hoardings.state',
                'hoardings.locality',
                'hoardings.category',
                'hoardings.hoarding_type',
                'hoardings.monthly_price',
                'hoardings.base_monthly_price',
                'hoardings.grace_period_days',
            )
            ->get();

        // Same buildCartItem() use karo jo logged-in ke liye use hoti hai
        $items = $rows->map(fn($item) => $this->cartService->buildCartItem($item));

        return $this->apiResponse(
            success: true,
            status: 'ok',
            data: [
                'items'      => $items,
                'cart_count' => $items->count(),
            ]
        );
    }
    /* ================= HELPERS ================= */

    private function cartCount(): int
    {
        return Auth::check()
            ? count($this->cartService->getCartHoardingIds())
            : 0;
    }

    private function guestIds(): array
    {
        return array_filter(
            array_map('intval', explode(',', request()->query('ids', '')))
        );
    }

    private function resolveUser()
    {
        if (Auth::check()) {
            return Auth::user();
        }

        try {
            $token = request()->bearerToken();
            if (!$token) return null;

            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if (!$accessToken) return null;

            return $accessToken->tokenable;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function apiResponse(
        bool   $success,
        string $status,
        string $message = '',
        array  $data = []
    ) {
        return response()->json([
            'success' => $success,
            'status'  => $status,
            'data'    => (object) $data,
            'message' => $message,
        ]);
    }
}
