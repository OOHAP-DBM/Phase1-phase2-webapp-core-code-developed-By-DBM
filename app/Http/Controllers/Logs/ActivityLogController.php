<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        // ---------------------------------------------------------
        // Base Query
        // ---------------------------------------------------------

        $query = ActivityLog::query()
            ->with('user')
            ->latest('created_at');


        // ---------------------------------------------------------
        // Role-wise Data Restriction
        // ---------------------------------------------------------

        // Admin → Can see all activity logs
        if (
            $user &&
            method_exists($user, 'hasAnyRole') &&
            $user->hasAnyRole([
                'admin',
                'superadmin',
                'super_admin',
            ])
        ) {
            // No restriction
        }

        // Vendor → Only own activity logs
        elseif (
            $user &&
            method_exists($user, 'hasRole') &&
            $user->hasRole('vendor')
        ) {
            $query->where('user_id', $user->id)
                ->where('user_role', 'vendor');
        }

        // Customer → ONLY own Direct Enquiry activity logs
        elseif (
            $user &&
            method_exists($user, 'hasRole') &&
            $user->hasRole('customer')
        ) {
            $query->where('user_id', $user->id)
                ->where('user_role', 'customer')
                ->where('module', 'direct_enquiry');
        }

        // Unknown role
        else {
            abort(403, 'User does not have the right roles.');
        }


        // ---------------------------------------------------------
        // Filters
        // ---------------------------------------------------------

        // Admin role filter only
        if (
            $request->filled('user_role') &&
            $user->hasAnyRole([
                'admin',
                'superadmin',
                'super_admin',
            ])
        ) {
            $query->where('user_role', $request->user_role);
        }


        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }


        // Module filter
        // Customer already restricted to direct_enquiry
        if (
            $request->filled('module') &&
            $user->hasAnyRole([
                'admin',
                'superadmin',
                'super_admin',
            ])
        ) {
            $query->where('module', $request->module);
        }


        // From date
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }


        // To date
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }


        // ---------------------------------------------------------
        // Logs
        // ---------------------------------------------------------

        $logs = $query
            ->paginate(10)
            ->withQueryString();


        // ---------------------------------------------------------
        // Filter Dropdown Data
        // ---------------------------------------------------------

        if (
            $user->hasAnyRole([
                'admin',
                'superadmin',
                'super_admin',
            ])
        ) {

            $roles = ActivityLog::query()
                ->whereNotNull('user_role')
                ->distinct()
                ->orderBy('user_role')
                ->pluck('user_role');

            $actions = ActivityLog::query()
                ->whereNotNull('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action');

            $modules = ActivityLog::query()
                ->whereNotNull('module')
                ->distinct()
                ->orderBy('module')
                ->pluck('module');

        } else {

            // Vendor / Customer → dropdowns only from their own logs

            $actions = (clone $query)
                ->reorder()
                ->whereNotNull('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action');

            $modules = (clone $query)
                ->reorder()
                ->whereNotNull('module')
                ->distinct()
                ->orderBy('module')
                ->pluck('module');

            $roles = collect();
        }


        // ---------------------------------------------------------
        // Role-wise View
        // ---------------------------------------------------------

        // Admin Activity Logs
        if (
            $user &&
            method_exists($user, 'hasAnyRole') &&
            $user->hasAnyRole([
                'admin',
                'superadmin',
                'super_admin',
            ])
        ) {
            return view('logs.activity.index', compact(
                'logs',
                'roles',
                'actions',
                'modules'
            ));
        }


        // Vendor Activity Logs
        if (
            $user &&
            method_exists($user, 'hasRole') &&
            $user->hasRole('vendor')
        ) {
            return view('logs.activity.vendor-index', compact(
                'logs',
                'roles',
                'actions',
                'modules'
            ));
        }


        // Customer Activity Logs
        if (
            $user &&
            method_exists($user, 'hasRole') &&
            $user->hasRole('customer')
        ) {
            return view('logs.activity.customer-index', compact(
                'logs',
                'roles',
                'actions',
                'modules'
            ));
        }


        // Fallback
        abort(403, 'User does not have the right roles.');
    }
}