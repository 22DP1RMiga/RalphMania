<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminActivityLogController extends Controller
{
    /**
     * Display activity logs.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('activity_type', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Activity type filter
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20)->withQueryString();

        // Get unique activity types for filter
        $activityTypes = ActivityLog::distinct()
            ->pluck('activity_type')
            ->filter()
            ->values();

        // Get users for filter
        $users = User::select('id', 'username')
            ->whereIn('id', ActivityLog::distinct()->pluck('user_id')->filter())
            ->orderBy('username')
            ->get();

        return Inertia::render('Admin/Logs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'activity_type', 'user_id', 'date_from', 'date_to']),
            'activityTypes' => $activityTypes,
            'users' => $users,
        ]);
    }

    /**
     * Export activity logs.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $filename = 'activity-logs-' . now()->format('Y-m-d-His');

        // Build query with filters
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('description', 'like', "%{$request->search}%");
        }
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        // CSV export
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}.csv",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, ['Laiks', 'Lietotājs', 'Darbības veids', 'Apraksts', 'IP adrese']);

            // Data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at?->format('Y-m-d H:i:s') ?? '-',
                    $log->user?->username ?? 'Sistēma',
                    $log->activity_type,
                    $log->description,
                    $log->ip_address,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
