<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Models\SessionLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = SessionLog::query()
            ->with('user')
            ->latest('created_at');

        // Search user
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }


        if ($request->filled('action')) {
            $query->where('event', $request->input('action'));
        }

        // Date filter
        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                $request->input('from_date')
            );
        }

        $logs = $query->paginate(20);

        return view('logs.session.index', compact('logs'));
    }

    public function show(SessionLog $sessionLog): View
    {
        $sessionLog->load('user');

        return view('admin.logs.session-show', compact('sessionLog'));
    }

    public function customerIndex(Request $request)
    {
        $logs = SessionLog::query()
            ->where('user_id', auth()->id())
            ->latest('created_at')
            ->paginate(20);

        return view('logs.session.customer-index', compact('logs'));
    }

    public function vendorIndex(Request $request): View
    {
        $user = auth()->user();

        $query = SessionLog::query()
            ->with('user')
            ->where('user_id', $user->id)
            ->latest('created_at');

        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        /*
        |--------------------------------------------------------------------------
        | From Date
        |--------------------------------------------------------------------------
        */
        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->from_date
            );
        }

        /*
        |--------------------------------------------------------------------------
        | To Date
        |--------------------------------------------------------------------------
        */
        if ($request->filled('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->to_date
            );
        }

        $logs = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'logs.session.vendor-index',
            compact('logs')
        );
    }

 
    public function vendorShow(SessionLog $sessionLog): View
    {
         

        abort_unless(
            $sessionLog->user_id === auth()->id(),
            403
        );

        $sessionLog->load('user');

        return view(
            'vendor.logs.session.show',
            compact('sessionLog')
        );
    }

}