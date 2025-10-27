<?php

namespace App\Http\Controllers\Api\Kepala;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardStatisticsController extends Controller
{
    public function getOverviewStats(): JsonResponse
    {
        $schoolId = auth()->user()->school_id;

        // Cache statistics for 1 minute
        $stats = Cache::remember('kepala_dashboard_stats_'.$schoolId, 60, function () use ($schoolId) {
            $now = Carbon::now();

            $totalStudents = Student::where('school_id', $schoolId)->count();
            $totalGrades = Grade::whereHas('students', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })->count();

            $activeExamSessions = ExamSession::whereHas('student', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->whereDate('start_time', '<=', $now)
            ->whereDate('end_time', '>=', $now)
            ->get();

            $onlineStudents = ExamSession::whereHas('student', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->where('status', 'active')
            ->whereDate('start_time', '<=', $now)
            ->whereDate('end_time', '>=', $now)
            ->distinct('student_id')
            ->count();

            return [
                'total_students' => $totalStudents,
                'total_grades' => $totalGrades,
                'online_students' => $onlineStudents,
                'online_percentage' => $totalStudents > 0 ? round(($onlineStudents / $totalStudents) * 100, 1) : 0,
                'active_exams' => $activeExamSessions->unique('exam_id')->count(),
                'total_participants' => $activeExamSessions->count(),
                'exam_completion' => $this->getExamCompletion($schoolId),
                'grade_performance' => $this->getGradePerformance($schoolId)
            ];
        });

        return response()->json($stats);
    }

    private function getExamCompletion($schoolId): array
    {
        // Get completion statistics for recent exams
        $recentExams = ExamSession::whereHas('student', function($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })
        ->whereDate('created_at', '>=', now()->subDays(30))
        ->get()
        ->groupBy('exam_id');

        $completionStats = [];
        foreach ($recentExams as $examId => $sessions) {
            $exam = Exam::find($examId);
            if ($exam) {
                $totalStudents = $sessions->count();
                $completed = $sessions->where('status', 'completed')->count();
                $completionStats[] = [
                    'exam_name' => $exam->name,
                    'total' => $totalStudents,
                    'completed' => $completed,
                    'percentage' => $totalStudents > 0 ? round(($completed / $totalStudents) * 100, 1) : 0
                ];
            }
        }

        return $completionStats;
    }

    private function getGradePerformance($schoolId): array
    {
        // Get average scores by grade
        $grades = Grade::whereHas('students', function($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->get();

        $gradeStats = [];
        foreach ($grades as $grade) {
            $examSessions = ExamSession::whereHas('student', function($query) use ($schoolId, $grade) {
                $query->where('school_id', $schoolId)
                    ->where('grade_id', $grade->id);
            })
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->where('status', 'completed')
            ->get();

            $averageScore = $examSessions->avg('score') ?? 0;
            $gradeStats[] = [
                'grade_name' => $grade->name,
                'average_score' => round($averageScore, 1),
                'total_students' => Student::where('school_id', $schoolId)
                    ->where('grade_id', $grade->id)
                    ->count(),
                'exams_taken' => $examSessions->count()
            ];
        }

        return $gradeStats;
    }
}
