<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Models\School;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Preassigned;
use App\Models\User;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function getStudentsBySchool($schoolId)
    {
        try {
            $examId = request()->query('exam_id');

            $students = Student::with(['grade', 'user'])
                ->where('school_id', $schoolId)
                ->join('grades', 'students.grade_id', '=', 'grades.id')
                ->select('students.*')
                ->orderByRaw("
                    CASE
                        WHEN grades.name LIKE 'XII%' THEN 1
                        WHEN grades.name LIKE 'XI%' THEN 2
                        WHEN grades.name LIKE 'X%' THEN 3
                        ELSE 4
                    END
                ")
                ->get()
                ->map(function ($student) use ($examId) {
                    $student->is_assigned = $student->user &&
                        Preassigned::where('user_id', $student->user->id)
                                  ->where('exam_id', $examId)
                                  ->exists();
                    return $student;
                })
                ->sort(function ($a, $b) {
                    // Sort by assignment status (assigned students first)
                    if ($a->is_assigned !== $b->is_assigned) {
                        return $b->is_assigned - $a->is_assigned;
                    }
                    return 0;
                });

            return StudentResource::collection($students);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching students'], 500);
        }
    }

    public function addStudentToExam(Request $request)
    {
        try {
            $request->validate([
                'exam_id' => 'required|exists:exams,id',
                'student_id' => 'required|exists:students,id'
            ]);

            $exam = Exam::findOrFail($request->exam_id);
            $student = Student::findOrFail($request->student_id);

            // Get user_id from student
            $user = $student->user;
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak memiliki akun user'
                ]);
            }

            // Check if student is already assigned to exam
            if (Preassigned::where('user_id', $user->id)
                         ->where('exam_id', $exam->id)
                         ->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa sudah terdaftar dalam ujian ini'
                ]);
            }

            // Add student to exam through preassigned
            Preassigned::create([
                'user_id' => $user->id,
                'exam_id' => $exam->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil ditambahkan ke ujian'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan siswa ke ujian'
            ], 500);
        }
    }

    public function getExamParticipants($examId)
    {
        try {
            $schoolId = request()->query('school_id');

            $query = Student::with(['grade', 'user', 'user.examSessions' => function($query) use ($examId) {
                $query->where('exam_id', $examId)
                      ->orderBy('updated_at', 'desc');
            }])
            ->whereHas('user.preassigned', function($query) use ($examId) {
                $query->where('exam_id', $examId);
            });

            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }

            $participants = $query->get()
                ->map(function ($student) use ($examId) {
                    $lastSession = $student->user->examSessions
                        ->where('exam_id', $examId)
                        ->first();

                    return [
                        'id' => $student->id,
                        'nis' => $student->nis,
                        'name' => $student->name,
                        'grade' => $student->grade->name,
                        'last_activity' => $lastSession ? [
                            'status' => $lastSession->status,
                            'start_time' => $lastSession->started_at,
                            'submit_time' => $lastSession->submited_at,
                            'time_remaining' => $lastSession->time_remaining,
                            'score' => $lastSession->total_score,
                            'attempt' => $lastSession->attempt_number,
                            'ip' => $lastSession->ip_address
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $participants
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching participants: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getExamStats($examId)
    {
        try {
            $schoolId = request()->query('school_id');

            $query = Preassigned::where('exam_id', $examId)
                ->join('users', 'preassigneds.user_id', '=', 'users.id')
                ->join('students', 'users.id', '=', 'students.user_id');

            if ($schoolId) {
                $query->where('students.school_id', $schoolId);
            }

            $totalParticipants = $query->count();

            $stats = [
                'total_participants' => $totalParticipants,
                'active_participants' => ExamSession::where('exam_id', $examId)
                    ->where('status', 'progress')
                    ->whereIn('user_id', $query->pluck('users.id'))
                    ->count(),
                'submitted_participants' => ExamSession::where('exam_id', $examId)
                    ->where('status', 'submited')
                    ->whereIn('user_id', $query->pluck('users.id'))
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stats: ' . $e->getMessage()
            ], 500);
        }
    }
}
