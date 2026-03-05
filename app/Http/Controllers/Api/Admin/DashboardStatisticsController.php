<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\School;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DashboardStatisticsController extends Controller
{
    /**
     * Get dashboard overview statistics (admin/super)
     */
    public function getOverviewStats(): JsonResponse
    {
        try {
            $stats = Cache::remember('admin_dashboard_stats', 30, function () {
                $totalStudents   = Student::count();
                $totalSchools    = School::count();
                $activeSchools   = School::has('students')->count();

                // Students currently in exam (active sessions)
                $studentsInExam = ExamSession::where('status', 'progress')
                    ->distinct('user_id')
                    ->count('user_id');

                // Active unique exams right now
                $activeExams = ExamSession::where('status', 'progress')
                    ->distinct('exam_id')
                    ->count('exam_id');

                // Submitted today
                $submittedToday = ExamSession::where('status', 'submited')
                    ->whereDate('updated_at', Carbon::today())
                    ->count();

                // Recent 5-min activity load (how many sessions updated)
                $recentActivity = ExamSession::where('updated_at', '>=', Carbon::now()->subMinutes(5))->count();

                $onlinePercentage = $totalStudents > 0
                    ? round(($studentsInExam / $totalStudents) * 100, 1)
                    : 0;

                $examStatusText  = $activeExams > 0 ? 'Ujian Berlangsung' : 'Tidak Ada Ujian';
                $examStatusColor = $activeExams > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';

                return [
                    'total_students'      => $totalStudents,
                    'total_schools'       => $totalSchools,
                    'active_school_count' => $activeSchools,
                    'students_in_exam'    => $studentsInExam,
                    'online_percentage'   => $onlinePercentage,
                    'active_exams'        => $activeExams,
                    'submitted_today'     => $submittedToday,
                    'recent_activity'     => $recentActivity,
                    'exam_status'         => [
                        'text'  => $examStatusText,
                        'color' => $examStatusColor,
                    ],
                ];
            });

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get list of active exams (for monitoring dropdown)
     */
    public function getActiveExams(): JsonResponse
    {
        try {
            $activeExamIds = ExamSession::where('status', 'progress')
                ->distinct('exam_id')
                ->pluck('exam_id');

            $exams = Exam::whereIn('id', $activeExamIds)
                ->select('id', 'title')
                ->get()
                ->map(fn($e) => ['exam_id' => $e->id, 'name' => $e->title]);

            return response()->json($exams);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}