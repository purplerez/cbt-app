<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Grade;
use App\Models\Question;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KepalaApiDashboardController extends Controller
{
    /**
     * Get overview statistics for kepala sekolah's school
     */
    public function getStats()
    {
        try {
            $schoolId = session('school_id');

            if (!$schoolId) {
                return response()->json(['error' => 'School not found in session'], 400);
            }

            $school = School::find($schoolId);

            // Get total students in school
            $totalStudents = Student::where('school_id', $schoolId)->count();

            // Get online students (you may need to track this differently based on your auth system)
            $onlineStudents = User::whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->count();

            $onlinePercentage = $totalStudents > 0 ? round(($onlineStudents / $totalStudents) * 100, 2) : 0;

            // Get total grades
            $totalGrades = Grade::where('school_id', $schoolId)->count();

            // Get active exams for this school
            $activeExams = ExamSession::whereHas('exam')
                ->whereHas('user.student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->whereIn('status', ['in_progress', 'waiting'])
                ->distinct('exam_id')
                ->count('exam_id');

            // Get participant count in active exams
            $participantCount = ExamSession::whereIn('status', ['in_progress', 'waiting'])
                ->whereHas('user.student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->count();

            // Get total exams created for this school (global exams that have sessions from this school)
            $totalExams = Exam::whereHas('examSessions', function ($q) use ($schoolId) {
                $q->whereHas('user.student', function ($subQ) use ($schoolId) {
                    $subQ->where('school_id', $schoolId);
                });
            })
            ->distinct()
            ->count();

            // Count exam types
            $examTypes = Exam::whereHas('examSessions', function ($q) use ($schoolId) {
                $q->whereHas('user.student', function ($subQ) use ($schoolId) {
                    $subQ->where('school_id', $schoolId);
                });
            })
            ->distinct('exam_type_id')
            ->count('exam_type_id');

            // Determine exam status
            $examStatusText = $activeExams > 0 ? 'Ada Ujian Aktif' : 'Tidak Ada Ujian';
            $examStatusColor = $activeExams > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';

            return response()->json([
                'school_name' => $school->name ?? '-',
                'school_info' => $school->address ?? '-',
                'total_students' => $totalStudents,
                'total_grades' => $totalGrades,
                'online_students' => $onlineStudents,
                'online_percentage' => $onlinePercentage,
                'active_exams' => $activeExams,
                'participant_count' => $participantCount,
                'total_exams' => $totalExams,
                'exam_types' => $examTypes,
                'exam_status' => [
                    'text' => $examStatusText,
                    'color' => $examStatusColor
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get statistics grouped by grade
     */
    public function getGradeStats()
    {
        try {
            $schoolId = session('school_id');

            if (!$schoolId) {
                return response()->json(['error' => 'School not found in session'], 400);
            }

            $grades = Grade::where('school_id', $schoolId)->get();

            $data = $grades->map(function ($grade) {
                // Total students in grade
                $totalStudents = Student::where('grade_id', $grade->id)->count();

                // Online students in grade
                $onlineStudents = User::whereHas('student', function ($q) use ($grade) {
                    $q->where('grade_id', $grade->id);
                })
                ->where('last_activity_at', '>=', now()->subMinutes(5))
                ->count();

                $percentage = $totalStudents > 0 ? round(($onlineStudents / $totalStudents) * 100, 2) : 0;

                $status = $percentage >= 80 ? 'Baik' : ($percentage >= 50 ? 'Cukup' : 'Kurang');

                return [
                    'id' => $grade->id,
                    'name' => $grade->name,
                    'total_students' => $totalStudents,
                    'online_students' => $onlineStudents,
                    'percentage' => $percentage,
                    'status' => $status
                ];
            });

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get active exams for the school
     */
    public function getActiveExams()
    {
        try {
            $schoolId = session('school_id');

            if (!$schoolId) {
                return response()->json(['error' => 'School not found in session'], 400);
            }

            $sessions = ExamSession::with(['exam', 'user.student.grade'])
                ->whereIn('status', ['in_progress', 'waiting'])
                ->whereHas('user.student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->get()
                ->groupBy('exam_id');

            $data = $sessions->map(function ($examSessions, $examId) {
                $firstSession = $examSessions->first();
                $exam = $firstSession->exam;

                $totalParticipants = $examSessions->count();
                $activeParticipants = $examSessions->where('status', 'in_progress')->count();
                $completedParticipants = $examSessions->where('status', 'completed')->count();

                // Get remaining time from first session
                $remainingTime = $firstSession->remaining_time ?? 0;

                // Get grade names from participants
                $grades = $examSessions->pluck('user.student.grade.name')->unique()->join(', ');

                // Get status from first session
                $status = $firstSession->status;

                return [
                    'exam_id' => $examId,
                    'name' => $exam->title ?? 'N/A',
                    'grade' => $grades,
                    'total_participants' => $totalParticipants,
                    'active_participants' => $activeParticipants,
                    'completed_participants' => $completedParticipants,
                    'remaining_time' => $remainingTime,
                    'status' => $status
                ];
            })->values();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get recent exam scores for the school
     */
    public function getRecentScores()
    {
        try {
            $schoolId = session('school_id');

            if (!$schoolId) {
                return response()->json(['error' => 'School not found in session'], 400);
            }

            // Get recent exam sessions grouped by exam and grade
            $recentSessions = ExamSession::with(['exam', 'user.student.grade'])
                ->where('status', 'completed')
                ->whereHas('user.student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->latest('submited_at')
                ->limit(100)
                ->get()
                ->groupBy(function ($session) {
                    return $session->exam_id . '-' . $session->user->student->grade_id;
                });

            $data = $recentSessions->map(function ($grouped) {
                $firstSession = $grouped->first();
                $exam = $firstSession->exam;
                $grade = $firstSession->user->student->grade;

                // Calculate average score
                $totalPossibleScore = Question::where('exam_id', $exam->id)
                    ->sum(DB::raw('CAST(points as DECIMAL(10,2))'));

                $averageScore = $grouped->map(function ($session) use ($totalPossibleScore) {
                    $score = (float) ($session->total_score ?? 0);
                    return $totalPossibleScore > 0 ? ($score / $totalPossibleScore) * 100 : 0;
                })->avg();

                return [
                    'exam_id' => $exam->id,
                    'exam_name' => $exam->title,
                    'grade_name' => $grade->name,
                    'grade_id' => $grade->id,
                    'average_score' => number_format($averageScore, 2),
                    'participant_count' => $grouped->count(),
                    'total_possible_score' => number_format($totalPossibleScore, 2)
                ];
            })->values();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get exam scores with detailed breakdown for a specific exam
     */
    public function getExamScores(Exam $exam)
    {
        try {
            $schoolId = session('school_id');
            $gradeId = request()->query('grade_id');

            if (!$schoolId) {
                return response()->json(['error' => 'School not found in session'], 400);
            }

            // Get total possible score
            $totalPossibleScore = Question::where('exam_id', $exam->id)
                ->sum(DB::raw('CAST(points as DECIMAL(10,2))'));

            // Get exam sessions
            $sessions = ExamSession::with(['user.student'])
                ->where('exam_id', $exam->id)
                ->whereHas('user.student', function ($q) use ($schoolId, $gradeId) {
                    $q->where('school_id', $schoolId);
                    if ($gradeId) {
                        $q->where('grade_id', $gradeId);
                    }
                })
                ->get();

            $data = $sessions->map(function ($s, $idx) use ($totalPossibleScore) {
                $totalScore = (float) ($s->total_score ?? 0);
                $percentage = ($totalPossibleScore > 0) ? ($totalScore / $totalPossibleScore) * 100 : 0;

                return [
                    'no' => $idx + 1,
                    'id' => $s->id,
                    'nis' => $s->user?->student?->nis ?? '-',
                    'student_name' => $s->user?->name ?? 'N/A',
                    'grade_name' => $s->user?->student?->grade?->name ?? '-',
                    'total_score' => number_format($totalScore, 2),
                    'total_possible' => number_format($totalPossibleScore, 2),
                    'percentage' => number_format($percentage, 2),
                    'status' => $s->status,
                    'started_at' => $s->started_at?->format('d/m/Y H:i'),
                    'submited_at' => $s->submited_at?->format('d/m/Y H:i'),
                ];
            });

            return response()->json([
                'exam' => $exam->title,
                'total_possible_score' => number_format($totalPossibleScore, 2),
                'total_participants' => $sessions->count(),
                'average_score' => $sessions->count() > 0 ? number_format(
                    $sessions->avg(function ($s) use ($totalPossibleScore) {
                        $score = (float) ($s->total_score ?? 0);
                        return $totalPossibleScore > 0 ? ($score / $totalPossibleScore) * 100 : 0;
                    }), 2
                ) : 0,
                'scores' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get student details for a specific session
     */
    public function getSessionDetails($sessionId)
    {
        try {
            $schoolId = session('school_id');

            if (!$schoolId) {
                return response()->json(['error' => 'School not found in session'], 400);
            }

            $session = ExamSession::with(['exam', 'user.student'])
                ->find($sessionId);

            if (!$session) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            // Verify school ownership
            if ($session->user->student->school_id != $schoolId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json([
                'id' => $session->id,
                'exam' => $session->exam->title,
                'student_name' => $session->user->name,
                'nis' => $session->user->student->nis,
                'grade' => $session->user->student->grade->name,
                'total_score' => $session->total_score,
                'status' => $session->status,
                'started_at' => $session->started_at?->format('d/m/Y H:i:s'),
                'submited_at' => $session->submited_at?->format('d/m/Y H:i:s'),
                'duration_minutes' => $session->duration_minutes,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
