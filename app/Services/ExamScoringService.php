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
 * Fitur Auto-Scoring Essay:
 * - Kesamaan 100%: Mendapat poin penuh (full points)
 * - Kesamaan 80-99%: Mendapat 80% dari poin penuh
 * - Kesamaan 60-79%: Mendapat 60% dari poin penuh
 * - Kesamaan <60%: Tidak mendapat poin (0)
 * 
 * Metode perhitungan similarity:
 * 1. Levenshtein distance (untuk jawaban pendek)
 * 2. Word matching (mencocokkan kata-kata kunci)
 * 3. PHP similar_text function
 * 
 * @version 2.0 - Auto-scoring essay implemented
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

                         if (!empty($question->answer_key)) {
                              $correctAnswer = trim($question->answer_key);
                              $studentAnswerTrimmed = trim($studentAnswer);

                              if (strcasecmp($studentAnswerTrimmed, $correctAnswer) === 0) {
                                   $isCorrect = true;
                                   $earnedPoints = $points;
                              } else {
                                   $similarity = $this->calculateAnswerSimilarity($studentAnswerTrimmed, $correctAnswer);

                                   if ($similarity >= 100) {
                                        $isCorrect = true;
                                        $earnedPoints = $points;
                                   } elseif ($similarity >= 80) {
                                        $isCorrect = false;
                                        $earnedPoints = $points * 0.8;
                                   } elseif ($similarity >= 60) {
                                        $isCorrect = false;
                                        $earnedPoints = $points * 0.6;
                                   } else {
                                        $isCorrect = false;
                                        $earnedPoints = 0;
                                   }
                              }
                         } else {
                              // If no answer key provided, essay needs manual grading
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

     /**
      * Update score breakdown by question type
      * 
      * @param array &$scoreBreakdown
      * @param int $questionType
      * @param float $points
      * @param array $result
      * @return void
      */
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

     /**
      * Calculate similarity between student answer and correct answer
      * 
      * @param string $studentAnswer
      * @param string $correctAnswer
      * @return float Similarity percentage (0-100)
      */
     private function calculateAnswerSimilarity($studentAnswer, $correctAnswer)
     {
          // Normalize both answers
          $studentAnswer = $this->normalizeText($studentAnswer);
          $correctAnswer = $this->normalizeText($correctAnswer);

          // If exact match after normalization, return 100%
          if ($studentAnswer === $correctAnswer) {
               return 100;
          }

          // Calculate similarity using multiple methods and take the highest score

          // Method 1: Levenshtein distance (for short answers)
          $levenshteinSimilarity = 0;
          if (strlen($studentAnswer) < 255 && strlen($correctAnswer) < 255) {
               $maxLength = max(strlen($studentAnswer), strlen($correctAnswer));
               if ($maxLength > 0) {
                    $distance = levenshtein($studentAnswer, $correctAnswer);
                    $levenshteinSimilarity = (1 - ($distance / $maxLength)) * 100;
               }
          }

          // Method 2: Word matching - check how many words from answer key are in student answer
          $correctWords = array_filter(explode(' ', $correctAnswer));
          $studentWords = array_filter(explode(' ', $studentAnswer));

          if (count($correctWords) > 0) {
               $matchedWords = 0;
               foreach ($correctWords as $correctWord) {
                    foreach ($studentWords as $studentWord) {
                         // Check exact match or very similar words
                         if ($correctWord === $studentWord) {
                              $matchedWords++;
                              break;
                         } elseif (strlen($correctWord) > 3 && strlen($studentWord) > 3) {
                              // For longer words, check if they are similar enough
                              similar_text($correctWord, $studentWord, $percent);
                              if ($percent >= 80) {
                                   $matchedWords++;
                                   break;
                              }
                         }
                    }
               }
               $wordMatchSimilarity = ($matchedWords / count($correctWords)) * 100;
          } else {
               $wordMatchSimilarity = 0;
          }

          // Method 3: similar_text function
          similar_text($studentAnswer, $correctAnswer, $percentSimilar);

          // Return the highest similarity score from all methods
          return max($levenshteinSimilarity, $wordMatchSimilarity, $percentSimilar);
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
