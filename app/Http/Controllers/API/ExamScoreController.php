<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamScoreController extends Controller
{
    public function getScores($examId)
    {
        try {
            $schoolId = request()->query('school_id');
            $gradeId = request()->query('grade_id');

            $query = Student::with(['grade', 'user.examSessions' => function ($query) use ($examId) {
                $query->where('exam_id', $examId)
                    ->where('status', 'submited')
                    ->orderBy('updated_at', 'desc');
            }])
            ->whereHas('user.examSessions', function ($query) use ($examId) {
                $query->where('exam_id', $examId)
                    ->where('status', 'submited');
            });

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }

            if ($gradeId) {
                $query->where('grade_id', $gradeId);
            }

            // Order by grade name
            $query->join('grades', 'students.grade_id', '=', 'grades.id')
                ->orderByRaw("
                    CASE
                        WHEN grades.name LIKE 'XII%' THEN 1
                        WHEN grades.name LIKE 'XI%' THEN 2
                        WHEN grades.name LIKE 'X%' THEN 3
                        ELSE 4
                    END,
                    grades.name,
                    students.name
                ")
                ->select('students.*');

            $students = $query->get();

            $scores = $students->map(function ($student) use ($examId) {
                $lastSession = $student->user->examSessions
                    ->where('exam_id', $examId)
                    ->where('status', 'submited')
                    ->first();

                return [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'grade' => $student->grade->name,
                    'score' => $lastSession ? (float) $lastSession->total_score : 0.0
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $scores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching scores: ' . $e->getMessage()
            ], 500);
        }
    }
}
