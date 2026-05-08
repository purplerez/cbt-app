<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogsController extends Controller
{
    /**
     * Get the 10 most recent activity logs
     */
    public function getRecentLogs(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'user_name' => $log->user?->name ?? 'Unknown',
                    'log_desc' => $log->log_desc,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'device' => $log->device,
                    'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : null,
                    'created_at_human' => $log->created_at ? $log->created_at->diffForHumans() : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $logs,
            'count' => count($logs),
        ]);
    }

    /**
     * Get paginated logs with filtering
     */
    public function getLogs(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $userId = $request->get('user_id', null);
        $searchTerm = $request->get('search', null);

        $query = ActivityLog::with('user');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($searchTerm) {
            $query->where('log_desc', 'like', '%' . $searchTerm . '%')
                  ->orWhere('ip_address', 'like', '%' . $searchTerm . '%');
        }

        $logs = $query->latest('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'total' => $logs->total(),
                'per_page' => $logs->perPage(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
            ],
        ]);
    }
}
