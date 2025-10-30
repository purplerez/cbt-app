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
        // Cache statistics for 1 minute to maintain real-time monitoring while reducing load
        $stats = Cache::remember('dashboard_stats', 60, function () {
            $now = Carbon::now();
            
            $totalStudents = Student::count();
            $activeSchools = School::has('students')->count();
            $activeExamSessions = ExamSession::whereDate('start_time', '<=', $now)
                ->whereDate('end_time', '>=', $now)
                ->get();
            
            $onlineStudents = ExamSession::where('status', 'active')
                ->whereDate('start_time', '<=', $now)
                ->whereDate('end_time', '>=', $now)
                ->distinct('student_id')
                ->count();

            // Calculate server load (simplified version)
            $last5MinLoad = ExamSession::where('updated_at', '>=', $now->subMinutes(5))
                ->count();
            
            return [
                'total_students' => $totalStudents,
                'active_school_count' => $activeSchools,
                'online_students' => $onlineStudents,
                'online_percentage' => $totalStudents > 0 ? round(($onlineStudents / $totalStudents) * 100, 1) : 0,
                'active_exams' => $activeExamSessions->unique('exam_id')->count(),
                'participant_count' => $activeExamSessions->count(),
                'server_load' => min(100, round(($last5MinLoad / 1500) * 100)), // Assuming max capacity 1500 concurrent
                'avg_response_time' => rand(50, 200), // Placeholder - implement actual monitoring
                'exam_status' => $this->getSystemStatus($onlineStudents, $last5MinLoad),
            ];
        });

        return response()->json($stats);
    }

    private function getSystemStatus($onlineStudents, $last5MinLoad): array
    {
        // Define thresholds
        $loadWarningThreshold = 1000; // 66% of max capacity
        $loadCriticalThreshold = 1350; // 90% of max capacity

        if ($last5MinLoad >= $loadCriticalThreshold) {
            return [
                'status' => 'critical',
                'text' => 'Beban Tinggi',
                'color' => 'bg-red-100 text-red-800'
            ];
        }

        if ($last5MinLoad >= $loadWarningThreshold) {
            return [
                'status' => 'warning',
                'text' => 'Beban Sedang',
                'color' => 'bg-yellow-100 text-yellow-800'
            ];
        }

        return [
            'status' => 'normal',
            'text' => 'Normal',
            'color' => 'bg-green-100 text-green-800'
        ];
    }    /**
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