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

            // Process each question
            foreach ($questions as $question) {
                $questionId = (string)$question->id; // Convert to string since JSON keys are strings
                $answer = null;

                // Determine if it's essay or multiple choice
                if ($question->qtype_id === 2) { // Essay type
                    $answer = $essayAnswers[$questionId] ?? null;
                } else { // Multiple choice
                    $answer = $mcAnswers[$questionId] ?? null;
                }

                // Add to answers array
                $answers[] = [
                    'question_id' => $questionId,
                    'question_text' => $question->question,
                    'question_type' => $question->qtype_id,
                    'question_type_name' => $question->qtype->name ?? 'Unknown',
                    'choices' => json_decode($question->choices, true),
                    'answer' => $answer,
                    'correct_answer' => $question->answer_key,
                    'answered_at' => $examSession->studentAnswer->last_answered_at,
                    'is_correct' => $question->qtype_id === 1 ? ($answer === $question->answer_key) : null
                ];
            }
        }

        return view('admin.exam-session-detail', [
            'session' => $examSession,
            'answers' => $answers
        ]);
    }
}
