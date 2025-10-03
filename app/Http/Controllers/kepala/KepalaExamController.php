<?php

namespace App\Http\Controllers\Kepala;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Examtype;
use App\Models\ExamSession;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KepalaExamController extends Controller
{
    //
    public function indexAll(){
        $exams = Examtype::where('is_global', true)->get();

        return view('kepala.view_examglobal', compact('exams'));
    }

    public function manage(Request $request, $id)
    {
        try {
            $exams = Examtype::findOrFail($id);

            session([
                'exam_id' => $exams->id,
                'exam_name' => $exams->title
            ]);

            return redirect()->route('kepala.exams.manage.view', $exams->id);
            // return view('kepala.view_exam', compact('exams'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Ujian Gagal di Load : ' . $e->getMessage()]);
        }
    }

    public function manageView(Examtype $examtype)
    {
        try {
            if (!session('exam_id')) {
                throw new \Exception('Ujian Tidak ditemukan');
            }

            $mapels = Exam::where('exam_type_id', session('exam_id'))->get();

            return view('kepala.manageexam', compact('mapels'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Tidak bisa melakukan manage Ujian :' . $e->getMessage()]);
        }
    }

    /**
     * Return JSON list of exam sessions (scores) for a given exam filtered by current school in session
     */
    public function scores(Exam $exam)
    {
        try {
            $schoolId = session('school_id');

            if (!$schoolId) {
                return response()->json(['error' => 'School not found in session'], 400);
            }

            $sessions = ExamSession::with(['user.student'])
                ->where('exam_id', $exam->id)
                ->whereHas('user.student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->get();

            $data = $sessions->map(function ($s) {
                return [
                    'id' => $s->id,
                    'student_name' => $s->user?->name ?? ($s->user?->student?->name ?? 'N/A'),
                    'nis' => $s->user?->student?->nis ?? '-',
                    'total_score' => $s->total_score,
                    'status' => $s->status,
                    'started_at' => $s->started_at?->toDateTimeString(),
                    'submited_at' => $s->submited_at?->toDateTimeString(),
                ];
            });

            return response()->json([
                'exam' => $exam->title,
                'sessions' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function participants($id)
    {
        $students = User::with(['preassigned' => function ($q) use ($id) {
                                                    $q->where('exam_id', $id);
                                                }])
                                                ->get();
        $grade = Grade::all();


        return view('kepala.view_participant', compact('students', 'grade'));
    }
}
