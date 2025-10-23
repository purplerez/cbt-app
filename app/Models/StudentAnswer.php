<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class StudentAnswer extends Model
{
     use HasFactory;

     protected $fillable = [
          'session_id',
          'answers',
          'essay_answers',
          'total_answered',
          'last_answered_at',
          'last_backup_at'
     ];

     protected $casts = [
          'answers' => 'array',
          'essay_answers' => 'array',
          'total_answered' => 'integer',
          'last_answered_at' => 'datetime',
          'last_backup_at' => 'datetime'
     ];

     /**
      * Relationship to ExamSession
      */
     public function session(): BelongsTo
     {
          return $this->belongsTo(ExamSession::class, 'session_id');
     }

     /**
      * Save or update student answers efficiently
      * 
      * @param int $sessionId
      * @param array $newAnswers Format: ['question_id' => 'answer', ...] or [question_id => 'answer', ...]
      * @param array $essayAnswers Format: ['question_id' => 'long_text', ...]
      * @return bool
      */
     public static function saveAnswers(int $sessionId, array $newAnswers = [], array $essayAnswers = []): bool
     {
          try {
               $studentAnswer = self::firstOrNew(['session_id' => $sessionId]);

               // Get existing answers or initialize empty object (not array)
               $existingAnswers = (array)($studentAnswer->answers ?? []);
               $existingEssays = (array)($studentAnswer->essay_answers ?? []);

               // Force conversion to associative array with string keys
               // This prevents PHP from creating indexed arrays
               foreach ($newAnswers as $qId => $ans) {
                    $existingAnswers[(string)$qId] = $ans;
               }

               foreach ($essayAnswers as $qId => $ans) {
                    $existingEssays[(string)$qId] = $ans;
               }

               // Force cast to object to ensure JSON stores as object, not array
               $studentAnswer->answers = empty($existingAnswers) ? new \stdClass() : (object)$existingAnswers;
               $studentAnswer->essay_answers = empty($existingEssays) ? new \stdClass() : (object)$existingEssays;
               $studentAnswer->total_answered = count($existingAnswers) + count($existingEssays);
               $studentAnswer->last_answered_at = now();
               $studentAnswer->last_backup_at = now();

               return $studentAnswer->save();
          } catch (\Exception $e) {
               Log::error('Failed to save student answers', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
               ]);
               return false;
          }
     }

     /**
      * Get answers in frontend-friendly format
      * 
      * @param int $sessionId
      * @return array
      */
     public static function getAnswersForFrontend(int $sessionId): array
     {
          $studentAnswer = self::where('session_id', $sessionId)->first();

          if (!$studentAnswer) {
               return [
                    'answers' => new \stdClass(), // Empty object
                    'essay_answers' => new \stdClass(), // Empty object
                    'total_answered' => 0,
                    'last_answered_at' => null,
                    'is_empty' => true
               ];
          }

          return [
               'answers' => (object)($studentAnswer->answers ?? []), // {"1":"A","2":"B","3":"C"}
               'essay_answers' => (object)($studentAnswer->essay_answers ?? []), // {"5":"Long essay text..."}
               'total_answered' => $studentAnswer->total_answered,
               'last_answered_at' => $studentAnswer->last_answered_at?->toISOString(),
               'last_backup_at' => $studentAnswer->last_backup_at?->toISOString(),
               'is_empty' => false
          ];
     }

     /**
      * Get compact format for network transfer (ultra-lightweight)
      * 
      * @param int $sessionId
      * @return string JSON string
      */
     public static function getCompactAnswers(int $sessionId): string
     {
          $studentAnswer = self::where('session_id', $sessionId)->first();

          if (!$studentAnswer || empty($studentAnswer->answers)) {
               return '{}';
          }

          // Return pure JSON string of answers for minimal data transfer
          return json_encode($studentAnswer->answers);
     }

     /**
      * Restore answers from compact JSON string
      * 
      * @param int $sessionId
      * @param string $compactJson
      * @param string|null $essayJson
      * @return bool
      */
     public static function restoreFromCompact(int $sessionId, string $compactJson, string $essayJson = null): bool
     {
          try {
               $answers = json_decode($compactJson, true);
               $essayAnswers = $essayJson ? json_decode($essayJson, true) : [];

               if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Invalid JSON in restore compact', ['session_id' => $sessionId]);
                    return false;
               }

               return self::saveAnswers($sessionId, $answers, $essayAnswers);
          } catch (\Exception $e) {
               Log::error('Failed to restore from compact', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage()
               ]);
               return false;
          }
     }

     /**
      * Update single answer (for real-time auto-save)
      * 
      * @param int $sessionId
      * @param int $questionId
      * @param string $answer
      * @param bool $isEssay
      * @return bool
      */
     public static function updateSingleAnswer(int $sessionId, int $questionId, string $answer, bool $isEssay = false): bool
     {
          try {
               $studentAnswer = self::firstOrNew(['session_id' => $sessionId]);

               // Get existing answers or initialize empty array
               $existingAnswers = (array)($studentAnswer->answers ?? []);
               $existingEssays = (array)($studentAnswer->essay_answers ?? []);

               if ($isEssay) {
                    // Update or add essay answer with question_id as string key
                    $existingEssays[(string)$questionId] = $answer;
               } else {
                    // Update or add multiple choice answer with question_id as string key
                    $existingAnswers[(string)$questionId] = $answer;
               }

               // Always update both fields to prevent null issues
               $studentAnswer->answers = empty($existingAnswers) ? new \stdClass() : (object)$existingAnswers;
               $studentAnswer->essay_answers = empty($existingEssays) ? new \stdClass() : (object)$existingEssays;

               // Recalculate total answered (count unique question IDs)
               $studentAnswer->total_answered = count($existingAnswers) + count($existingEssays);
               $studentAnswer->last_answered_at = now();
               $studentAnswer->last_backup_at = now();

               return $studentAnswer->save();
          } catch (\Exception $e) {
               Log::error('Failed to update single answer', [
                    'session_id' => $sessionId,
                    'question_id' => $questionId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
               ]);
               return false;
          }
     }

     /**
      * Clear all answers for a session (reset functionality)
      * 
      * @param int $sessionId
      * @return bool
      */
     public static function clearAnswers(int $sessionId): bool
     {
          try {
               $studentAnswer = self::where('session_id', $sessionId)->first();

               if ($studentAnswer) {
                    $studentAnswer->answers = new \stdClass(); // Empty object
                    $studentAnswer->essay_answers = new \stdClass(); // Empty object
                    $studentAnswer->total_answered = 0;
                    $studentAnswer->last_answered_at = null;
                    $studentAnswer->last_backup_at = now();

                    return $studentAnswer->save();
               }

               return true;
          } catch (\Exception $e) {
               Log::error('Failed to clear student answers', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage()
               ]);
               return false;
          }
     }

     /**
      * Get answer statistics
      * 
      * @param int $sessionId
      * @return array
      */
     public static function getAnswerStats(int $sessionId): array
     {
          $studentAnswer = self::where('session_id', $sessionId)->first();

          if (!$studentAnswer) {
               return [
                    'total_answered' => 0,
                    'multiple_choice_count' => 0,
                    'essay_count' => 0,
                    'completion_percentage' => 0
               ];
          }

          $answers = (array)($studentAnswer->answers ?? []);
          $essays = (array)($studentAnswer->essay_answers ?? []);

          $multipleChoiceCount = count($answers);
          $essayCount = count($essays);

          return [
               'total_answered' => $studentAnswer->total_answered,
               'multiple_choice_count' => $multipleChoiceCount,
               'essay_count' => $essayCount,
               'completion_percentage' => 0, // This should be calculated based on total questions in exam
               'last_activity' => $studentAnswer->last_answered_at?->toISOString()
          ];
     }

     /**
      * Scope for getting recent backups
      */
     public function scopeRecentBackups($query, int $hours = 24)
     {
          return $query->where('last_backup_at', '>=', now()->subHours($hours));
     }
}
