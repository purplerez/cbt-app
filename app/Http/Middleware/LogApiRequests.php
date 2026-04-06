<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LogApiRequests
{
    /**
     * Handle an incoming request for security monitoring and rate limiting
     */
    public function handle(Request $request, Closure $next)
    {
        // Get client info
        $ip = $this->getClientIp($request);
        $userId = auth()->id() ?? 'anonymous';
        $endpoint = $request->path();
        $method = $request->method();

        // Heavy endpoints to monitor
        $heavyEndpoints = [
            'api/siswa/heartbeat',
            'api/siswa/exam-session/save-answers',
            'api/siswa/exam-session/update-answer',
            'api/kepala/dashboard/stats',
            'api/admin/dashboard/stats',
        ];

        $isHeavy = in_array($endpoint, $heavyEndpoints);

        // Track request rate by IP
        if ($isHeavy) {
            $this->trackRequestRate($ip, $endpoint);
        }

        // Log suspicious patterns
        if ($this->isSuspicious($ip, $endpoint)) {
            $this->logSuspiciousActivity($ip, $userId, $endpoint, $method);
        }

        return $next($request);
    }

    /**
     * Get client's actual IP (handles proxies)
     */
    protected function getClientIp(Request $request)
    {
        // Check for IP from a shared internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        // Check for IP passed from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return trim($ip);
    }

    /**
     * Track request rate for this IP/endpoint
     */
    protected function trackRequestRate(string $ip, string $endpoint)
    {
        $key = "api:request_rate:{$ip}:{$endpoint}";
        $currentCount = Cache::get($key, 0);

        Cache::put($key, $currentCount + 1, 60); // 1-minute window

        // Log if exceeding threshold
        $threshold = 100; // Requests per minute per endpoint
        if ($currentCount > $threshold) {
            Log::warning("High request rate detected", [
                'ip' => $ip,
                'endpoint' => $endpoint,
                'requests_per_minute' => $currentCount + 1,
            ]);
        }
    }

    /**
     * Detect suspicious patterns
     */
    protected function isSuspicious(string $ip, string $endpoint): bool
    {
        // Pattern 1: Multiple rapid requests from same IP
        $recentRequests = Cache::get("api:suspicious:{$ip}", []);

        if (count($recentRequests) > 50) {
            // More than 50 requests in 5 minutes
            return true;
        }

        // Pattern 2: Bulk requests to heavy endpoints
        if (str_contains($endpoint, 'heartbeat')) {
            $heartbeatKey = "api:heartbeat_burst:{$ip}";
            $heartbeatCount = Cache::get($heartbeatKey, 0);
            if ($heartbeatCount > 10) { // More than 10 heartbeats per minute
                return true;
            }
            Cache::put($heartbeatKey, $heartbeatCount + 1, 60);
        }

        // Pattern 3: Non-authenticated bulk requests
        if (!auth()->check() && str_contains($endpoint, 'api')) {
            $anonKey = "api:anonymous_requests:{$ip}";
            $anonCount = Cache::get($anonKey, 0);
            if ($anonCount > 20) { // More than 20 anonymous requests per minute
                return true;
            }
            Cache::put($anonKey, $anonCount + 1, 60);
        }

        return false;
    }

    /**
     * Log suspicious activity
     */
    protected function logSuspiciousActivity(string $ip, string $userId, string $endpoint, string $method)
    {
        $alert = [
            'type' => 'SUSPICIOUS_ACTIVITY',
            'ip' => $ip,
            'user_id' => $userId,
            'endpoint' => $endpoint,
            'method' => $method,
            'timestamp' => now()->toIso8601String(),
            'user_agent' => request()->userAgent(),
        ];

        Log::channel('security')->warning(json_encode($alert));

        // Store in database for investigation
        try {
            DB::table('security_alerts')->insert([
                'alert_type' => 'SUSPICIOUS_API_USAGE',
                'ip_address' => $ip,
                'user_id' => $userId === 'anonymous' ? null : $userId,
                'endpoint' => $endpoint,
                'details' => json_encode($alert),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log security alert', ['error' => $e->getMessage()]);
        }
    }
}
