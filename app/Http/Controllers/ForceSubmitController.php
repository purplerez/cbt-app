<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamLog;
use App\Models\Preassigned;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Services\ExamScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForceSubmitController extends Controller
{
    protected $scoringService;

    public function __construct(ExamScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Force submit a single exam session
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceSubmitSession(Request $request)
    {
        $validated = $request->validate([
            'exam_session_id' => 'required|integer|exists:exam_sessions,id'
        ]);

        try {
            DB::beginTransaction();

            $examSession = ExamSession::findOrFail($validated['exam_session_id']);

            // Check if already submitted
            if ($examSession->status === 'submited') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ujian ini sudah disubmit sebelumnya'
                ], 400);
            }

            // Calculate score from student answers
            $scoreResult = $this->calculateExamScore($examSession);

            // Update exam session
            $examSession->update([
                'total_score' => $scoreResult['total_score'],
                'status' => 'submited',
                'submited_at' => now()
            ]);

            // Create exam log entry
            ExamLog::create([
                'session_id' => $examSession->id,
                'action' => 'force_submit',
                'details' => [
                    'score' => $scoreResult['total_score'],
                    'max_score' => $scoreResult['max_score'],
                    'percentage' => $scoreResult['percentage'],
                    'grade' => $scoreResult['grade'],
                    'submitted_by_admin' => auth()->user()->name,
                    'reason' => 'Force submitted due to connection loss or system issue'
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);

            // Remove from preassigned (cleanup)
            Preassigned::where('user_id', $examSession->user_id)
                ->where('exam_id', $examSession->exam_id)
                ->delete();

            // Log the action
            Log::channel('exam_actions')->info('Exam session force submitted', [
                'exam_session_id' => $examSession->id,
                'user_id' => $examSession->user_id,
                'exam_id' => $examSession->exam_id,
                'submitted_by_admin_id' => auth()->id(),
                'score' => $scoreResult['total_score'],
                'timestamp' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil di-force submit dengan skor: ' . round($scoreResult['total_score'], 2),
                'score' => round($scoreResult['total_score'], 2),
                'max_score' => round($scoreResult['max_score'], 2),
                'percentage' => $scoreResult['percentage']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Force submit failed', [
                'error' => $e->getMessage(),
                'exam_session_id' => $validated['exam_session_id'] ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan force submit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force submit multiple exam sessions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceSubmitMultiple(Request $request)
    {
        $validated = $request->validate([
            'exam_session_ids' => 'required|array|min:1',
            'exam_session_ids.*' => 'integer|exists:exam_sessions,id'
        ]);

        $sessionIds = $validated['exam_session_ids'];
        $successCount = 0;
        $failedCount = 0;
        $results = [];

        try {
            DB::beginTransaction();

            foreach ($sessionIds as $sessionId) {
                try {
                    $examSession = ExamSession::findOrFail($sessionId);

                    // Skip if already submitted
                    if ($examSession->status === 'submited') {
                        $results[] = [
                            'session_id' => $sessionId,
                            'success' => false,
                            'message' => 'Sudah disubmit sebelumnya'
                        ];
                        $failedCount++;
                        continue;
                    }

                    // Calculate score
                    $scoreResult = $this->calculateExamScore($examSession);

                    // Update exam session
                    $examSession->update([
                        'total_score' => $scoreResult['total_score'],
                        'status' => 'submited',
                        'submited_at' => now()
                    ]);

                    // Create exam log entry
                    ExamLog::create([
                        'session_id' => $examSession->id,
                        'action' => 'force_submit',
                        'details' => [
                            'score' => $scoreResult['total_score'],
                            'max_score' => $scoreResult['max_score'],
                            'percentage' => $scoreResult['percentage'],
                            'grade' => $scoreResult['grade'],
                            'submitted_by_admin' => auth()->user()->name
                        ],
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'created_at' => now()
                    ]);

                    // Remove from preassigned
                    Preassigned::where('user_id', $examSession->user_id)
                        ->where('exam_id', $examSession->exam_id)
                        ->delete();

                    $results[] = [
                        'session_id' => $sessionId,
                        'success' => true,
                        'message' => 'Berhasil disubmit',
                        'score' => round($scoreResult['total_score'], 2)
                    ];

                    $successCount++;

                    Log::channel('exam_actions')->info('Exam session force submitted', [
                        'exam_session_id' => $examSession->id,
                        'user_id' => $examSession->user_id,
                        'exam_id' => $examSession->exam_id,
                        'submitted_by_admin_id' => auth()->id(),
                        'score' => $scoreResult['total_score']
                    ]);

                } catch (\Exception $e) {
                    $results[] = [
                        'session_id' => $sessionId,
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ];
                    $failedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'total_submitted' => $successCount + $failedCount,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'message' => "Force submit selesai. Berhasil: $successCount, Gagal: $failedCount",
                'results' => $results
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk force submit failed', [
                'error' => $e->getMessage(),
                'count' => count($sessionIds)
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan force submit bulk: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate exam score from student answers
     *
     * @param ExamSession $examSession
     * @return array
     */
    private function calculateExamScore(ExamSession $examSession): array
    {
        // Get all questions for this exam
        $questions = Question::where('exam_id', $examSession->exam_id)
            ->where('status', 'active')
            ->get();

        if ($questions->isEmpty()) {
            // If no questions, return 0
            return [
                'total_score' => 0,
                'max_score' => 0,
                'percentage' => 0,
                'grade' => 'N/A'
            ];
        }

        // Get student answers
        $studentAnswers = StudentAnswer::where('session_id', $examSession->id)->get();

        // Build answers array for scoring service
        $answers = [];
        $essayAnswers = [];

        foreach ($studentAnswers as $answer) {
            if ($answer->answer_type === 'essay') {
                $essayAnswers[$answer->question_id] = $answer->answer;
            } else {
                $answers[$answer->question_id] = $answer->answer;
            }
        }

        // Calculate score using ExamScoringService
        $scoreResult = $this->scoringService->calculateScore($questions, $answers, $essayAnswers);

        return [
            'total_score' => $scoreResult['total_score'],
            'max_score' => $scoreResult['max_score'],
            'percentage' => $scoreResult['percentage'],
            'grade' => $scoreResult['grade'] ?? 'N/A'
        ];
    }

    /**
     * Get incomplete sessions for an exam
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getIncompleteSessions(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|integer|exists:exams,id'
        ]);

        try {
            $incompleteSessions = ExamSession::where('exam_id', $validated['exam_id'])
                ->where('status', '!=', 'submited')
                ->with(['user.student.grade', 'exam'])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'user_id' => $session->user_id,
                        'student_name' => $session->user->student?->name ?? $session->user->name,
                        'nis' => $session->user->student?->nis ?? '-',
                        'grade' => $session->user->student?->grade?->name ?? '-',
                        'started_at' => $session->started_at?->format('d/m/Y H:i') ?? '-',
                        'last_activity' => $session->updated_at?->format('d/m/Y H:i') ?? '-',
                        'status' => $session->status,
                        'session_id' => $session->id
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $incompleteSessions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }
}
