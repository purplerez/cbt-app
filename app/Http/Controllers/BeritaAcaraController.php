<?php

namespace App\Http\Controllers;

use App\Models\BeritaAcara;
use App\Models\Exam;
use App\Models\Examtype;
use App\Models\ExamSession;
use App\Models\Rooms;
use App\Models\School;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BeritaAcaraController extends Controller
{
    /**
     * Display a listing of berita acara
     */
    public function index(Request $request)
    {
       // dd($request->all());
        $query = BeritaAcara::with(['examType', 'exam', 'school', 'room'])
            ->orderBy('created_at', 'desc');

        // Filter by school (for kepala sekolah)
        if (auth()->user()->hasRole('kepala')) {
            $headmaster = auth()->user()->headmaster;
            if ($headmaster) {
                $query->where('school_id', session('school_id'));
            }
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('exam_type_id')) {
            $query->byExamType($request->exam_type_id);
        }

        if ($request->filled('school_id') && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super'))) {
            $query->bySchool($request->school_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }
        // dd($query);
        $beritaAcaras = $query->paginate(15);

        // For filter dropdowns
        $examTypes = Examtype::all();
        $schools = School::where('status', '1')->get();

        return view('berita-acara.index', compact('beritaAcaras', 'examTypes', 'schools'));
    }

    /**
     * Show the form for creating a new berita acara
     */
    public function create()
    {
        $user = auth()->user();

        // Get schools based on role
        if ($user->hasRole('kepala')) {
            $headmaster = $user->headmaster;
            $schools = School::where('id', session('school_id'))->get();
            $defaultSchoolId = session('school_id');
        } else {
            $schools = School::where('status', '1')->get();
            $defaultSchoolId = null;
        }

        $examTypes = Examtype::all();
        $exams = Exam::all();
        $rooms = Rooms::all();

        // Get teachers for pengawas
        $teachers = User::role(['guru', 'kepala'])->get();

        return view('berita-acara.create', compact(
            'examTypes',
            'exams',
            'schools',
            'rooms',
            'teachers',
            'defaultSchoolId'
        ));
    }

    /**
     * Store a newly created berita acara
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'exam_id' => 'nullable|exists:exams,id',
            'school_id' => 'required|exists:schools,id',
            'room_id' => 'nullable|exists:rooms,id',
            'tanggal_pelaksanaan' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'jumlah_peserta_hadir' => 'required|integer|min:0',
            'jumlah_peserta_tidak_hadir' => 'required|integer|min:0',
            'jumlah_peserta_terdaftar' => 'required|integer|min:0',
            'pengawas' => 'nullable|array',
            'pengawas.*' => 'exists:users,id',
            'catatan_khusus' => 'nullable|string',
            'kondisi_pelaksanaan' => 'required|in:lancar,ada_kendala,terganggu',
            'kendala' => 'nullable|string',
            'kondisi_ruangan' => 'required|in:baik,cukup,kurang',
            'kondisi_peralatan' => 'required|in:baik,cukup,kurang',
        ]);

        try {
            DB::beginTransaction();

            $validated['created_by'] = auth()->id();
            $validated['status'] = 'draft';

            $beritaAcara = BeritaAcara::create($validated);

            logActivity(
                'berita_acara_created',
                "Berita Acara {$beritaAcara->nomor_ba} dibuat",
                auth()->id()
            );

            DB::commit();

            return redirect()
                ->route($this->getRoutePrefix() . '.berita-acara.index')
                ->with('success', 'Berita Acara berhasil dibuat dengan nomor: ' . $beritaAcara->nomor_ba);

        } catch (\Exception $e) {
            DB::rollBack();

            logActivity(
                'berita_acara_create_failed',
                "Gagal membuat Berita Acara: " . $e->getMessage(),
                auth()->id()
            );

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Gagal membuat Berita Acara: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified berita acara
     */
    public function show(BeritaAcara $beritaAcara)
    {
        $beritaAcara->load(['examType', 'exam', 'school', 'room', 'creator', 'approver']);

        return view('berita-acara.show', compact('beritaAcara'));
    }

    /**
     * Show the form for editing the specified berita acara
     */
    public function edit(BeritaAcara $beritaAcara)
    {
        // Check if can be edited
        if (!$beritaAcara->canBeEdited()) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Berita Acara tidak dapat diedit karena sudah disetujui atau diarsipkan']);
        }

        $user = auth()->user();

        // Get schools based on role
        if ($user->hasRole('kepala')) {
            $headmaster = $user->headmaster;
            $schools = School::where('id', $headmaster->school_id)->get();
        } else {
            $schools = School::where('status', '1')->get();
        }

        $examTypes = Examtype::all();
        $exams = Exam::all();
        $rooms = Rooms::all();
        $teachers = User::role(['guru', 'kepala'])->get();

        return view('berita-acara.edit', compact(
            'beritaAcara',
            'examTypes',
            'exams',
            'schools',
            'rooms',
            'teachers'
        ));
    }

    /**
     * Update the specified berita acara
     */
    public function update(Request $request, BeritaAcara $beritaAcara)
    {
        // Check if can be edited
        if (!$beritaAcara->canBeEdited()) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Berita Acara tidak dapat diedit']);
        }

        $validated = $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'exam_id' => 'nullable|exists:exams,id',
            'school_id' => 'required|exists:schools,id',
            'room_id' => 'nullable|exists:rooms,id',
            'tanggal_pelaksanaan' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'jumlah_peserta_hadir' => 'required|integer|min:0',
            'jumlah_peserta_tidak_hadir' => 'required|integer|min:0',
            'jumlah_peserta_terdaftar' => 'required|integer|min:0',
            'pengawas' => 'nullable|array',
            'pengawas.*' => 'exists:users,id',
            'catatan_khusus' => 'nullable|string',
            'kondisi_pelaksanaan' => 'required|in:lancar,ada_kendala,terganggu',
            'kendala' => 'nullable|string',
            'kondisi_ruangan' => 'required|in:baik,cukup,kurang',
            'kondisi_peralatan' => 'required|in:baik,cukup,kurang',
        ]);

        try {
            $beritaAcara->update($validated);

            logActivity(
                'berita_acara_updated',
                "Berita Acara {$beritaAcara->nomor_ba} diperbarui",
                auth()->id()
            );

            return redirect()
                ->route($this->getRoutePrefix() . '.berita-acara.show', $beritaAcara)
                ->with('success', 'Berita Acara berhasil diperbarui');

        } catch (\Exception $e) {
            logActivity(
                'berita_acara_update_failed',
                "Gagal memperbarui Berita Acara {$beritaAcara->nomor_ba}: " . $e->getMessage(),
                auth()->id()
            );

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Gagal memperbarui Berita Acara: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified berita acara
     */
    public function destroy(BeritaAcara $beritaAcara)
    {
        // Only draft can be deleted
        if ($beritaAcara->status !== 'draft') {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Hanya Berita Acara dengan status Draft yang dapat dihapus']);
        }

        try {
            $nomorBA = $beritaAcara->nomor_ba;
            $beritaAcara->delete();

            logActivity(
                'berita_acara_deleted',
                "Berita Acara {$nomorBA} dihapus",
                auth()->id()
            );

            return redirect()
                ->route($this->getRoutePrefix() . '.berita-acara.index')
                ->with('success', 'Berita Acara berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Gagal menghapus Berita Acara: ' . $e->getMessage()]);
        }
    }

    /**
     * Finalize berita acara (mark as ready for approval)
     */
    public function finalize(BeritaAcara $beritaAcara)
    {
        if ($beritaAcara->status !== 'draft') {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Hanya Berita Acara Draft yang dapat diselesaikan']);
        }

        $beritaAcara->finalize();

        return redirect()
            ->back()
            ->with('success', 'Berita Acara berhasil diselesaikan dan siap untuk disetujui');
    }

    /**
     * Approve berita acara (kepala sekolah or admin)
     */
    public function approve(BeritaAcara $beritaAcara)
    {
        if (!$beritaAcara->canBeApproved()) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Berita Acara tidak dapat disetujui']);
        }

        $beritaAcara->approve(auth()->id());

        return redirect()
            ->back()
            ->with('success', 'Berita Acara berhasil disetujui');
    }

    /**
     * Archive berita acara
     */
    public function archive(BeritaAcara $beritaAcara)
    {
        $beritaAcara->archive();

        return redirect()
            ->back()
            ->with('success', 'Berita Acara berhasil diarsipkan');
    }

    /**
     * Export to PDF
     */
    public function exportPdf(BeritaAcara $beritaAcara)
    {
        $beritaAcara->load(['examType', 'exam', 'school', 'room', 'creator', 'approver']);

        $pdf = Pdf::loadView('berita-acara.pdf', compact('beritaAcara'))
            ->setPaper('a4', 'portrait');

        $filename = 'Berita_Acara_' . str_replace('/', '_', $beritaAcara->nomor_ba) . '.pdf';

        logActivity(
            'berita_acara_exported',
            "Berita Acara {$beritaAcara->nomor_ba} diekspor ke PDF",
            auth()->id()
        );

        return $pdf->download($filename);
    }

    /**
     * Auto-fill data from exam session
     */
    public function autoFill(Request $request)
    {
        $validated = $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'exam_id' => 'required|exists:exams,id',
            'school_id' => 'required|exists:schools,id',
        ]);

        // Count participants from exam sessions
        $hadir = ExamSession::where('exam_id', $validated['exam_id'])
            ->whereHas('user.student', function ($q) use ($validated) {
                $q->where('school_id', $validated['school_id']);
            })
            ->whereIn('status', ['submited', 'progress'])
            ->count();

        // Get total registered students
        $terdaftar = DB::table('preassigneds')
            ->join('users', 'preassigneds.user_id', '=', 'users.id')
            ->join('students', 'users.id', '=', 'students.user_id')
            ->where('preassigneds.exam_id', $validated['exam_id'])
            ->where('students.school_id', $validated['school_id'])  // ✅ Now from students table
            // ->where('school_id', $validated['school_id'])
            ->count();

        $tidakHadir = $terdaftar - $hadir;

        return response()->json([
            'success' => true,
            'data' => [
                'jumlah_peserta_hadir' => $hadir,
                'jumlah_peserta_tidak_hadir' => max(0, $tidakHadir),
                'jumlah_peserta_terdaftar' => $terdaftar,
            ]
        ]);
    }

    /**
     * Print student attendance list by room and grade
     */
    public function printStudentList(BeritaAcara $beritaAcara)
    {
        $beritaAcara->load(['examType', 'exam', 'school', 'room']);

        // Get students from the room, grouped by grade
        $studentsByGrade = [];

        if ($beritaAcara->room_id) {
            // Get students assigned to this specific room
            $students = DB::table('student_rooms')
                ->join('students', 'student_rooms.student_id', '=', 'students.id')
                ->join('grades', 'students.grade_id', '=', 'grades.id')
                ->where('student_rooms.room_id', $beritaAcara->room_id)
                ->select(
                    'students.id',
                    'students.nis',
                    'students.name',
                    'students.grade_id',
                    'grades.name as grade_name'
                )
                ->orderBy('grades.name')
                ->orderBy('students.name')
                ->get();

            // Group by grade
            $studentsByGrade = $students->groupBy('grade_name');
        } else {
            // If no room specified, get all students registered for this exam
            $students = DB::table('preassigned')
                ->join('students', 'preassigned.student_id', '=', 'students.id')
                ->join('grades', 'students.grade_id', '=', 'grades.id')
                ->where('preassigned.exam_id', $beritaAcara->exam_id)
                ->where('preassigned.school_id', $beritaAcara->school_id)
                ->select(
                    'students.id',
                    'students.nis',
                    'students.name',
                    'students.grade_id',
                    'grades.name as grade_name'
                )
                ->orderBy('grades.name')
                ->orderBy('students.name')
                ->get();

            $studentsByGrade = $students->groupBy('grade_name');
        }

        $pdf = Pdf::loadView('berita-acara.student-list', compact('beritaAcara', 'studentsByGrade'))
            ->setPaper('a4', 'portrait');

        $filename = 'Daftar_Hadir_' . str_replace('/', '_', $beritaAcara->nomor_ba) . '.pdf';

        logActivity(
            'berita_acara_student_list_printed',
            "Daftar hadir siswa untuk BA {$beritaAcara->nomor_ba} dicetak",
            auth()->id()
        );

        return $pdf->download($filename);
    }

    /**
     * Get route prefix based on user role
     */
    private function getRoutePrefix()
    {
        $user = auth()->user();

        if ($user->hasRole('super')) {
            return 'super';
        } elseif ($user->hasRole('admin')) {
            return 'admin';
        } elseif ($user->hasRole('kepala')) {
            return 'kepala';
        }

        return 'admin';
    }
}
