<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Exam;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;


class ExamScoreController extends Controller
{
    public function exportPDF(Request $request, $examId)
    {

        try {
            $exam = Exam::findOrFail($examId);
            $schoolId = $request->query('school_id');
            // there is a possubility that grade_id is not provided
            $gradeId = $request->query('grade_id') ?: null;

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

            $scores = $students->map(function ($student) use ($examId) {
                $lastSession = $student->user->examSessions
                    ->where('exam_id', $examId)
                    ->where('status', 'submited')
                    ->first();

                return [
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'grade' => $student->grade->name,
                    'score' => $lastSession ? number_format((float) $lastSession->total_score, 2) : '0.00'
                ];
            });

            $pdf = PDF::loadView('exports.exam-scores', [
                'scores' => $scores,
                'exam' => $exam,
                'school' => $schoolId ? Student::find($scores->first()['id'])->school : null,
                'grade' => $gradeId ? Student::find($scores->first()['id'])->grade : null
            ]);

            return $pdf->download('nilai-ujian-' . Str::slug($exam->name) . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting scores: ' . $e->getMessage());
        }
    }
}
