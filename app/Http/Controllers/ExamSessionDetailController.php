<?php

namespace App\Http\Controllers;

use App\Models\ExamSession;
use App\Models\StudentAnswer;
use App\Models\Question;
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

                // Scoring rules:
                // - Types 0 (MC single), 2 (True/False), 3 (Essay): full points if answer equals key
                // - Type 1 (MC complex): points are split by number of correct answers in key
                $qtype = (int)$question->question_type_id;

                if ($qtype === 1) {
                    // complex MC: parse correct answers as array
                    $rawKey = $question->answer_key;
                    $correctAnswers = [];
                    if ($rawKey !== null && $rawKey !== '') {
                        $decoded = json_decode($rawKey, true);
                        if (is_array($decoded)) {
                            $correctAnswers = $decoded;
                        } else {
                            // try comma-separated
                            $correctAnswers = array_map('trim', explode(',', (string)$rawKey));
                        }
                    }

                    // normalize to strings
                    $correctAnswers = array_map('strval', array_filter($correctAnswers, fn($v) => $v !== '' && $v !== null));
                    $correctCount = count($correctAnswers);

                    // student answers as array
                    $studentAnswers = [];
                    if (is_array($answer)) {
                        $studentAnswers = array_map('strval', $answer);
                    } elseif ($answer !== null && $answer !== '') {
                        // if stored as comma-separated string
                        $studentAnswers = array_map('trim', explode(',', (string)$answer));
                        $studentAnswers = array_map('strval', $studentAnswers);
                    }

                    // count matches (unique)
                    $matches = array_intersect($correctAnswers, $studentAnswers);
                    $matchedCount = count(array_unique($matches));

                    $perAnswerPoint = $correctCount > 0 ? ($pointsPossible / $correctCount) : 0.0;
                    $pointsAwarded = $perAnswerPoint * $matchedCount;

                    // mark correctness if full match
                    $isCorrect = ($correctCount > 0 && $matchedCount === $correctCount);
                } elseif (in_array($qtype, [0, 2, 3], true)) {
                    // single-answer objective types and essay (as requested): compare key
                    if ($answer === null) {
                        $isCorrect = null;
                        $pointsAwarded = 0.0;
                    } else {
                        if (is_array($answer)) {
                            // if array, check if any matches
                            $found = in_array((string)$question->answer_key, array_map('strval', $answer), true);
                            $isCorrect = $found;
                        } else {
                            $isCorrect = ((string)$answer === (string)$question->answer_key);
                        }
                        $pointsAwarded = $isCorrect ? $pointsPossible : 0.0;
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
                    'correct_answer' => $question->answer_key,
                    'answered_at' => $examSession->studentAnswer->last_answered_at ?? null,
                    'is_correct' => $isCorrect,
                    'points_awarded' => $pointsAwarded,
                    'points_possible' => $pointsPossible,
                ];
            }
        }

        return view('admin.exam-session-detail', [
            'session' => $examSession,
            'answers' => $answers
            // 'questions' =>

        ]);
    }
}
