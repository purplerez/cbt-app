<?php

namespace App\Helpers;

class AnswerKeyHelper
{
     /**
      * Convert choice index (0,1,2...) to letter (A,B,C...)
      *
      * @param int $index
      * @return string
      */
     public static function indexToLetter($index)
     {
          if (!is_numeric($index)) {
               return strtoupper(trim($index));
          }

          $index = (int)$index;
          return chr(65 + $index); // 65 is ASCII for 'A'
     }

     /**
      * Convert letter (A,B,C...) to choice index (0,1,2...)
      *
      * @param string $letter
      * @return int
      */
     public static function letterToIndex($letter)
     {
          $letter = strtoupper(trim($letter));

          // Handle True/False
          if ($letter === 'T') {
               return 0;
          }
          if ($letter === 'F') {
               return 1;
          }

          return ord($letter) - 65; // 65 is ASCII for 'A'
     }

     /**
      * Convert answer key from letter format to index format
      * Used for frontend compatibility or when indices are needed
      *
      * @param mixed $answerKey - Can be string, array, or JSON string
      * @return int|array - Answer key in index format
      */
     public static function letterToIndexFormat($answerKey)
     {
          // Handle null or empty
          if ($answerKey === null || $answerKey === '') {
               return null;
          }

          // If it's a JSON string, decode it first
          if (is_string($answerKey) && (strpos($answerKey, '[') === 0)) {
               $decoded = json_decode($answerKey, true);
               if (json_last_error() === JSON_ERROR_NONE) {
                    $answerKey = $decoded;
               }
          }

          // Handle array
          if (is_array($answerKey)) {
               return array_map([self::class, 'letterToIndex'], $answerKey);
          }

          // Handle single value
          return self::letterToIndex($answerKey);
     }

     /**
      * Normalize answer key to letter format
      * Handles both index-based and letter-based answer keys
      *
      * @param mixed $answerKey - Can be string, array, or JSON string
      * @param int|null $questionType - Question type for True/False conversion
      * @return string|array - Normalized answer key in letter format
      */
     public static function normalizeAnswerKey($answerKey, $questionType = null)
     {
          // Handle null or empty (but not 0 which is valid index)
          if ($answerKey === null || $answerKey === '') {
               return '';
          }

          // If it's a JSON string, decode it first
          if (is_string($answerKey) && (strpos($answerKey, '[') === 0 || strpos($answerKey, '{') === 0 || strpos($answerKey, '"') === 0)) {
               $decoded = json_decode($answerKey, true);
               if (json_last_error() === JSON_ERROR_NONE) {
                    $answerKey = $decoded;
               }
          }

          // Handle array (complex multiple choice)
          if (is_array($answerKey)) {
               $normalized = [];
               foreach ($answerKey as $key) {
                    if (is_numeric($key)) {
                         // For True/False (type 2), convert 0 to T, 1 to F
                         if ($questionType == 2) {
                              $normalized[] = $key == 0 ? 'T' : 'F';
                         } else {
                              $normalized[] = self::indexToLetter((int)$key);
                         }
                    } else {
                         $normalized[] = strtoupper(trim($key));
                    }
               }
               // Sort array for consistent comparison
               sort($normalized);

               // If array only has 1 element, return as string for single choice
               if (count($normalized) === 1) {
                    return $normalized[0];
               }

               return $normalized;
          }

          // Handle single value (string or number)
          if (is_numeric($answerKey)) {
               // For True/False (type 2), convert 0 to T, 1 to F
               if ($questionType == 2) {
                    return $answerKey == 0 ? 'T' : 'F';
               }
               return self::indexToLetter((int)$answerKey);
          }

          // Already a letter, just normalize
          return strtoupper(trim($answerKey));
     }

     /**
      * Normalize student answer to letter format
      *
      * @param mixed $studentAnswer
      * @return string|array|null
      */
     public static function normalizeStudentAnswer($studentAnswer)
     {
          // Handle null or empty
          if ($studentAnswer === null || $studentAnswer === '') {
               return null;
          }

          // If it's a JSON string, decode it first
          if (is_string($studentAnswer) && (strpos($studentAnswer, '[') === 0 || strpos($studentAnswer, '{') === 0)) {
               $decoded = json_decode($studentAnswer, true);
               if (json_last_error() === JSON_ERROR_NONE) {
                    $studentAnswer = $decoded;
               }
          }

          // Handle comma-separated string (e.g., "A,C" for complex multiple choice)
          if (is_string($studentAnswer) && strpos($studentAnswer, ',') !== false) {
               $studentAnswer = array_map('trim', explode(',', $studentAnswer));
          }

          // Handle array (complex multiple choice)
          if (is_array($studentAnswer)) {
               $normalized = [];
               foreach ($studentAnswer as $answer) {
                    if (is_numeric($answer)) {
                         $normalized[] = self::indexToLetter((int)$answer);
                    } else {
                         $normalized[] = strtoupper(trim($answer));
                    }
               }
               // Sort array for consistent comparison
               sort($normalized);
               return $normalized;
          }

          // Handle single value
          if (is_numeric($studentAnswer)) {
               return self::indexToLetter((int)$studentAnswer);
          }

          // Already a letter, just normalize
          return strtoupper(trim($studentAnswer));
     }

     /**
      * Compare student answer with correct answer key
      * Both will be normalized to letter format before comparison
      *
      * @param mixed $studentAnswer
      * @param mixed $correctAnswer
      * @return bool
      */
     public static function compareAnswers($studentAnswer, $correctAnswer)
     {
          $normalizedStudent = self::normalizeStudentAnswer($studentAnswer);
          $normalizedCorrect = self::normalizeAnswerKey($correctAnswer);

          // Handle null student answer
          if ($normalizedStudent === null) {
               return false;
          }

          // Handle empty correct answer
          if (empty($normalizedCorrect)) {
               return false;
          }

          // Array comparison (complex multiple choice)
          if (is_array($normalizedStudent) && is_array($normalizedCorrect)) {
               return $normalizedStudent === $normalizedCorrect;
          }

          // Single value comparison
          return $normalizedStudent === $normalizedCorrect;
     }

     /**
      * Calculate partial score for complex multiple choice
      * Awards points proportionally based on correct matches
      *
      * @param mixed $studentAnswer
      * @param mixed $correctAnswer
      * @param float $totalPoints
      * @return array ['points' => float, 'is_correct' => bool, 'matched' => int, 'total' => int]
      */
     public static function calculatePartialScore($studentAnswer, $correctAnswer, $totalPoints)
     {
          $normalizedStudent = self::normalizeStudentAnswer($studentAnswer);
          $normalizedCorrect = self::normalizeAnswerKey($correctAnswer);

          // Convert to arrays if not already
          $studentArray = is_array($normalizedStudent) ? $normalizedStudent : [$normalizedStudent];
          $correctArray = is_array($normalizedCorrect) ? $normalizedCorrect : [$normalizedCorrect];

          // Remove empty values
          $studentArray = array_filter($studentArray);
          $correctArray = array_filter($correctArray);

        //   dd($studentArray, $correctArray);

          // Calculate matches
          $matches = array_intersect($correctArray, $studentArray);

          $matchedCount = count($matches);
          $totalCorrect = count($correctArray);
            dd($studentArray, $correctArray, $matches, $matchedCount, $totalCorrect);
          // Calculate points
          $perAnswerPoint = $totalCorrect > 0 ? ($totalPoints / $totalCorrect) : 0;
          $earnedPoints = $perAnswerPoint * $matchedCount;

          // Full credit only if all correct answers are selected
          $isFullyCorrect = ($matchedCount === $totalCorrect && count($studentArray) === $totalCorrect);

          return [
               'points' => round($earnedPoints, 2),
               'is_correct' => $isFullyCorrect,
               'matched' => $matchedCount,
               'total' => $totalCorrect,
               'percentage' => $totalCorrect > 0 ? round(($matchedCount / $totalCorrect) * 100, 2) : 0
          ];
     }
}
