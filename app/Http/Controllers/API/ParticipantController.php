<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitExamRequest;
use App\Services\ExamScoringService;
use App\Events\ExamSubmitted;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Models\ExamLog;
use Illuminate\Http\Request;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ParticipantController extends Controller
{
    protected $scoringService;

    public function __construct(ExamScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }
    public function index(Request $request)
    {
        $user = $request->user();

        // get user data
        $student = Student::with(['user', 'school'])
            ->where('user_id', $user->id)
            ->first();

        // take exam
        $assigned = $user->preassigned()
            ->with(['exam.examType'])
            ->get()
            ->map(function ($preassigned) {
                return [
                    'exam_id' => $preassigned->exam->id,
                    'title' => $preassigned->exam->title,
                    'duration' => $preassigned->exam->duration,
                    'total_quest' => $preassigned->exam->total_quest,
                ];
            });

        return response()->json([
            'success' => true,
            'student' => $student,
            'assigned' => $assigned
        ]);
    }

    public function start(Request $request, $examId)
    {
        try {
            $user = $request->user();

            // Get exam data first and validate
            $exam = Exam::find($examId);
            if (!$exam) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ujian tidak ditemukan'
                ], 404);
            }

            // Ensure duration and total_quest are strictly integers with validation
            $duration = filter_var($exam->duration, FILTER_VALIDATE_INT);
            $totalQuest = filter_var($exam->total_quest, FILTER_VALIDATE_INT);

            // Validate that we got valid integers
            if ($duration === false || $duration <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Durasi ujian tidak valid'
                ], 422);
            }

            if ($totalQuest === false || $totalQuest <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah soal ujian tidak valid'
                ], 422);
            }

            // Check if user already has an active session for this exam
            $existingSession = $user->examSessions()
                ->where('exam_id', $examId)
                ->where('status', 'progress')
                ->first();

            if ($existingSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki sesi ujian yang aktif untuk ujian ini'
                ], 422);
            }

            $sessionToken = bin2hex(random_bytes(16));
            $startTime = Carbon::now();

            // Use explicit integer casting and addMinutes method for safety
            $endTime = Carbon::parse($startTime)->addMinutes(intval($duration));

            $user->examSessions()->create([
                'user_id' => $user->id,
                'exam_id' => $examId,
                'session_token' => $sessionToken,
                'started_at' => $startTime,
                'submited_at' => $endTime,
                'time_remaining' => intval($duration * 60),
                'total_score' => 0,
                'status' => 'progress',
                'attempt_number' => 1,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            $questions = Question::where('exam_id', $examId)
                ->limit($totalQuest)
                ->get();

            return response()->json([
                'success' => true,
                'exam' => $questions,
                'session_token' => $sessionToken,
                'exam_info' => [
                    'title' => $exam->title,
                    'duration' => $duration,
                    'total_questions' => $totalQuest,
                    'start_time' => $startTime->toISOString(),
                    'end_time' => $endTime->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to start exam', [
                'user_id' => $request->user()->id ?? null,
                'exam_id' => $examId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai ujian. Silakan coba lagi.'
            ], 500);
        }
    }

    public function submit(SubmitExamRequest $request, $examId)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $sessionToken = $request->input('session_token');
            $answers = $request->input('answers', []);
            $essayAnswers = $request->input('essay_answers', []);
            $forceSubmit = $request->input('force_submit', false);

            // Find exam session
            $examSession = ExamSession::where('session_token', $sessionToken)
                ->where('user_id', $user->id)
                ->where('exam_id', $examId)
                ->where('status', 'progress')
                ->first();

            if (!$examSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ujian tidak ditemukan atau sudah berakhir'
                ], 404);
            }

            // Check if exam time is up (unless force submit)
            if (!$forceSubmit && Carbon::now()->isAfter($examSession->submited_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Waktu ujian telah berakhir'
                ], 422);
            }

            // Get exam and questions
            $exam = Exam::findOrFail($examId);
            $questions = Question::where('exam_id', $examId)->get();

            // Save final answers if provided
            if (!empty($answers) || !empty($essayAnswers)) {
                StudentAnswer::saveAnswers($examSession->id, $answers, $essayAnswers);
            }

            // Get all saved answers for scoring
            $studentAnswers = StudentAnswer::where('session_id', $examSession->id)->first();
            $finalAnswers = $studentAnswers ? $studentAnswers->answers : [];
            $finalEssayAnswers = $studentAnswers ? $studentAnswers->essay_answers : [];

            // Calculate score
            $scoreDetails = $this->scoringService->calculateScore($questions, $finalAnswers, $finalEssayAnswers);

            // Update exam session
            $examSession->update([
                'submited_at' => Carbon::now(),
                'time_remaining' => 0,
                'total_score' => $scoreDetails['total_score'],
                'status' => 'submited'
            ]);

            // Log submission
            ExamLog::create([
                'session_id' => $examSession->id,
                'action' => 'submit_exam',
                'details' => [
                    'user_id' => $user->id,
                    'submission_time' => Carbon::now()->toISOString(),
                    'total_questions' => $scoreDetails['total_questions'],
                    'answered_questions' => $scoreDetails['answered_questions'],
                    'score_breakdown' => $scoreDetails['score_breakdown'],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->header('User-Agent')
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'created_at' => Carbon::now()
            ]);

            // Fire event for additional processing
            event(new ExamSubmitted($examSession, $scoreDetails, [
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'submission_method' => $forceSubmit ? 'force' : 'normal'
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil diselesaikan',
                'data' => [
                    'session_id' => $examSession->id,
                    'exam_title' => $exam->title,
                    'total_score' => $scoreDetails['total_score'],
                    'max_score' => $scoreDetails['max_score'],
                    'percentage' => $scoreDetails['percentage'],
                    'grade' => $scoreDetails['grade'],
                    'total_questions' => $scoreDetails['total_questions'],
                    'answered_questions' => $scoreDetails['answered_questions'],
                    'unanswered_questions' => $scoreDetails['unanswered_questions'],
                    'submission_time' => Carbon::now()->toISOString(),
                    'score_breakdown' => $scoreDetails['score_breakdown'],
                    'detailed_results' => $scoreDetails['detailed_results']
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Exam submission failed', [
                'user_id' => $user->id ?? null,
                'exam_id' => $examId,
                'session_token' => $sessionToken ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan hasil ujian. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Get exam session status and remaining time
     * 
     * @param Request $request
     * @param int $examId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSessionStatus(Request $request, $examId)
    {
        $request->validate([
            'session_token' => 'required|string'
        ]);

        try {
            $user = $request->user();
            $sessionToken = $request->input('session_token');

            $examSession = ExamSession::where('session_token', $sessionToken)
                ->where('user_id', $user->id)
                ->where('exam_id', $examId)
                ->first();

            if (!$examSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak ditemukan'
                ], 404);
            }

            $now = Carbon::now();
            $endTime = Carbon::parse($examSession->submited_at);
            $timeRemaining = $endTime->diffInSeconds($now, false);

            if ($timeRemaining < 0) {
                $timeRemaining = 0;

                if ($examSession->status === 'progress') {
                    $this->autoSubmitExpiredExam($examSession);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'session_id' => $examSession->id,
                    'status' => $examSession->status,
                    'time_remaining' => $timeRemaining,
                    'started_at' => $examSession->started_at->toISOString(),
                    'end_time' => $examSession->submited_at->toISOString(),
                    'is_expired' => $timeRemaining <= 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status session'
            ], 500);
        }
    }

    private function autoSubmitExpiredExam($examSession)
    {
        try {
            DB::beginTransaction();

            $studentAnswers = StudentAnswer::where('session_id', $examSession->id)->first();
            $answers = $studentAnswers ? $studentAnswers->answers : [];
            $essayAnswers = $studentAnswers ? $studentAnswers->essay_answers : [];

            $questions = Question::where('exam_id', $examSession->exam_id)->get();

            $scoreDetails = $this->scoringService->calculateScore($questions, $answers, $essayAnswers);

            $examSession->update([
                'submited_at' => Carbon::now(),
                'time_remaining' => 0,
                'total_score' => $scoreDetails['total_score'],
                'status' => 'submited'
            ]);

            ExamLog::create([
                'session_id' => $examSession->id,
                'action' => 'auto_submit_expired',
                'details' => [
                    'user_id' => $examSession->user_id,
                    'submission_time' => Carbon::now()->toISOString(),
                    'reason' => 'Time expired',
                    'score_details' => $scoreDetails
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
                'created_at' => Carbon::now()
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto-submit failed', [
                'session_id' => $examSession->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
