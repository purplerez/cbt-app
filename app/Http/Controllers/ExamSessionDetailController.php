<?php

namespace App\Http\Controllers;

use App\Models\ExamSession;
use App\Models\StudentAnswer;
use App\Models\Question;
use App\Models\Student;
use App\Helpers\AnswerKeyHelper;
use Illuminate\Http\Request;

class ExamSessionDetailController extends Controller
{
    public function show(ExamSession $examSession)
    {
        // Load the session with related models
        $examSession->load(['student', 'exam', 'studentAnswer']);

        $answers = [];
        if ($examSession->studentAnswer) {
            // Get answers from JSON
            $mcAnswers = (array)($examSession->studentAnswer->answers ?? new \stdClass());
            $essayAnswers = (array)($examSession->studentAnswer->essay_answers ?? new \stdClass());

            // Get all questions for this exam with their types
            $questions = Question::where('exam_id', $examSession->exam_id)
                ->orderBy('id')
                ->get();

            // add the total score of all the questions
            $totalScore = $questions->sum(function ($question) {
                return is_numeric($question->points) ? (float)$question->points : 0.0;
            });

            // Process each question. Note: Question model uses `question_type_id` and relation `questionType()`
            foreach ($questions as $question) {
                $questionId = (string)$question->id; // JSON keys are strings
                $answer = null;

                // Essay type is represented by 3 in Question model mapping (see Question::getQuestionTypeLabelAttribute)
                if ((int)$question->question_type_id === 3) { // Essay
                    $answer = $essayAnswers[$questionId] ?? null;
                } else {
                    // For multiple-choice / complex / true-false use the answers map
                    $answer = $mcAnswers[$questionId] ?? null;
                }

                // Normalize choices
                $choices = [];
                if ($question->choices) {
                    $decoded = json_decode($question->choices, true);
                    $choices = is_array($decoded) ? $decoded : [];
                }

                // Determine correctness for objective types (0,1,2)
                $isCorrect = null;
                $pointsPossible = is_numeric($question->points) ? (float)$question->points : 0.0;
                $pointsAwarded = 0.0;
                $totalPoints = 0.0;

                // Scoring rules using AnswerKeyHelper for consistency
                // - Types 0 (MC single), 2 (True/False), 3 (Essay): full points if answer equals key
                // - Type 1 (MC complex): points are split by number of correct answers in key
                $qtype = (int)$question->question_type_id;
                // dd($answer, $choices);
                if ($qtype === 1) {
                    // Complex Multiple Choice: calculate partial score
                    if ($answer !== null && $answer !== '') {
                        $scoreResult = AnswerKeyHelper::calculatePartialScore(
                            $answer,
                            $question->answer_key,
                            $pointsPossible
                        );

                        $pointsAwarded = $scoreResult['points'];
                        $isCorrect = $scoreResult['is_correct'];
                    } else {
                        $isCorrect = null;
                        $pointsAwarded = 0.0;
                    }
                } elseif (in_array($qtype, [0, 2, 3], true)) {
                    // Single-answer types (MC Single, True/False, Essay)
                    if ($answer === null || $answer === '') {
                        $isCorrect = null;
                        $pointsAwarded = 0.0;
                    } else {
                        // Use AnswerKeyHelper to normalize and compare answers
                        $isCorrect = AnswerKeyHelper::compareAnswers($answer, $question->answer_key);
                        $pointsAwarded = $isCorrect ? $pointsPossible : 0.0;
                    }
                }

                //addition to status of complex multiple choice
                $displayCorrectAnswer = $question->answer_key;
                if ($qtype !== 3) { // Not essay
                    $normalized = AnswerKeyHelper::normalizeAnswerKey($question->answer_key, $qtype);
                    if (is_array($normalized)) {
                        $displayCorrectAnswer = implode(', ', $normalized);
                    } else {
                        $displayCorrectAnswer = $normalized;
                    }
                }



                // Add to answers array
                $answers[] = [
                    'question_id' => $questionId,
                    'question_text' => $question->question_text,
                    'question_type' => (int)$question->question_type_id,
                    'question_type_name' => $question->questionType->name ?? ($question->getQuestionTypeLabelAttribute() ?? 'Unknown'),
                    'choices' => $choices,
                    'answer' => $answer,
                    'correct_answer' => $displayCorrectAnswer, // $question->answer_key,
                    'answered_at' => $examSession->studentAnswer->last_answered_at ?? null,
                    'is_correct' => $isCorrect,
                    'points_awarded' => $pointsAwarded,
                    'points_possible' => $pointsPossible,
                ];
            }
            // sums all awarded points
            $totalPoints = array_sum(array_map(fn($a) => $a['points_awarded'], $answers));
            $percentage = ($totalScore > 0) ? ($totalPoints / $totalScore) * 100 : 0;
        }

        // dd($answers, $totalPoints, $totalScore );

        return view('admin.exam-session-detail', [
            'session' => $examSession,
            'answers' => $answers,
            'points_total' => $totalScore,
            'points_awarded' => $totalPoints,
            'percentage' => $percentage,
            // 'questions' =>

        ]);
    }

    public function resetLogin(Request $request, $studentNis)
    {
        try {
            $student = Student::where('nis', $studentNis)->firstOrFail();
            $user    = $student->user;

            // Check conditions for reset: is_logout == 0 AND ongoing session (time_remaining > 0 OR status = 'progress')
            $ongoingSessions = $user->examSessions()
                ->where('status', 'progress')
                ->orWhere('time_remaining', '>', 0)
                ->exists();

            if ($user->is_logout == 1 || !$ongoingSessions) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Reset tidak diperlukan: is_logout sudah 1 atau tidak ada sesi ongoing.',
                    ], 400);
                }
                return redirect()->back()->with('info', 'Reset tidak diperlukan: is_logout sudah 1 atau tidak ada sesi ongoing.');
            }

            // Lock the user account
            $user->is_active = 0;
            $user->save();

            // Log who performed the reset
            $actor = auth()->user();
            logActivity(
                $actor->name . ' (ID: ' . $actor->id . ') Kunci login untuk siswa ' .
                $student->name . ' (NIS: ' . $student->nis . ') - ongoing session detected'
            );

            // Return JSON for AJAX calls from dashboard monitoring panel
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => 'Akun siswa ' . $student->name . ' berhasil direset (is_active = 0, ongoing session).',
                    'student_name' => $student->name,
                    'nis'          => $student->nis,
                    'is_active'    => 0,
                    'is_logout'    => $user->is_logout,
                ]);
            }

            // Fallback redirect for non-AJAX
            $latestSession = $student->user->examSessions()->latest()->first();
            if ($latestSession) {
                $routePrefix = auth()->user()->hasRole('kepala') ? 'kepala' : 'admin';
                return redirect()
                    ->route($routePrefix . '.exam-sessions.detail', $latestSession->id)
                    ->with('success', 'Akun siswa berhasil direset.');
            }

            return redirect()->back()->with('success', 'Akun siswa berhasil direset.');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal reset akun: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal reset akun: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $sessionId)
    {
        try {
            $session = ExamSession::findOrFail($sessionId);

            // Log who performed the delete
            $actor = auth()->user();
            logActivity(
                $actor->name . ' (ID: ' . $actor->id . ') Menghapus sesi ujian ' .
                $session->id . ' untuk user ID: ' . $session->user_id
            );

            // Delete associated student answer explicitly if not cascaded
            if ($session->studentAnswer) {
                $session->studentAnswer->delete();
            }

            $session->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sesi ujian berhasil dihapus.',
                ]);
            }

            return redirect()->back()->with('success', 'Sesi ujian berhasil dihapus.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus sesi: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal menghapus sesi: ' . $e->getMessage());
        }
    }
}

