<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Examtype;
use App\Models\Grade;
use App\Models\StudentRooms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomAssignmentController extends Controller
{
    /**
     * Show room assignment page for an exam type
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get school_id based on role
        if ($user->hasRole('kepala')) {
            $schoolId = session('school_id');
        }
        else if ($user->hasRole('guru'))
        {
            $schoolId = $user->teacher->school_id;
        }
        else {
            $schoolId = $request->school_id;
        }

        // Get exam types
        $examTypes = Examtype::where('is_active', true)
                        ->get();

        // Get rooms for the school
        $rooms = Rooms::where('school_id', $schoolId)
                        ->where('exam_type_id', $request->exam_type_id)
                        ->get();

        // Get grades
        $grades = Grade::all();

        // If exam_type_id is selected, get exams and students
        $selectedExamType = null;
        $exams = collect();
        $studentsAvailable = collect();

        if ($request->filled('exam_type_id')) {
            $selectedExamType = Examtype::find($request->exam_type_id);

            // Get exams under this exam type
            $exams = Exam::where('exam_type_id', $request->exam_type_id)->get();

            // Get students registered for these exams (from preassigned)
            if ($request->filled('exam_id')) {
                $studentsAvailable = DB::table('preassigneds')
                                    ->join('users', 'preassigneds.user_id', '=', 'users.id')
                                    ->join('students', 'users.id', '=', 'students.user_id')
                                    ->join('grades', 'students.grade_id', '=', 'grades.id')
                                    ->leftJoin('studentrooms', 'students.id', '=', 'studentrooms.student_id')
                                    ->leftJoin('rooms', function($join) use ($request) {
                                        $join->on('studentrooms.room_id', '=', 'rooms.id')
                                            ->where('rooms.exam_type_id', '=', $request->exam_type_id);
                                    })
                                    ->where('preassigneds.exam_id', $request->exam_id)
                                    ->where('students.school_id', $schoolId)
                                    ->select(
                                        'students.id',
                                        'students.nis',
                                        'students.name',
                                        'students.gender',
                                        'grades.name as grade_name',
                                        'studentrooms.room_id as assigned_room_id',
                                        'rooms.name as room_name'  // Optional: if you want room name
                                    )
                                    ->orderBy('grades.name')
                                    ->orderBy('students.name')
                                    ->get();
            }
        }

        return view('room-assignment.index', compact(
            'examTypes',
            'rooms',
            'grades',
            'selectedExamType',
            'exams',
            'studentsAvailable'
        ));
    }

    /**
     * Assign multiple students to a room
     */
    public function assignStudents(Request $request)
    {
        $validated = $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'room_id' => 'required|exists:rooms,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['student_ids'] as $studentId) {
                // Check if already assigned
                $existing = StudentRooms::where('student_id', $studentId)
                    ->where('exam_type_id', $validated['exam_type_id'])
                    ->first();

                if ($existing) {
                    // Update existing assignment
                    $existing->update(['room_id' => $validated['room_id']]);
                } else {
                    // Create new assignment
                    StudentRooms::create([
                        'student_id' => $studentId,
                        'room_id' => $validated['room_id'],
                        'exam_type_id' => $validated['exam_type_id'],
                    ]);
                }
            }

            logActivity(
                'students_assigned_to_room',
                count($validated['student_ids']) . " siswa ditugaskan ke ruangan ID: {$validated['room_id']}",
                auth()->id()
            );

            DB::commit();

            return redirect()->back()->with('success', count($validated['student_ids']) . ' siswa berhasil ditugaskan ke ruangan');

        } catch (\Exception $e) {
            DB::rollBack();

            logActivity(
                'students_room_assignment_failed',
                "Gagal menugaskan siswa ke ruangan: " . $e->getMessage(),
                auth()->id()
            );

            return redirect()->back()->withErrors(['error' => 'Gagal menugaskan siswa: ' . $e->getMessage()]);
        }
    }

    /**
     * Auto-assign students to rooms (distribute evenly)
     */
    public function autoAssign(Request $request)
    {
        $validated = $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'exam_id' => 'required|exists:exams,id',
            'school_id' => 'required|exists:schools,id',
        ]);

        try {
            DB::beginTransaction();

            // Get all rooms for this school
            $rooms = Rooms::where('school_id', $validated['school_id'])->get();

            if ($rooms->isEmpty()) {
                return redirect()->back()->withErrors(['error' => 'Tidak ada ruangan tersedia. Buat ruangan terlebih dahulu.']);
            }

            // Get students registered for this exam
            $students = DB::table('preassigned')
                ->join('students', 'preassigned.student_id', '=', 'students.id')
                ->where('preassigned.exam_id', $validated['exam_id'])
                ->where('preassigned.school_id', $validated['school_id'])
                ->select('students.id')
                ->pluck('id')
                ->toArray();

            if (empty($students)) {
                return redirect()->back()->withErrors(['error' => 'Tidak ada siswa terdaftar untuk ujian ini.']);
            }

            // Delete existing assignments for this exam type
            StudentRooms::where('exam_type_id', $validated['exam_type_id'])
                ->whereIn('student_id', $students)
                ->delete();

            // Distribute students evenly across rooms
            $totalStudents = count($students);
            $totalRooms = $rooms->count();
            $studentsPerRoom = ceil($totalStudents / $totalRooms);

            $currentRoomIndex = 0;
            $currentRoomCount = 0;

            foreach ($students as $studentId) {
                StudentRooms::create([
                    'student_id' => $studentId,
                    'room_id' => $rooms[$currentRoomIndex]->id,
                    'exam_type_id' => $validated['exam_type_id'],
                ]);

                $currentRoomCount++;

                // Move to next room if current room is full
                if ($currentRoomCount >= $studentsPerRoom && $currentRoomIndex < $totalRooms - 1) {
                    $currentRoomIndex++;
                    $currentRoomCount = 0;
                }
            }

            logActivity(
                'students_auto_assigned',
                "$totalStudents siswa berhasil didistribusikan ke $totalRooms ruangan",
                auth()->id()
            );

            DB::commit();

            return redirect()->back()->with('success', "$totalStudents siswa berhasil didistribusikan ke $totalRooms ruangan");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Gagal auto-assign: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove student from room
     */
    public function removeStudent(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'exam_type_id' => 'required|exists:exam_types,id',
        ]);

        try {
            StudentRooms::where('student_id', $validated['student_id'])
                ->where('exam_type_id', $validated['exam_type_id'])
                ->delete();

            logActivity(
                'student_removed_from_room',
                "Siswa ID: {$validated['student_id']} dihapus dari ruangan",
                auth()->id()
            );

            return redirect()->back()->with('success', 'Siswa berhasil dihapus dari ruangan');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus siswa: ' . $e->getMessage()]);
        }
    }

    /**
     * Get room details with students
     */
    public function getRoomDetails($roomId, Request $request)
    {
        $examTypeId = $request->get('exam_type_id');

        $room = Rooms::with(['students' => function($query) use ($examTypeId) {
            if ($examTypeId) {
                $query->wherePivot('exam_type_id', $examTypeId);
            }
            $query->with('grade');
        }])->findOrFail($roomId);

        return response()->json([
            'success' => true,
            'room' => $room,
            'students_count' => $room->students->count(),
            'students_by_grade' => $room->students->groupBy('grade.name'),
        ]);
    }
}
