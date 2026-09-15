<?php

namespace Modules\Admin\Controllers\Web\AuditLogs;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AuditLogsController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'action' => $request->get('action'),
            'module' => $request->get('module'),
            'user_id' => $request->get('user_id'),
            'user_type' => $request->get('user_type'),
            'from' => $request->get('from_date'),
            'to' => $request->get('to_date'),
            'search' => $request->get('search'),
        ];

        $logs = $this->auditService
            ->search($filters)
            ->with(['user', 'auditable'])
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        $actions = AuditLog::query()
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $modules = AuditLog::query()
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $userTypes = AuditLog::query()
            ->whereNotNull('user_type')
            ->distinct()
            ->orderBy('user_type')
            ->pluck('user_type');

        $statistics = $this->auditService->getStatistics([
            'from' => $request->get('from_date'),
            'to' => $request->get('to_date'),
            'module' => $request->get('module'),
        ]);

        return view('admin.audit-logs.index', compact(
            'logs',
            'actions',
            'modules',
            'userTypes',
            'statistics'
        ));
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load(['user', 'auditable']);

        $relatedLogs = collect();

        if ($auditLog->auditable_type && $auditLog->auditable_id) {
            $relatedLogs = AuditLog::forModel(
                $auditLog->auditable_type,
                $auditLog->auditable_id
            )
                ->where('id', '!=', $auditLog->id)
                ->with('user')
                ->latest('created_at')
                ->limit(10)
                ->get();
        }

        return view('admin.audit-logs.show', compact(
            'auditLog',
            'relatedLogs'
        ));
    }

    public function forModel(Request $request)
    {
        $request->validate([
            'model_type' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
        ]);

        $logs = AuditLog::forModel(
            $request->model_type,
            $request->model_id
        )
            ->with('user')
            ->latest('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    public function userActivity(Request $request, int $userId)
    {
        $limit = min(
            (int) $request->get('limit', 100),
            500
        );

        return response()->json([
            'success' => true,
            'logs' => $this->auditService->getUserActivity(
                $userId,
                $limit
            ),
        ]);
    }

    public function recent(Request $request)
    {
        $limit = min(
            (int) $request->get('limit', 50),
            500
        );

        return response()->json([
            'success' => true,
            'logs' => $this->auditService->getRecentActivity($limit),
        ]);
    }

    public function statistics(Request $request)
    {
        $statistics = $this->auditService->getStatistics([
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'module' => $request->get('module'),
        ]);

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
        ]);
    }

    public function search(Request $request)
    {
        $filters = [
            'action' => $request->get('action'),
            'module' => $request->get('module'),
            'user_id' => $request->get('user_id'),
            'user_type' => $request->get('user_type'),
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'model_type' => $request->get('model_type'),
            'model_id' => $request->get('model_id'),
            'search' => $request->get('search'),
        ];

        $perPage = min(
            (int) $request->get('per_page', 50),
            200
        );

        $logs = $this->auditService
            ->search($filters)
            ->with(['user', 'auditable'])
            ->latest('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    public function export(Request $request): Response
    {
        $filters = [
            'action' => $request->get('action'),
            'module' => $request->get('module'),
            'user_id' => $request->get('user_id'),
            'user_type' => $request->get('user_type'),
            'from' => $request->get('from'),
            'to' => $request->get('to'),
            'search' => $request->get('search'),
        ];

        $logs = $this->auditService
            ->search($filters)
            ->latest('created_at')
            ->get();

        $filename = 'audit_logs_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Date/Time',
                'User',
                'Role',
                'Action',
                'Module',
                'Model',
                'Description',
                'IP Address',
                'Changed Fields',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                    $log->user_name ?? 'System',
                    $log->user_type ?? 'system',
                    $log->action_label,
                    $log->module ?? '-',
                    $log->model_name,
                    $log->description ?? '-',
                    $log->ip_address ?? '-',
                    count($log->changed_fields ?? []),
                ]);
            }

            fclose($file);
        };

        return response()->stream(
            $callback,
            200,
            $headers
        );
    }

    public function timeline(Request $request)
    {
        $request->validate([
            'model_type' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
        ]);

        $logs = AuditLog::forModel(
            $request->model_type,
            $request->model_id
        )
            ->with('user')
            ->latest('created_at')
            ->get();

        $timeline = $logs->groupBy(
            fn($log) => $log->created_at->format('Y-m-d')
        );

        return response()->json([
            'success' => true,
            'timeline' => $timeline,
        ]);
    }
}