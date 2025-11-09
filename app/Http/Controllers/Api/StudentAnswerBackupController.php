<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentAnswer;
use App\Models\ExamSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class StudentAnswerBackupController extends Controller
{
     public function saveAnswers(Request $request): JsonResponse
     {
          $validator = Validator::make($request->all(), [
               'session_id' => 'required|integer|exists:exam_sessions,id',
               'answers' => 'sometimes|array', // {"1":"A","2":"B","3":"C"}
               'answers.*' => 'string|max:1', // A, B, C, D for multiple choice
               'essay_answers' => 'sometimes|array', // {"5":"Long text..."}
               'essay_answers.*' => 'string|max:65535' // For essay questions
          ]);

          if ($validator->fails()) {
               return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
               ], 422);
          }

          $sessionId = $request->session_id;
          $answers = $request->get('answers', []);
          $essayAnswers = $request->get('essay_answers', []);

          try {
               // Verify session belongs to authenticated user
               $session = ExamSession::where('id', $sessionId)
                    ->where('user_id', Auth::id())
                    ->where('status', 'progress')
                    ->first();

               if (!$session) {
                    return response()->json([
                         'success' => false,
                         'message' => 'Invalid session or session has ended'
                    ], 403);
               }

               // Save answers using the new efficient method
               $saved = StudentAnswer::saveAnswers($sessionId, $answers, $essayAnswers);

               if ($saved) {
                    // Update session timestamp to track last activity
                    $session->touch();

                    return response()->json([
                         'success' => true,
                         'message' => 'Answers saved successfully',
                         'data' => [
                              'session_id' => $sessionId,
                              'multiple_choice_count' => count($answers),
                              'essay_count' => count($essayAnswers),
                              'total_saved' => count($answers) + count($essayAnswers),
                              'saved_at' => now()->toISOString()
                         ]
                    ]);
               } else {
                    return response()->json([
                         'success' => false,
                         'message' => 'Failed to save answers'
                    ], 500);
               }
          } catch (\Exception $e) {
               Log::error('Failed to save student answers', [
                    'session_id' => $sessionId,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
               ]);

               return response()->json([
                    'success' => false,
                    'message' => 'Failed to save answers. Please try again.'
               ], 500);
          }
     }

     public function getAnswers(Request $request, int $sessionId): JsonResponse
     {
          try {
               $session = ExamSession::where('id', $sessionId)
                    ->where('user_id', Auth::id())
                    ->first();

               if (!$session) {
                    return response()->json([
                         'success' => false,
                         'message' => 'Session not found or access denied'
                    ], 404);
               }

               $answersData = StudentAnswer::getAnswersForFrontend($sessionId);
               $compact = $request->get('compact', false);
               $compactData = [];

               if ($compact) {
                    $compactData = [
                         'compact_answers' => StudentAnswer::getCompactAnswers($sessionId),
                         'size_bytes' => strlen(StudentAnswer::getCompactAnswers($sessionId))
                    ];
               }

               return response()->json([
                    'success' => true,
                    'data' => array_merge([
                         'session_id' => $sessionId,
                         'session_status' => $session->status,
                         'exam_title' => $session->exam->title ?? 'Unknown',
                         'retrieved_at' => now()->toISOString()
                    ], $answersData, $compactData)
               ]);
          } catch (\Exception $e) {
               Log::error('Failed to retrieve student answers', [
                    'session_id' => $sessionId,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
               ]);

               return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve answers'
               ], 500);
          }
     }

     public function restoreAnswers(Request $request): JsonResponse
     {
          $validator = Validator::make($request->all(), [
               'session_id' => 'required|integer|exists:exam_sessions,id',
               'answers_json' => 'required|string', // JSON string: {"1":"A","2":"B"}
               'essay_json' => 'sometimes|string' // JSON string: {"5":"Essay text"}
          ]);

          if ($validator->fails()) {
               return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
               ], 422);
          }

          $sessionId = $request->session_id;
          $answersJson = $request->answers_json;
          $essayJson = $request->get('essay_json', '{}');

          try {
               // Verify session belongs to authenticated user and is resumable
               $session = ExamSession::where('id', $sessionId)
                    ->where('user_id', Auth::id())
                    ->whereIn('status', ['progress', 'expired'])
                    ->first();

               if (!$session) {
                    return response()->json([
                         'success' => false,
                         'message' => 'Session not found or cannot be resumed'
                    ], 403);
               }

               // Restore answers using compact JSON format
               $restored = StudentAnswer::restoreFromCompact($sessionId, $answersJson, $essayJson);

               if ($restored) {
                    // Update session status if it was expired
                    if ($session->status === 'expired') {
                         $session->status = 'progress';
                         $session->save();
                    }

                    // Get stats after restore
                    $stats = StudentAnswer::getAnswerStats($sessionId);

                    return response()->json([
                         'success' => true,
                         'message' => 'Answers restored successfully',
                         'data' => [
                              'session_id' => $sessionId,
                              'session_status' => $session->fresh()->status,
                              'restored_at' => now()->toISOString(),
                              'stats' => $stats
                         ]
                    ]);
               } else {
                    return response()->json([
                         'success' => false,
                         'message' => 'Failed to restore answers'
                    ], 500);
               }
          } catch (\Exception $e) {
               Log::error('Failed to restore student answers', [
                    'session_id' => $sessionId,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
               ]);

               return response()->json([
                    'success' => false,
                    'message' => 'Failed to restore answers. Please try again.'
               ], 500);
          }
     }

     public function updateSingleAnswer(Request $request): JsonResponse
     {
          $validator = Validator::make($request->all(), [
               'session_id' => 'required|integer|exists:exam_sessions,id',
               'question_id' => 'required|integer',
               'answer' => 'required|string',
               'type' => 'required|in:choice,essay'
          ]);

          if ($validator->fails()) {
               return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
               ], 422);
          }

          try {
               $sessionId = $request->session_id;
               $questionId = $request->question_id;
               $answer = $request->answer;
               $isEssay = $request->type === 'essay';

               // Verify session belongs to authenticated user
               $session = ExamSession::where('id', $sessionId)
                    ->where('user_id', Auth::id())
                    ->where('status', 'progress')
                    ->first();

               if (!$session) {
                    return response()->json([
                         'success' => false,
                         'message' => 'Invalid session'
                    ], 403);
               }

               // Update single answer
               $updated = StudentAnswer::updateSingleAnswer($sessionId, $questionId, $answer, $isEssay);

               if ($updated) {
                    $session->touch();

                    return response()->json([
                         'success' => true,
                         'message' => 'Answer updated successfully',
                         'data' => [
                              'session_id' => $sessionId,
                              'question_id' => $questionId,
                              'answer' => $answer,
                              'type' => $request->type,
                              'updated_at' => now()->toISOString()
                         ]
                    ]);
               } else {
                    return response()->json([
                         'success' => false,
                         'message' => 'Failed to update answer'
                    ], 500);
               }
          } catch (\Exception $e) {
               Log::error('Failed to update single answer', [
                    'session_id' => $request->session_id,
                    'question_id' => $request->question_id,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
               ]);

               return response()->json([
                    'success' => false,
                    'message' => 'Failed to update answer'
               ], 500);
          }
     }

     public function getSessionProgress(int $sessionId): JsonResponse
     {
          try {
               $session = ExamSession::where('id', $sessionId)
                    ->where('user_id', Auth::id())
                    ->with('exam:id,title,duration,total_questions')
                    ->first();

               if (!$session) {
                    return response()->json([
                         'success' => false,
                         'message' => 'Session not found'
                    ], 404);
               }

               $stats = StudentAnswer::getAnswerStats($sessionId);
               $totalQuestions = $session->exam->total_questions ?? 0;
               $progressPercent = $totalQuestions > 0 ? round(($stats['total_answered'] / $totalQuestions) * 100, 2) : 0;

               return response()->json([
                    'success' => true,
                    'data' => [
                         'session_id' => $sessionId,
                         'exam_title' => $session->exam->title,
                         'status' => $session->status,
                         'total_questions' => $totalQuestions,
                         'progress_percent' => $progressPercent,
                         'time_remaining' => $session->time_remaining,
                         'started_at' => $session->started_at->toISOString(),
                         'last_activity' => $session->updated_at->toISOString(),
                         'answer_stats' => $stats
                    ]
               ]);
          } catch (\Exception $e) {
               Log::error('Failed to get session progress', [
                    'session_id' => $sessionId,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
               ]);

               return response()->json([
                    'success' => false,
                    'message' => 'Failed to get session progress'
               ], 500);
          }
     }

     public function getCompactAnswers(int $sessionId): JsonResponse
     {
          try {
               $session = ExamSession::where('id', $sessionId)
                    ->where('user_id', Auth::id())
                    ->first();

               if (!$session) {
                    return response()->json([
                         'success' => false,
                         'message' => 'Session not found'
                    ], 404);
               }

               $compactAnswers = StudentAnswer::getCompactAnswers($sessionId);

               return response()->json([
                    'success' => true,
                    'data' => [
                         'session_id' => $sessionId,
                         'answers' => $compactAnswers, // Raw JSON string
                         'size_bytes' => strlen($compactAnswers),
                         'retrieved_at' => now()->toISOString()
                    ]
               ]);
          } catch (\Exception $e) {
               Log::error('Failed to get compact answers', [
                    'session_id' => $sessionId,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
               ]);

               return response()->json([
                    'success' => false,
                    'message' => 'Failed to get compact answers'
               ], 500);
          }
     }
}
