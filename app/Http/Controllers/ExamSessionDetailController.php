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
        // Load the session with related student answer and exam
        $examSession->load(['student', 'exam', 'studentAnswer']);

        $answers = [];
        if ($examSession->studentAnswer) {
            // Get all answers
            $mcAnswers = (array)($examSession->studentAnswer->answers ?? []);
            $essayAnswers = (array)($examSession->studentAnswer->essay_answers ?? []);

            // Get all questions for this exam
            $questions = Question::where('exam_id', $examSession->exam_id)
                ->orderBy('id')
                ->get()
                ->keyBy('id');

            // Combine questions with answers
            foreach ($questions as $id => $question) {
                $answers[] = [
                    'question_id' => $id,
                    'question_text' => $question->question,
                    'question_type' => $question->qtype_id,
                    'choices' => json_decode($question->choices, true),
                    'answer' => $mcAnswers[$id] ?? $essayAnswers[$id] ?? null,
                    'correct_answer' => $question->answer_key,
                    'answered_at' => $examSession->studentAnswer->last_answered_at,
                ];
            }
        }

        return view('admin.exam-session-detail', [
            'session' => $examSession,
            'answers' => $answers
        ]);
    }
}
