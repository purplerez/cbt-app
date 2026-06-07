<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Models\ExamLog;
use App\Models\Preassigned;
use App\Models\User;
use App\Models\Student;
use App\Services\ExamScoringService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BenchmarkController extends Controller
{
    protected $scoringService;

    public function __construct(ExamScoringService $scoringService)
    {
        $this->scoringService = $scoringService;
    }

    public function health()
    {
        return response()->json([
            'success' => true,
            'message' => 'Server is healthy',
            'timestamp' => now()->toISOString(),
            'server_time' => microtime(true),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $token = $user->createToken('benchmark-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function dashboard(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $student = Student::with(['user', 'school'])
            ->where('user_id', $user->id)
            ->first();

        $assigned = Preassigned::where('user_id', $user->id)
            ->with(['exam.examType'])
            ->get()
            ->map(function ($preassigned) {
                $now = Carbon::now();
                $startDate = Carbon::parse($preassigned->exam->start_date);
                $endDate = Carbon::parse($preassigned->exam->end_date);

                $status = 'upcoming';
                if ($now->greaterThanOrEqualTo($startDate) && $now->lessThanOrEqualTo($endDate)) {
                    $status = 'available';
                } elseif ($now->greaterThan($endDate)) {
                    $status = 'expired';
                }

                return [
                    'exam_id' => $preassigned->exam->id,
                    'title' => $preassigned->exam->title,
                    'duration' => $preassigned->exam->duration,
                    'total_quest' => $preassigned->exam->total_quest,
                    'start_date' => $preassigned->exam->start_date ? $startDate->toISOString() : null,
                    'end_date' => $preassigned->exam->end_date ? $endDate->toISOString() : null,
                    'status' => $status,
                    'can_start' => $status === 'available',
                ];
            });

        return response()->json([
            'success' => true,
            'student' => $student,
            'assigned' => $assigned
        ]);
    }

    public function startExam(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'exam_id' => 'required|integer',
        ]);

        $user = User::find($request->user_id);
        $examId = $request->exam_id;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $exam = Exam::find($examId);
        if (!$exam) {
            return response()->json([
                'success' => false,
                'message' => 'Exam not found'
            ], 404);
        }

        $duration = filter_var($exam->duration, FILTER_VALIDATE_INT);
        $totalQuest = filter_var($exam->total_quest, FILTER_VALIDATE_INT);

        if ($duration === false || $duration <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid exam duration'
            ], 422);
        }

        if ($totalQuest === false || $totalQuest <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid total questions'
            ], 422);
        }

        return DB::transaction(function () use ($user, $examId, $exam, $duration, $totalQuest, $request) {
            $existingSession = $user->examSessions()
                ->where('exam_id', $examId)
                ->where('status', 'progress')
                ->lockForUpdate()
                ->first();

            if ($existingSession) {
                $questions = Question::where('exam_id', $examId)
                    ->limit($totalQuest)
                    ->get();

                return response()->json([
                    'success' => true,
                    'exam' => $questions,
                    'session_token' => $existingSession->session_token,
                    'exam_info' => [
                        'title' => $exam->title,
                        'duration' => $duration,
                        'total_questions' => $totalQuest,
                        'start_time' => $existingSession->started_at->toISOString(),
                        'end_time' => $existingSession->submited_at->toISOString(),
                        'is_continuing' => true
                    ],
                    'message' => 'Continuing existing exam session'
                ]);
            }

            $sessionToken = bin2hex(random_bytes(16));
            $startTime = Carbon::now();
            $endTime = Carbon::parse($startTime)->addMinutes(intval($duration));

            $examSession = $user->examSessions()->create([
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

            StudentAnswer::saveAnswers($examSession->id, [], []);

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
        });
    }

    public function saveAnswer(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string',
            'question_id' => 'required|integer',
            'answer' => 'required|string',
        ]);

        $examSession = ExamSession::where('session_token', $request->session_token)
            ->where('status', 'progress')
            ->first();

        if (!$examSession) {
            return response()->json([
                'success' => false,
                'message' => 'Exam session not found or expired'
            ], 404);
        }

        $studentAnswer = StudentAnswer::firstOrNew([
            'session_id' => $examSession->id,
        ]);

        $answers = $studentAnswer->answers ?? [];
        $answers[$request->question_id] = $request->answer;
        $studentAnswer->answers = $answers;
        $studentAnswer->save();

        return response()->json([
            'success' => true,
            'message' => 'Answer saved',
            'question_id' => $request->question_id,
        ]);
    }

    public function submitExam(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string',
            'exam_id' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $examSession = ExamSession::where('session_token', $request->session_token)
                ->where('exam_id', $request->exam_id)
                ->where('status', 'progress')
                ->first();

            if (!$examSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam session not found or already submitted'
                ], 404);
            }

            $exam = Exam::find($request->exam_id);
            $studentAnswer = StudentAnswer::where('session_id', $examSession->id)->first();
            $answers = $studentAnswer ? ($studentAnswer->answers ?? []) : [];

            $questions = Question::where('exam_id', $request->exam_id)->get();

            $scoreResult = $this->scoringService->calculateScore($questions, $answers, []);
            $totalScore = is_array($scoreResult) ? ($scoreResult['total_score'] ?? 0) : (float) $scoreResult;

            $examSession->status = 'submited';
            $examSession->total_score = $totalScore;
            $examSession->save();

            ExamLog::create([
                'session_id' => $examSession->id,
                'action' => 'submit',
                'details' => ['description' => 'Exam submitted via benchmark'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Exam submitted successfully',
                'score' => $totalScore,
                'score_details' => $scoreResult,
                'session_token' => $request->session_token,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit exam: ' . $e->getMessage()
            ], 500);
        }
    }
}
