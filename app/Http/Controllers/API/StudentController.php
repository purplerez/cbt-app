<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Models\School;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Preassigned;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function getStudentsBySchool($schoolId)
    {
        try {
            $examId = request()->query('exam_id');

            $students = Student::with(['grade', 'user'])
                ->where('school_id', $schoolId)
                ->get()
                ->map(function ($student) use ($examId) {
                    $student->is_assigned = $student->user &&
                        Preassigned::where('user_id', $student->user->id)
                                  ->where('exam_id', $examId)
                                  ->exists();
                    return $student;
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
}
