<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardMonitorController extends Controller
{
    /**
     * Get live participant status for a specific exam.
     * Used by admin (all schools) and super (all schools, read-only).
     * Optional ?school_id= filter.
     */
    public function participants(Request $request, int $examId): JsonResponse
    {
        try {
            $schoolId = $request->query('school_id');

            $query = ExamSession::with(['user.student.grade', 'user.student.school'])
                ->where('exam_id', $examId)
                ->whereIn('status', ['progress', 'submited']);

            if ($schoolId) {
                $query->whereHas('user.student', fn($q) => $q->where('school_id', $schoolId));
            }

            $sessions = $query->get()->map(function ($session) {
                $now        = Carbon::now();
                $endTime    = Carbon::parse($session->submited_at);
                $isExpired  = $now->greaterThan($endTime);

                $timeRemaining = 0;
                if ($session->status === 'progress' && !$isExpired) {
                    $timeRemaining = max(0, $now->diffInSeconds($endTime, false));
                }

                return [
                    'session_id'   => $session->id,
                    'user_id'      => $session->user_id,
                    'nis'          => $session->user?->student?->nis ?? '-',
                    'name'         => $session->user?->student?->name ?? $session->user?->name ?? 'N/A',
                    'grade'        => $session->user?->student?->grade?->name ?? '-',
                    'school'       => $session->user?->student?->school?->name ?? '-',
                    'status'       => $session->status,
                    'time_remaining' => (int) $timeRemaining,
                    'started_at'   => $session->started_at?->format('H:i:s'),
                    'score'        => $session->status === 'submited' ? $session->total_score : null,
                    'ip_address'   => $session->ip_address,
'is_active'    => (isset($session->user) && $session->user->is_active !== 1 && $session->user->is_active !== true) ? 0 : 1,
                    'is_logout'    => $session->user->is_logout ?? 0,
                ];
            });

            // Also include pre-assigned students who haven't started yet
            $startedUserIds = $sessions->pluck('user_id')->toArray();

            $preassignedQuery = Student::with(['user', 'grade', 'school'])
                ->whereHas('user.preassigned', fn($q) => $q->where('exam_id', $examId))
                ->whereNotIn('user_id', $startedUserIds);

            if ($schoolId) {
                $preassignedQuery->where('school_id', $schoolId);
            }

            $notStarted = $preassignedQuery->get()->map(fn($student) => [
                'session_id'     => null,
                'user_id'        => $student->user_id,
                'nis'            => $student->nis,
                'name'           => $student->name,
                'grade'          => $student->grade?->name ?? '-',
                'school'         => $student->school?->name ?? '-',
                'status'         => 'not_started',
                'time_remaining' => 0,
                'started_at'     => null,
                'score'          => null,
                'ip_address'     => null,
'is_active'      => (isset($student->user) && $student->user->is_active !== 1 && $student->user->is_active !== true) ? 0 : 1,
                    'is_logout'    => $student->user->is_logout ?? 0,
            ]);

            $all = $sessions->concat($notStarted)->sortBy('name')->values();

            return response()->json([
                'success' => true,
                'data'    => $all,
                'counts'  => [
                    'progress'    => $sessions->where('status', 'progress')->count(),
                    'submited'    => $sessions->where('status', 'submited')->count(),
                    'not_started' => $notStarted->count(),
                    'total'       => $all->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
