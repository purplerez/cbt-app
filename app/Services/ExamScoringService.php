<?php

namespace App\Services;

use App\Models\Question;
use App\Models\ExamSession;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\Log;

/**
 * ExamScoringService
 * 
 * Service untuk menghitung skor ujian dengan dukungan penilaian otomatis
 * untuk semua tipe soal termasuk essay.
 * 
 * Fitur Auto-Scoring Essay (Keyword-Based):
 * - Sistem akan mengecek apakah SEMUA keyword dari answer_key ada di jawaban siswa
 * - Tidak peduli urutan kata, case sensitivity (UPPER/lower/CamelCase), atau ada kalimat tambahan
 * - Jika SEMUA keyword ditemukan: Dapat poin PENUH ✅
 * - Jika ada keyword yang hilang: Tidak dapat poin (0) ❌
 * 
 * Contoh:
 * Answer Key: "Jakarta ibukota Indonesia"
 * ✅ "Jakarta adalah ibukota negara Indonesia" → BENAR (dapat poin penuh)
 * ✅ "Ibukota Indonesia adalah Jakarta" → BENAR (dapat poin penuh)
 * ✅ "JAKARTA merupakan IBUKOTA dari INDONESIA" → BENAR (dapat poin penuh)
 * ❌ "Jakarta adalah kota besar" → SALAH (keyword "ibukota" dan "indonesia" tidak ada)
 * 
 * Metode perhitungan:
 * - Keyword matching dengan normalisasi text (lowercase, remove punctuation)
 * - Persentase = (jumlah keyword yang ditemukan / total keyword) * 100
 * - 100% = semua keyword ada = dapat poin penuh
 * 
 * @version 2.1 - Keyword-based essay scoring implemented
 * @date November 9, 2025
 */
class ExamScoringService
{
     /**
      * Calculate comprehensive exam score
      * 
      * @param \Illuminate\Database\Eloquent\Collection $questions
      * @param array $answers
      * @param array $essayAnswers
      * @return array
      */
     public function calculateScore($questions, $answers, $essayAnswers)
     {
          $totalScore = 0;
          $maxScore = 0;
          $answeredQuestions = 0;
          $scoreBreakdown = [
               'multiple_choice' => ['correct' => 0, 'total' => 0, 'score' => 0, 'max_score' => 0],
               'essay' => ['answered' => 0, 'correct' => 0, 'total' => 0, 'score' => 0, 'max_score' => 0],
               'true_false' => ['correct' => 0, 'total' => 0, 'score' => 0, 'max_score' => 0],
               'complex_multiple' => ['correct' => 0, 'total' => 0, 'score' => 0, 'max_score' => 0]
          ];

          $detailedResults = [];

          foreach ($questions as $question) {
               $questionId = (string)$question->id;
               $questionType = (int)$question->question_type_id;
               $points = (float)$question->points;
               $maxScore += $points;

               $result = $this->scoreQuestion($question, $answers, $essayAnswers);

               $detailedResults[] = [
                    'question_id' => $question->id,
                    'question_type' => $questionType,
                    'question_type_label' => $question->getQuestionTypeLabelAttribute(),
                    'points' => $points,
                    'earned_points' => $result['earned_points'],
                    'is_correct' => $result['is_correct'],
                    'is_answered' => $result['is_answered'],
                    'student_answer' => $result['student_answer'],
                    'correct_answer' => $question->answer_key
               ];

               if ($result['is_answered']) {
                    $answeredQuestions++;
               }

               $totalScore += $result['earned_points'];

               // Update breakdown based on question type
               $this->updateScoreBreakdown($scoreBreakdown, $questionType, $points, $result);
          }

          $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0;
          $grade = $this->calculateGrade($percentage);

          return [
               'total_score' => round($totalScore, 2),
               'max_score' => round($maxScore, 2),
               'percentage' => $percentage,
               'grade' => $grade,
               'total_questions' => $questions->count(),
               'answered_questions' => $answeredQuestions,
               'unanswered_questions' => $questions->count() - $answeredQuestions,
               'score_breakdown' => $scoreBreakdown,
               'detailed_results' => $detailedResults
          ];
     }

     /**
      * Score individual question
      * 
      * @param Question $question
      * @param array $answers
      * @param array $essayAnswers
      * @return array
      */
     private function scoreQuestion($question, $answers, $essayAnswers)
     {
          $questionId = (string)$question->id;
          $questionType = (int)$question->question_type_id;
          $points = (float)$question->points;

          $isAnswered = false;
          $isCorrect = false;
          $earnedPoints = 0;
          $studentAnswer = null;

          switch ($questionType) {
               case 0: // Multiple Choice
               case 1: // Complex Multiple Choice
               case 2: // True/False
                    if (isset($answers[$questionId]) && !empty(trim($answers[$questionId]))) {
                         $isAnswered = true;
                         $studentAnswer = trim(strtoupper($answers[$questionId]));
                         $correctAnswer = trim(strtoupper($question->answer_key));

                         if ($studentAnswer === $correctAnswer) {
                              $isCorrect = true;
                              $earnedPoints = $points;
                         }
                    }
                    break;

               case 3: // Essay
                    if (isset($essayAnswers[$questionId]) && !empty(trim($essayAnswers[$questionId]))) {
                         $isAnswered = true;
                         $studentAnswer = $essayAnswers[$questionId];

                         // Auto-scoring for essay based on keyword matching
                         if (!empty($question->answer_key)) {
                              $correctAnswer = trim($question->answer_key);
                              $studentAnswerTrimmed = trim($studentAnswer);

                              $keywordMatch = $this->calculateAnswerSimilarity($studentAnswerTrimmed, $correctAnswer);

                              if ($keywordMatch >= 100) {
                                   $isCorrect = true;
                                   $earnedPoints = $points;
                              } else {
                                   $isCorrect = false;
                                   $earnedPoints = 0;
                              }
                         } else {
                              $earnedPoints = 0;
                         }
                    }
                    break;
          }

          return [
               'is_answered' => $isAnswered,
               'is_correct' => $isCorrect,
               'earned_points' => $earnedPoints,
               'student_answer' => $studentAnswer
          ];
     }

     private function updateScoreBreakdown(&$scoreBreakdown, $questionType, $points, $result)
     {
          switch ($questionType) {
               case 0: // Multiple Choice
                    $scoreBreakdown['multiple_choice']['total']++;
                    $scoreBreakdown['multiple_choice']['max_score'] += $points;
                    if ($result['is_correct']) {
                         $scoreBreakdown['multiple_choice']['correct']++;
                    }
                    $scoreBreakdown['multiple_choice']['score'] += $result['earned_points'];
                    break;

               case 1: // Complex Multiple Choice
                    $scoreBreakdown['complex_multiple']['total']++;
                    $scoreBreakdown['complex_multiple']['max_score'] += $points;
                    if ($result['is_correct']) {
                         $scoreBreakdown['complex_multiple']['correct']++;
                    }
                    $scoreBreakdown['complex_multiple']['score'] += $result['earned_points'];
                    break;

               case 2: // True/False
                    $scoreBreakdown['true_false']['total']++;
                    $scoreBreakdown['true_false']['max_score'] += $points;
                    if ($result['is_correct']) {
                         $scoreBreakdown['true_false']['correct']++;
                    }
                    $scoreBreakdown['true_false']['score'] += $result['earned_points'];
                    break;

               case 3: // Essay
                    $scoreBreakdown['essay']['total']++;
                    $scoreBreakdown['essay']['max_score'] += $points;
                    if ($result['is_answered']) {
                         $scoreBreakdown['essay']['answered']++;
                    }
                    if ($result['is_correct']) {
                         $scoreBreakdown['essay']['correct'] = ($scoreBreakdown['essay']['correct'] ?? 0) + 1;
                    }
                    $scoreBreakdown['essay']['score'] += $result['earned_points'];
                    break;
          }
     }

     private function calculateAnswerSimilarity($studentAnswer, $correctAnswer)
     {
          // Normalize both answers (lowercase, remove extra spaces and punctuation)
          $studentAnswerNormalized = $this->normalizeText($studentAnswer);
          $correctAnswerNormalized = $this->normalizeText($correctAnswer);

          if ($studentAnswerNormalized === $correctAnswerNormalized) {
               return 100;
          }

          $keywords = array_filter(explode(' ', $correctAnswerNormalized));

          if (empty($keywords)) {
               return 0;
          }

          $matchedKeywords = 0;
          $totalKeywords = count($keywords);

          foreach ($keywords as $keyword) {
               if (strpos($studentAnswerNormalized, $keyword) !== false) {
                    $matchedKeywords++;
               }
          }

          $matchPercentage = ($matchedKeywords / $totalKeywords) * 100;

          return $matchPercentage;
     }

     /**
      * Normalize text for comparison
      * 
      * @param string $text
      * @return string
      */
     private function normalizeText($text)
     {
          // Convert to lowercase
          $text = mb_strtolower($text, 'UTF-8');

          // Remove extra whitespaces
          $text = preg_replace('/\s+/', ' ', $text);

          // Remove punctuation
          $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);

          // Trim
          $text = trim($text);

          return $text;
     }

     /**
      * Calculate letter grade based on percentage
      * 
      * @param float $percentage
      * @return array
      */
     private function calculateGrade($percentage)
     {
          if ($percentage >= 90) {
               return ['letter' => 'A', 'description' => 'Sangat Baik'];
          } elseif ($percentage >= 80) {
               return ['letter' => 'B', 'description' => 'Baik'];
          } elseif ($percentage >= 70) {
               return ['letter' => 'C', 'description' => 'Cukup'];
          } elseif ($percentage >= 60) {
               return ['letter' => 'D', 'description' => 'Kurang'];
          } else {
               return ['letter' => 'E', 'description' => 'Sangat Kurang'];
          }
     }

     /**
      * Get exam statistics for admin
      * 
      * @param int $examId
      * @return array
      */
     public function getExamStatistics($examId)
     {
          $sessions = ExamSession::where('exam_id', $examId)
               ->where('status', 'submited')
               ->get();

          if ($sessions->isEmpty()) {
               return [
                    'total_participants' => 0,
                    'average_score' => 0,
                    'highest_score' => 0,
                    'lowest_score' => 0,
                    'pass_rate' => 0,
                    'grade_distribution' => []
               ];
          }

          $scores = $sessions->pluck('total_score');
          $averageScore = $scores->avg();
          $highestScore = $scores->max();
          $lowestScore = $scores->min();

          // Calculate pass rate (assuming 60% is passing)
          $passingScores = $scores->filter(function ($score) {
               return $score >= 60;
          });
          $passRate = ($passingScores->count() / $sessions->count()) * 100;

          // Grade distribution
          $gradeDistribution = [];
          foreach ($sessions as $session) {
               $percentage = $session->total_score;
               $grade = $this->calculateGrade($percentage);
               $gradeDistribution[$grade['letter']] = ($gradeDistribution[$grade['letter']] ?? 0) + 1;
          }

          return [
               'total_participants' => $sessions->count(),
               'average_score' => round($averageScore, 2),
               'highest_score' => round($highestScore, 2),
               'lowest_score' => round($lowestScore, 2),
               'pass_rate' => round($passRate, 2),
               'grade_distribution' => $gradeDistribution
          ];
     }
}
