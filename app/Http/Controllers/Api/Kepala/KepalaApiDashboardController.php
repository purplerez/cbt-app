<?php

namespace App\Http\Controllers\Api\Kepala;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Grade;
use App\Models\Headmaster;
use App\Models\Preassigned;
use App\Models\Question;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KepalaApiDashboardController extends Controller
{
    /**
     * Get school_id from Headmaster (kepala) or Teacher (guru) table
     */
    private function getSchoolId()
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        if ($user->hasRole('kepala')) {
            return Headmaster::where('user_id', $user->id)->value('school_id');
        }

        if ($user->hasRole('guru')) {
            return \App\Models\Teacher::where('user_id', $user->id)->value('school_id');
        }

        return null;
    }

    /**
     * Get overview statistics for kepala sekolah's school
     */
    public function getStats()
    {
        try {
            $schoolId = $this->getSchoolId();

            if (!$schoolId) {
                return response()->json(['error' => 'School not found for this user'], 400);
            }

            $school = School::find($schoolId);

            // Get total students in school
            $totalStudents = Student::where('school_id', $schoolId)->count();

            // Get online students (from active exam sessions)
            $onlineStudents = ExamSession::where('status', 'progress')
                ->whereHas('user.student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->distinct('user_id')
                ->count('user_id');

            $onlinePercentage = $totalStudents > 0 ? round(($onlineStudents / $totalStudents) * 100, 2) : 0;

            // Get total grades
            $totalGrades = Grade::where('school_id', $schoolId)->count();

            // Get active exams (exam_sessions with progress status for this school, only valid for today)
// $activeExams = ExamSession::whereIn('status', ['progress', 'waiting'])
                //     ->whereHas('exam', function ($q) {
                //         $q->whereDate('start_date', '<=', \Carbon\Carbon::today())
                //           ->whereDate('end_date', '>=', \Carbon\Carbon::today());
                //     })
                //     ->whereHas('user.student', function ($q) use ($schoolId) {
                //         $q->where('school_id', $schoolId);
                //     })
                //     ->distinct('exam_id')
                //     ->count('exam_id');
                $activeExams = Exam::whereHas('preassigneds.user.student', fn($q) => $q->where('school_id', $schoolId))
                    ->count();

            // Get active participant count
            $activeParticipants = ExamSession::where('status', 'progress')
                ->whereHas('exam', function ($q) {
                    $q->whereDate('start_date', '<=', \Carbon\Carbon::today())
                      ->whereDate('end_date', '>=', \Carbon\Carbon::today());
                })
                ->whereHas('user.student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->count();

            // Get total preassigned (registered for exams, not yet started)
            $totalPreassigned = Preassigned::whereHas('user.student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->count();

            // Get total exams this school's students are registered for
            $totalExams = Exam::whereHas('preassigneds', function ($q) use ($schoolId) {
                $q->whereHas('user.student', function ($subQ) use ($schoolId) {
                    $subQ->where('school_id', $schoolId);
                });
            })
            ->distinct()
            ->count();

            // Determine exam status
            $examStatusText = $activeExams > 0 ? 'Ada Ujian Aktif' : 'Tidak Ada Ujian';
            $examStatusColor = $activeExams > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';

            return response()->json([
                'school_name' => $school?->name ?? '-',
                'school_info' => $school?->address ?? '-',
                'school_id' => $schoolId,
                'total_students' => $totalStudents,
                'total_grades' => $totalGrades,
                'online_students' => $onlineStudents,
                'online_percentage' => $onlinePercentage,
                'active_exams' => $activeExams,
                'participant_count' => $activeParticipants,
                'total_exams' => $totalExams,
                'total_preassigned' => $totalPreassigned,
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
     * Get statistics grouped by grade - OPTIMIZED (no N+1 queries)
     */
    public function getGradeStats()
    {
        try {
            $schoolId = $this->getSchoolId();

            if (!$schoolId) {
                return response()->json(['error' => 'School not found for this user'], 400);
            }

            // Get all grades with student counts in ONE query
            $gradeStats = Student::where('school_id', $schoolId)
                ->selectRaw('grade_id, COUNT(*) as total_students')
                ->groupBy('grade_id')
                ->pluck('total_students', 'grade_id');

            // Get online students per grade in ONE query
            $onlineStats = DB::table('exam_sessions')
                ->selectRaw('students.grade_id, COUNT(DISTINCT exam_sessions.user_id) as online_students')
                ->join('users', 'exam_sessions.user_id', '=', 'users.id')
                ->join('students', 'users.id', '=', 'students.user_id')
                ->where('exam_sessions.status', 'progress')
                ->where('students.school_id', $schoolId)
                ->groupBy('students.grade_id')
                ->pluck('online_students', 'grade_id');

            // Now just format the data
            $grades = Grade::where('school_id', $schoolId)->get();

            $data = $grades->map(function ($grade) use ($gradeStats, $onlineStats) {
                $totalStudents = $gradeStats[$grade->id] ?? 0;
                $onlineStudents = $onlineStats[$grade->id] ?? 0;
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
     * Get active exams (exam_sessions with progress status)
     */
    public function getActiveExams()
    {
        try {
            $schoolId = $this->getSchoolId();

            if (!$schoolId) {
                return response()->json(['error' => 'School not found for this user'], 400);
            }

            // Get all exams with preassigned from this school
            $exams = Exam::whereHas('preassigneds.user.student', fn($q) => $q->where('school_id', $schoolId))
                ->select('id', 'title as name')
                ->orderBy('title')
                ->get();

            return response()->json($exams);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get live participant status for a specific exam (school-scoped)
     * Used by the kepala/guru live monitoring panel.
     */
    public function getMonitorParticipants(int $examId)
    {
        try {
            $schoolId = $this->getSchoolId();
            if (!$schoolId) {
                return response()->json(['error' => 'School not found for this user'], 400);
            }

            $sessions = ExamSession::with(['user.student.grade'])
                ->where('exam_id', $examId)
                ->whereIn('status', ['progress', 'submited'])
                ->whereHas('user.student', fn($q) => $q->where('school_id', $schoolId))
                ->get()
                ->map(function ($session) {
                    $now      = \Carbon\Carbon::now();
                    $endTime  = \Carbon\Carbon::parse($session->submited_at);
                    $timeLeft = ($session->status === 'progress' && $now->lessThan($endTime))
                        ? max(0, $now->diffInSeconds($endTime, false)) : 0;
                    return [
                        'session_id'     => $session->id,
                        'user_id'        => $session->user_id,
                        'nis'            => $session->user?->student?->nis ?? '-',
                        'name'           => $session->user?->student?->name ?? $session->user?->name ?? 'N/A',
                        'grade'          => $session->user?->student?->grade?->name ?? '-',
                        'status'         => $session->status,
                        'time_remaining' => (int) $timeLeft,
                        'started_at'     => $session->started_at?->format('H:i:s'),
                        'score'          => $session->status === 'submited' ? $session->total_score : null,
                        'ip_address'     => $session->ip_address,
                        'is_active'      => (isset($session->user) && $session->user->is_active !== 1 && $session->user->is_active !== true) ? 0 : 1,
                    ];
                });

            $startedUserIds = $sessions->pluck('user_id')->toArray();
            $notStarted = Student::with(['user', 'grade'])
                ->where('school_id', $schoolId)
                ->whereHas('user.preassigned', fn($q) => $q->where('exam_id', $examId))
                ->whereNotIn('user_id', $startedUserIds)
                ->get()
                ->map(fn($student) => [
                    'session_id'     => null,
                    'user_id'        => $student->user_id,
                    'nis'            => $student->nis,
                    'name'           => $student->name,
                    'grade'          => $student->grade?->name ?? '-',
                    'status'         => 'not_started',
                    'time_remaining' => 0,
                    'started_at'     => null,
                    'score'          => null,
                    'ip_address'     => null,
                    'is_active'      => (isset($student->user) && $student->user->is_active !== 1 && $student->user->is_active !== true) ? 0 : 1,
                ]);

            $all = $sessions->concat($notStarted)->sortBy('name')->values();
            return response()->json([
                'success' => true,
                'data'    => $all,
                'counts'  => [
                    'progress'    => $sessions->where('status', 'progress')->count(),
                    'submited'    => $sessions->where('status', 'submited')->count(),
                    'not_started' => $notStarted->count(),
                    'total'       => $all->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get recent exam scores (completed exams)
     */
    public function getRecentScores()
    {
        try {
            $schoolId = $this->getSchoolId();

            if (!$schoolId) {
                return response()->json(['error' => 'School not found for this user'], 400);
            }

            // Get submitted exam sessions grouped by exam and grade
            $completedSessions = ExamSession::with(['exam', 'user.student.grade'])
                ->where('status', 'submited')
                ->whereHas('user.student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })
                ->latest('submited_at')
                ->limit(100)
                ->get()
                ->groupBy(function ($session) {
                    return $session->exam_id . '-' . $session->user->student->grade_id;
                });

            // Pre-calculate exam total scores in ONE query (not per exam in the loop)
            $examTotals = Question::whereIn('exam_id', $completedSessions->map(fn($g) => $g->first()->exam_id)->unique())
                ->groupBy('exam_id')
                ->selectRaw('exam_id, SUM(CAST(points as DECIMAL(10,2))) as total_score')
                ->pluck('total_score', 'exam_id');

            $data = $completedSessions->map(function ($grouped) use ($examTotals) {
                $firstSession = $grouped->first();
                $exam = $firstSession->exam;
                $grade = $firstSession->user->student->grade;

                // Calculate average score from this group
                $averageScore = $grouped->avg('total_score');

                // Use pre-calculated totals (no query per item)
                $totalPossibleScore = $examTotals[$exam->id] ?? 0;

                $percentage = $totalPossibleScore > 0 ? ($averageScore / $totalPossibleScore) * 100 : 0;

                return [
                    'exam_id' => $exam->id,
                    'exam_name' => $exam->title,
                    'grade_name' => $grade->name,
                    'grade_id' => $grade->id,
                    'average_score' => number_format($averageScore, 2),
                    'percentage' => number_format($percentage, 2),
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
     * Get exam scores with detailed breakdown
     */
    public function getExamScores(Exam $exam)
    {
        try {
            $schoolId = $this->getSchoolId();
            $gradeId = request()->query('grade_id');

            if (!$schoolId) {
                return response()->json(['error' => 'School not found for this user'], 400);
            }

            // Get total possible score
            $totalPossibleScore = Question::where('exam_id', $exam->id)
                ->sum(DB::raw('CAST(points as DECIMAL(10,2))'));

            // Get exam sessions
            $sessions = ExamSession::with(['user.student'])
                ->where('exam_id', $exam->id)
                ->where('status', 'submited')
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

            // Get preassigned students (not yet started)
            $preassignedStudents = Preassigned::with(['user.student'])
                ->where('exam_id', $exam->id)
                ->whereHas('user.student', function ($q) use ($schoolId, $gradeId) {
                    $q->where('school_id', $schoolId);
                    if ($gradeId) {
                        $q->where('grade_id', $gradeId);
                    }
                })
                ->get()
                ->map(function ($p, $idx) use ($data, $totalPossibleScore) {
                    return [
                        'no' => $data->count() + $idx + 1,
                        'id' => null,
                        'nis' => $p->user?->student?->nis ?? '-',
                        'student_name' => $p->user?->name ?? 'N/A',
                        'grade_name' => $p->user?->student?->grade?->name ?? '-',
                        'total_score' => '0.00',
                        'total_possible' => number_format($totalPossibleScore, 2),
                        'percentage' => '0.00',
                        'status' => 'not_started',
                        'started_at' => '-',
                        'submited_at' => '-',
                    ];
                });

            $allData = $data->merge($preassignedStudents);

            return response()->json([
                'exam' => $exam->title,
                'total_possible_score' => number_format($totalPossibleScore, 2),
                'total_registered' => Preassigned::where('exam_id', $exam->id)->count(),
                'total_submitted' => $data->count(),
                'average_score' => $data->count() > 0 ? number_format(
                    $data->avg(function ($s) use ($totalPossibleScore) {
                        $score = (float) str_replace('.', '', str_replace(',', '.', $s['total_score']));
                        return $totalPossibleScore > 0 ? ($score / $totalPossibleScore) * 100 : 0;
                    }), 2
                ) : 0,
                'scores' => $allData
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get student session details
     */
    public function getSessionDetails($sessionId)
    {
        try {
            $schoolId = $this->getSchoolId();

            if (!$schoolId) {
                return response()->json(['error' => 'School not found for this user'], 400);
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
                'duration_minutes' => ceil($session->time_remaining / 60),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
