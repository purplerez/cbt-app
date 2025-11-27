<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ExamSession;
use App\Models\StudentAnswer;
use App\Models\Question;
use App\Helpers\AnswerKeyHelper;
use App\Models\Exam;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Models\School;
use App\Models\Grade;


class ExamScoreController extends Controller
{
    public function exportPDF(Request $request, $examId)
    {

        try {
            $exam = Exam::findOrFail($examId);
            $schoolId = $request->query('school_id');
            $gradeId = $request->query('grade_id') ?: null;

            // Get total possible score for this exam
            $totalPossibleScore = Question::where('exam_id', $examId)
                ->sum(\DB::raw('CAST(points as DECIMAL(10,2))'));

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

            // Order by grade then name
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

            $scores = $students->map(function ($student) use ($examId, $totalPossibleScore) {
                $lastSession = $student->user->examSessions
                    ->where('exam_id', $examId)
                    ->where('status', 'submited')
                    ->first();

                $totalScore = $lastSession ? (float) $lastSession->total_score : 0;
                $percentage = ($totalPossibleScore > 0) ? ($totalScore / $totalPossibleScore) * 100 : 0;

                return [
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'grade' => $student->grade->name,
                    'score' => number_format($totalScore, 2),
                    'total_possible' => number_format($totalPossibleScore, 2),
                    'percentage' => number_format($percentage, 2)
                ];
            });

            // Resolve school and grade models directly from the provided IDs (safer than deriving from first student)
            $schoolModel = $schoolId ? School::find($schoolId) : null;
            $gradeModel = $gradeId ? Grade::find($gradeId) : null;

            $pdf = PDF::loadView('exports.exam-scores', [
                'scores' => $scores,
                'exam' => $exam,
                'school' => $schoolModel,
                'grade' => $gradeModel,
                'totalPossibleScore' => number_format($totalPossibleScore, 2)
            ]);

            return $pdf->download('nilai-ujian-' . Str::slug($exam->name) . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting scores: ' . $e->getMessage());
        }
    }
}
