<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamScoreController extends Controller
{
    /*
    public function getScores(Request $request, $examId)
    {
        try {
            $schoolId = $request->school_id;
            $gradeId = $request->grade_id;
            // $schoolId = $request->query('school_id');
            // $gradeId = $request->query('grade_id');
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
        */
    public function getScores(Request $request, $examId)
    {
        $schoolId = $request->school_id;
        $gradeId = $request->grade_id;

        if (!$schoolId) {
            return response()->json(['error' => 'school_id is required'], 422);
        }

        // Query siswa
        $studentsQuery = Student::where('school_id', $schoolId);

        if ($gradeId) {
            $studentsQuery->where('grade_id', $gradeId);
        }

        // Ambil siswa beserta sesi ujian (examSessions) dan grade
        $students = $studentsQuery->with([
            'examSessions' => function ($query) use ($examId) {
                $query->where('exam_id', $examId)->where('status', 'submited');
            },
            'grade'
        ])->get();

        // Bentuk output sesuai kebutuhan JavaScript
        $formatted = $students->map(function ($s) {
            $session = $s->examSessions->first();
            return [
                'nis'   => $s->nis,
                'name'  => $s->name,
                'grade' => $s->grade?->name ?? '-',
                'score' => $session?->total_score ?? 0
            ];
        });

        return response()->json([
            'students' => $formatted,
            'total_possible_score' => 100
        ]);
    }
}