<?php

namespace App\Http\Middleware;

use App\Models\ExamSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentExamState
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('siswa')) {
            return $next($request);
        }

        // Allow non-exam endpoints so students can still open the dashboard,
        // inspect their status, and keep heartbeat/user info flowing while locked.
        $allowedPaths = [
            'api/siswa/dashboard',
            'api/siswa/me',
            'api/user',
            'api/heartbeat',
        ];

        if (in_array($request->path(), $allowedPaths, true) || $request->routeIs('api.siswa.exam.force-exit')) {
            return $next($request);
        }

        $activeSessionExists = ExamSession::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'process', 'progress'])
            ->exists();

        // Check if student is locked (force exit: is_active = 1, is_logout = 1)
        if ($user->is_active === true && $user->is_logout === true) {
            return $this->forceExitResponse($request, $user->id, 'force_exit_locked');
        }

        // Check state mismatch: active session but not in correct state (is_active = 1, is_logout = 0)
        if ($activeSessionExists && !($user->is_active === true && $user->is_logout === false)) {
            return $this->forceExitResponse($request, $user->id, 'state_mismatch_with_active_session');
        }

        return $next($request);
    }

    private function forceExitResponse(Request $request, int $userId, string $reason): Response
    {
        $user = $request->user();

        // Mark student as force-exit locked: is_active = 1, is_logout = 1
        if ($user) {
            $user->is_active = true;
            $user->is_logout = true;
            $user->save();
        }

        Log::warning('Student force exit detected', [
            'user_id' => $userId,
            'reason' => $reason,
            'path' => $request->path(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Akun Anda sedang dikunci karena force exit. Hubungi proctor untuk reaktivasi.',
            'force_exit' => true,
        ], 403);
    }
}
