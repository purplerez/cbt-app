<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

if (! function_exists('logActivity')) {
    /**
     * Log activity to DB and also to a txt file (daily).
     *
     * @param  string       $event    Short key, e.g. 'login', 'exam_started'
     * @param  string|null  $desc     Human-readable description
     * @param  int|null     $userId
     * @param  array        $meta     Extra info (ip_address, user_agent, device)
     * @return void
     */
    function logActivity(string $event, ?string $desc = null, ?int $userId = null, array $meta = []): void
    {
        $userId = $userId ?? Auth::id();
        $ip = $meta['ip_address'] ?? (request()->ip() ?? null);
        $ua = $meta['user_agent'] ?? (request()->userAgent() ?? null);
        $device = $meta['device'] ?? null;

        $dbPayload = [
            'user_id'    => $userId,
            'log_desc'   => $desc ?? $event,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'device'     => $device,
            'created_at' => now(),
        ];

        // 1) Write to DB
        try {
            ActivityLog::create($dbPayload);
        } catch (\Throwable $e) {
            Log::error('ActivityLog DB write failed: ' . $e->getMessage(), $dbPayload);
        }

        // 2) Write to txt file (storage/logs/activity-YYYY-MM-DD.txt)
        try {
            $line = json_encode(array_merge(['timestamp' => now()->toDateTimeString(), 'event' => $event], $dbPayload), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $file = storage_path('logs/activity-' . now()->format('Y-m-d') . '.txt');
            file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            Log::error('ActivityLog file write failed: ' . $e->getMessage(), ['file' => $file ?? null]);
        }
    }
}
