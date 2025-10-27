<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardStatisticsController extends Controller
{
    /**
     * Get dashboard overview statistics
     */
    public function getOverviewStats(): JsonResponse
    {
        // Cache statistics for 5 minutes to reduce database load
        $stats = Cache::remember('dashboard_stats', 300, function () {
            $now = Carbon::now();

            return [
                'total_students' => Student::count(),
                'active_exams' => ExamSession::whereDate('start_time', '<=', $now)
                    ->whereDate('end_time', '>=', $now)
                    ->count(),
                'online_students' => ExamSession::where('status', 'active')
                    ->whereDate('start_time', '<=', $now)
                    ->whereDate('end_time', '>=', $now)
                    ->distinct('student_id')
                    ->count(),
                'today_exams' => Exam::whereDate('created_at', Carbon::today())->count(),
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get currently active exams with details
     */
    public function getActiveExams(): JsonResponse
    {
        $now = Carbon::now();

        $activeExams = ExamSession::with(['exam', 'student', 'student.grade'])
            ->whereDate('start_time', '<=', $now)
            ->whereDate('end_time', '>=', $now)
            ->get()
            ->groupBy('exam_id')
            ->map(function ($sessions) use ($now) {
                $firstSession = $sessions->first();
                $exam = $firstSession->exam;

                return [
                    'exam_id' => $exam->id,
                    'name' => $exam->name,
                    'grade' => $firstSession->student->grade->name,
                    'total_participants' => $sessions->count(),
                    'active_participants' => $sessions->where('status', 'active')->count(),
                    'end_time' => $firstSession->end_time,
                    'remaining_time' => $now->diffInMinutes($firstSession->end_time),
                    'status' => $this->getExamStatus($firstSession),
                ];
            })
            ->values();

        return response()->json($activeExams);
    }

    /**
     * Calculate exam status based on session times
     */
    private function getExamStatus($session): string
    {
        $now = Carbon::now();

        if ($now < $session->start_time) {
            return 'waiting';
        }

        if ($now > $session->end_time) {
            return 'finished';
        }

        return 'in_progress';
    }
}