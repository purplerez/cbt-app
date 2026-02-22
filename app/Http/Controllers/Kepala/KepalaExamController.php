<?php

namespace App\Http\Controllers\Kepala;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Examtype;
use App\Models\ExamSession;
use App\Models\Grade;
use App\Models\Preassigned;
use App\Models\Question;
use App\Models\School;
use App\Models\Student;
use App\Models\Headmaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KepalaExamController extends Controller
{
    //
    public function indexAll(){
        $exams = Examtype::where('is_global', true)
                    ->where('is_active', true)
                    ->get();

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

            return redirect()->route($this->getRoutePrefix() . '.exams.manage.view', $exams->id);
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

            $schoolId = session('school_id');
            $mapels = Exam::where('exam_type_id', session('exam_id'))
                        ->where('start_date', 'like', now()->format('Y-m-d').'%')
                        ->get();
            $grades = Grade::where('school_id', $schoolId)->get();

            return view('kepala.manageexam', compact('mapels', 'grades'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Tidak bisa melakukan manage Ujian :' . $e->getMessage()]);
        }
    }

    /**
     * Return JSON list of exam sessions (scores) for a given exam filtered by current school in session
     */
    public function scores(Request $request, Exam $exam)
    {
    try {
        $schoolId = session('school_id');
        $gradeId = $request->query('grade_id');

        if (!$schoolId) {
            return response()->json(['error' => 'School not found in session'], 400);
        }

        // Get total possible score for this exam
        $totalPossibleScore = Question::where('exam_id', $exam->id)
            ->sum(\Illuminate\Support\Facades\DB::raw('CAST(points as DECIMAL(10,2))'));

        $sessions = ExamSession::with(['user.student'])
            ->where('exam_id', $exam->id)
            ->whereHas('user.student', function ($q) use ($schoolId, $gradeId) {
                $q->where('school_id', $schoolId);
                if ($gradeId) {
                    $q->where('grade_id', $gradeId);
                }
            })
            ->get()
            ->sort(function ($a, $b) {
                // Sort by is_active ascending (0 first), then by name ascending
                $isActiveA = $a->user?->is_active ?? 1;
                $isActiveB = $b->user?->is_active ?? 1;
                if ($isActiveA !== $isActiveB) {
                    return $isActiveA - $isActiveB;
                }
                $nameA = strtolower($a->user?->name ?? '');
                $nameB = strtolower($b->user?->name ?? '');
                return $nameA <=> $nameB;
            })
            ->values();

        $data = $sessions->map(function ($s) use ($totalPossibleScore) {
            $totalScore = (float) ($s->total_score ?? 0);
            $percentage = ($totalPossibleScore > 0) ? ($totalScore / $totalPossibleScore) * 100 : 0;

            return [
                'id' => $s->id,
                'student_name' => $s->user?->name ?? ($s->user?->student?->name ?? 'N/A'),
                'nis' => $s->user?->student?->nis ?? '-',
                'total_score' => number_format($totalScore, 2),
                'total_possible' => number_format($totalPossibleScore, 2),
                'percentage' => number_format($percentage, 2),
                'is_active' => $s->user?->is_active ?? 1,
                'status' => $s->status,
                'started_at' => $s->started_at?->toDateTimeString(),
                'submited_at' => $s->submited_at?->toDateTimeString(),
            ];
        });

        return response()->json([
            'exam' => $exam->title,
            'total_possible_score' => number_format($totalPossibleScore, 2),
            'sessions' => $data,
        ])
        ->values();
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function participants(Request $request, $id)
    {
        // Fetch users (students) who belong to the currently selected school
        $schoolId = session('school_id');

        $selGrade = $request->query('grade_id');
        $selExam = $request->query('exam_id');


        $students = User::with(['preassigned' => function ($q) use ($id) {
            $q->where('exam_id', $id);
        }, 'student.grade'])
        ->whereHas('student', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })
        ->join('students', 'users.id', '=', 'students.user_id')
        ->join('grades', 'students.grade_id', '=', 'grades.id')
        ->orderBy('grades.name', 'asc')
        ->orderBy('users.name', 'asc')
        ->select('users.*')
        ->get();

        $grade = Grade::where('school_id', $schoolId)->get();

        // also fetch exams for the given exam_type_id (passed via URL)
        $examsList = Exam::where('exam_type_id', $id)->get();

        return view('kepala.view_participant', compact('students', 'grade', 'examsList'))->with('examId', $id);
    }

    /**
     * Register one or multiple students into a selected exam (preassigned)
     */
    public function registerParticipants(Request $request)
    {
        try {
            $data = $request->validate([
                'exam_id' => 'required|integer',
                'student_ids' => 'sometimes|array',
                'student_ids.*' => 'integer|exists:users,id',
                'student_id' => 'sometimes|integer',
            ]);

            $examId = $data['exam_id'];

            $ids = [];
            if (!empty($data['student_ids'])) {
                $ids = $data['student_ids'];
            } elseif (!empty($data['student_id'])) {
                $ids = [$data['student_id']];
            }

            $added = 0;

            if (empty($ids)) {
                throw new \Exception('Pilih minimal satu siswa untuk didaftarkan');
            }

            foreach ($ids as $userId) {
                $exists = Preassigned::where('user_id', $userId)->where('exam_id', $examId)->exists();

                if (!$exists) {
                    Preassigned::create([
                        'user_id' => $userId,
                        'exam_id' => $examId,
                    ]);
                    $added++;
                }
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['added' => $added], 200);
            }

            if ($added > 0) {
                return redirect()->back()->with('success', "Berhasil menambahkan $added peserta.");
            }
            else {
                throw new \Exception('Tidak ada peserta baru yang ditambahkan.');
            }
        }
        catch(\Exception $e){
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function deleteParticipant(Request $request)
    {
        try {
            $data = $request->validate([
                'student_id' => 'required|integer|exists:users,id',
                'exam_id' => 'required|integer|exists:exams,id',
            ]);

            $deleted = Preassigned::where('user_id', $data['student_id'])
                ->where('exam_id', $data['exam_id'])
                ->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => $deleted > 0,
                    'message' => $deleted > 0 ? 'Berhasil menghapus peserta' : 'Peserta tidak ditemukan'
                ], 200);
            }

            if ($deleted > 0) {
                return redirect()->back()->with('success', 'Berhasil menghapus peserta dari ujian.');
            } else {
                return redirect()->back()->with('error', 'Peserta tidak ditemukan.');
            }
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal menghapus peserta: ' . $e->getMessage());
        }
    }

    /**
     * Return students for current school with a flag whether they are preassigned for given exam_id
     */
    public function studentsByExam($examTypeId, Request $request)
    {
        $examId = $request->query('exam_id');
        $gradeId = $request->query('grade_id');

        if (!$examId) {
            return response()->json(['error' => 'exam_id is required'], 400);
        }

        $schoolId = session('school_id');
        if (!$schoolId) {
            return response()->json(['error' => 'school not found in session'], 400);
        }

        $query = User::with(['student.grade'])
            ->whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->join('students', 'users.id', '=', 'students.user_id')
            ->join('grades', 'students.grade_id', '=', 'grades.id');

        // Apply grade filter if provided
        if ($gradeId) {
            $query->where('students.grade_id', $gradeId);
        }

        $users = $query->orderBy('grades.name', 'asc')
            ->orderBy('users.name', 'asc')
            ->select('users.*')
            ->get();

        $data = $users->map(function ($u) use ($examId) {
            $registered = Preassigned::where('user_id', $u->id)
            ->where('exam_id', $examId)
            ->exists();

            return [
                'id' => $u->id,
                'name' => $u->student->name ?? $u->name,
                'grade' => $u->student->grade->name ?? '-',
                'registered' => $registered,
            ];
        });

        return response()->json(['students' => $data]);
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

    /**
     * Export exam scores as PDF with header containing school, exam, and grade
     */
    public function exportScoresPDF(Request $request, Exam $exam)
    {
        try {
            $schoolId = session('school_id');
            $gradeId = $request->query('grade_id');

            if (!$schoolId) {
                return back()->with('error', 'School not found in session');
            }

            // Get school and grade info
            $school = \App\Models\School::find($schoolId);
            $grade = $gradeId ? Grade::find($gradeId) : null;

            // Get total possible score for this exam
            $totalPossibleScore = Question::where('exam_id', $exam->id)
                ->sum(\Illuminate\Support\Facades\DB::raw('CAST(points as DECIMAL(10,2))'));

            // Fetch exam sessions with student data
            $sessions = ExamSession::with(['user.student'])
                ->where('exam_id', $exam->id)
                ->whereHas('user.student', function ($q) use ($schoolId, $gradeId) {
                    $q->where('school_id', $schoolId);
                    if ($gradeId) {
                        $q->where('grade_id', $gradeId);
                    }
                })
                ->get();

            $scores = $sessions->map(function ($s, $idx) use ($totalPossibleScore) {
                $totalScore = (float) ($s->total_score ?? 0);
                $percentage = ($totalPossibleScore > 0) ? ($totalScore / $totalPossibleScore) * 100 : 0;

                return [
                    'no' => $idx + 1,
                    'nis' => $s->user?->student?->nis ?? '-',
                    'student_name' => $s->user?->name ?? 'N/A',
                    'grade_name' => $s->user?->student?->grade?->name ?? '-',
                    'total_score' => number_format($totalScore, 2),
                    'total_possible' => number_format($totalPossibleScore, 2),
                    'percentage' => number_format($percentage, 2),
                ];
            });

            // Load view and generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.kepala-exam-scores', [
                'scores' => $scores,
                'exam' => $exam,
                'school' => $school,
                'grade' => $grade,
                'totalPossibleScore' => number_format($totalPossibleScore, 2),
            ]);

            return $pdf->download('nilai-ujian-' . \Illuminate\Support\Str::slug($exam->title) . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting scores: ' . $e->getMessage());
        }
    }

    /**
     * Print participant ID cards as PDF
     */
    public function printParticipantCards(Request $request, $examTypeId)
    {
        try {
            $schoolId = session('school_id');

            if (!$schoolId) {
                return back()->with('error', 'Sekolah tidak ditemukan di session');
            }

            // Get examtype info
            $examType = Examtype::findOrFail($examTypeId);

            // Get school info
            $school = School::findOrFail($schoolId);

            $headmaster = Headmaster::where('school_id', $schoolId)->first();

            $logoPath = storage_path('app/public/' . $school->logo);

            $logo = null;
            if (file_exists($logoPath)) {
                $logo = base64_encode(file_get_contents($logoPath));
            }

            // Get all preassigned students for this exam type's exams
            // First get all Exam records for this ExamType
            $examIds = Exam::where('exam_type_id', $examTypeId)->pluck('id')->toArray();

            // Get all students preassigned to any of these exams
            $preassignedUsers = Preassigned::whereIn('exam_id', $examIds)
                ->with(['user.student.grade'])
                ->whereHas('user.student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);  
                })
                ->get()
                ->groupBy('user_id')
                ->map(function ($group) {
                    return $group->first(); // Get first (unique per user)
                })
                ->values();

            $students = $preassignedUsers->map(function ($preassigned) {
                $user = $preassigned->user;
                $student = $user->student;

                return [
                    'name' => $user->name ?? 'N/A',
                    'email' => $user->email ?? '-',
                    'tgl_lahir' => $student?->d_birth ? \Carbon\Carbon::parse($student->d_birth)->format('d F Y') : '-',
                    'password' => $student?->nis ?? '-', // Use NIS as password (was used during user creation)
                    'nis' => $student?->nis ?? '-',
                    'class' => $student?->grade?->name ?? '-',
                ];
            });

            // Load view and generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.participant-cards', [
                'students' => $students,
                'examType' => $examType,
                'school' => $school,
                'logo' => $logo,
                'headmaster' => $headmaster,
            ]);

            // Set PDF options for better quality
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('dpi', 150);
            $pdf->setOption('isPhpEnabled', true);

            return $pdf->download('Data Kartu Peserta - ' . $examType->title . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Error mencetak kartu peserta: ' . $e->getMessage());
        }
    }

    private function getRoutePrefix()
    {
        $user = auth()->user();

        if ($user->hasRole('kepala')) {
            return 'kepala';
        }
        elseif ($user->hasRole('guru')) {
            return 'guru';
        }

        return 'admin';
    }

}
