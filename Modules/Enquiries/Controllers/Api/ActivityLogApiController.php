<?php

namespace Modules\Enquiries\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ActivityLog;

class ActivityLogApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/logs/activity",
     *     summary="List activity logs",
     *     tags={"Logs"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="user_role", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="action", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="module", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=10)),
     *     @OA\Response(response=200, description="Paginated activity logs"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $query = ActivityLog::query()->with('user')->latest('created_at');

        // Role-wise restriction
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin','superadmin','super_admin'])) {
            // admin: no restriction
        } elseif (method_exists($user, 'hasRole') && $user->hasRole('vendor')) {
            $query->where('user_id', $user->id)->where('user_role', 'vendor');
        } elseif (method_exists($user, 'hasRole') && $user->hasRole('customer')) {
            $query->where('user_id', $user->id)->where('user_role', 'customer')->where('module', 'direct_enquiry');
        } else {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        // Filters
        if ($request->filled('user_role') && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin','superadmin','super_admin'])) {
            $query->where('user_role', $request->user_role);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module') && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin','superadmin','super_admin'])) {
            $query->where('module', $request->module);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $perPage = (int) $request->get('per_page', 10);
        $logs = $query->paginate($perPage)->withQueryString();

        // Dropdown data
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin','superadmin','super_admin'])) {
            $roles = ActivityLog::query()->whereNotNull('user_role')->distinct()->orderBy('user_role')->pluck('user_role');
            $actions = ActivityLog::query()->whereNotNull('action')->distinct()->orderBy('action')->pluck('action');
            $modules = ActivityLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');
        } else {
            $actions = (clone $query)->reorder()->whereNotNull('action')->distinct()->orderBy('action')->pluck('action');
            $modules = (clone $query)->reorder()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');
            $roles = collect();
        }

        return response()->json([
            'success' => true,
            'data' => $logs,
            'meta' => [
                'roles' => $roles,
                'actions' => $actions,
                'modules' => $modules,
            ]
        ]);
    }
}
