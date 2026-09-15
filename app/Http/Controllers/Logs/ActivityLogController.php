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


        $query = ActivityLog::query()
            ->with('user')
            ->latest('created_at');
        if (
            $user &&
            method_exists($user, 'hasAnyRole') &&
            $user->hasAnyRole([
                'admin',
                'superadmin',
                'super_admin',
            ])
        ) {

        } elseif (
            $user &&
            method_exists($user, 'hasRole') &&
            $user->hasRole('vendor')
        ) {
            $query->where('user_id', $user->id)
                ->where('user_role', 'vendor');
        } elseif (
            $user &&
            method_exists($user, 'hasRole') &&
            $user->hasRole('customer')
        ) {
            $query->where('user_id', $user->id)
                ->where('user_role', 'customer');

        } else {
            abort(403, 'User does not have the right roles.');
        }

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

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

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



        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }


        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }


        $logs = $query
            ->paginate(10)
            ->withQueryString();


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

        abort(403, 'User does not have the right roles.');
    }
}