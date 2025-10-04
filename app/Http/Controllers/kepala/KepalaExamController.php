<?php

namespace App\Http\Controllers\Kepala;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Examtype;
use App\Models\ExamSession;
use App\Models\Grade;
use App\Models\Preassigned;
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
        // Fetch users (students) who belong to the currently selected school
        $schoolId = session('school_id');

        $students = User::with(['preassigned' => function ($q) use ($id) {
            $q->where('exam_id', $id);
        }, 'student'])
        ->whereHas('student', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })
        ->get();

        $grade = Grade::all();

        return view('kepala.view_participant', compact('students', 'grade'))->with('examId', $id);
    }

    public function storeParticipants(Request $request, $exam)
    {
        $data = $request->validate([
            'student_ids' => 'sometimes|array',
            'student_ids.*' => 'integer|exists:users,id',
            'student_id' => 'sometimes|integer|exists:users,id',
        ]);

        $added = 0;
        $examId = $exam;

        $ids = [];
        if (!empty($data['student_ids'])) {
            $ids = $data['student_ids'];
        } elseif (!empty($data['student_id'])) {
            $ids = [$data['student_id']];
        }

        foreach ($ids as $userId) {
            // prevent duplicates
            $exists = Preassigned::where('user_id', $userId)->where('exam_id', $examId)->exists();
            if (!$exists) {
                Preassigned::create([
                    'user_id' => $userId,
                    'exam_id' => $examId,
                ]);
                $added++;
            }
        }

        if ($added > 0) {
            return redirect()->back()->with('success', "Berhasil menambahkan $added peserta.");
        }

        return redirect()->back()->with('error', 'Tidak ada peserta baru yang ditambahkan.');
    }

    public function storeOneParticipant(Request $request, $exam)
    {
        try{
            $data = $request->validate([
                'student_id' => 'required|integer|exists:users,id',
            ]);

            Preassigned::create([
                'user_id' => $data['student_id'],
                'exam_id' => $exam,
            ]);

            return redirect()->back()->with('success', 'Berhasil menambahkan peserta.');
        }
        catch(\Exception $e){
            return redirect()->back()->with('error', 'Tidak ada peserta baru yang ditambahkan.');
        }
    }
}
