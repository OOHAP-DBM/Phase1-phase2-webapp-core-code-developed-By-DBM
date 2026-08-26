<?php

namespace Modules\Enquiries\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SessionLog;

class SessionLogApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/logs/session",
     *     summary="List session logs",
     *     tags={"Logs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="action", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="from_date", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="to_date", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=20)),
     *     @OA\Response(response=200, description="Paginated session logs"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        // Determine role
        $role = $user->active_role ?? null;

        $query = SessionLog::query()->with('user')->latest('created_at');

        // If vendor or customer restrict to own logs
        if ($role === 'vendor' || $role === 'customer') {
            $query->where('user_id', $user->id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = (int) $request->get('per_page', 20);
        $logs = $query->paginate($perPage)->withQueryString();

        return response()->json(['success' => true, 'data' => $logs]);
    }

    /**
     * @OA\Get(
     *     path="/logs/session/{id}",
     *     summary="Show a single session log",
     *     tags={"Logs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Session log detail"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $session = SessionLog::with('user')->find($id);
        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        // vendor/customer can only view own sessions
        $role = $user->active_role ?? null;
        if (in_array($role, ['vendor','customer']) && $session->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        return response()->json(['success' => true, 'data' => $session]);
    }
}
