<?php

namespace App\Listeners;

use App\Events\ExamSubmitted;
use App\Models\ExamLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HandleExamSubmission implements ShouldQueue
{
     use InteractsWithQueue;

     /**
      * Create the event listener.
      */
     public function __construct()
     {
          //
     }

     /**
      * Handle the event.
      */
     public function handle(ExamSubmitted $event): void
     {
          try {
               $examSession = $event->examSession;
               $scoreDetails = $event->scoreDetails;

               // Log detailed submission info
               $this->logDetailedSubmission($examSession, $scoreDetails);

               // Send notification to admin (if needed)
               $this->notifyAdminIfNeeded($examSession, $scoreDetails);

               // Generate certificate (if passing grade)
               $this->generateCertificateIfPassing($examSession, $scoreDetails);

               // Update student statistics
               $this->updateStudentStatistics($examSession, $scoreDetails);

               Log::info('Exam submission processed successfully', [
                    'session_id' => $examSession->id,
                    'user_id' => $examSession->user_id,
                    'score' => $scoreDetails['total_score']
               ]);
          } catch (\Exception $e) {
               Log::error('Failed to process exam submission', [
                    'session_id' => $event->examSession->id,
                    'error' => $e->getMessage()
               ]);
          }
     }

     /**
      * Log detailed submission information
      */
     private function logDetailedSubmission($examSession, $scoreDetails)
     {
          ExamLog::create([
               'session_id' => $examSession->id,
               'action' => 'exam_completed',
               'details' => [
                    'user_id' => $examSession->user_id,
                    'score_details' => $scoreDetails,
                    'exam_duration' => $examSession->started_at->diffInMinutes($examSession->submited_at),
                    'completion_rate' => ($scoreDetails['answered_questions'] / $scoreDetails['total_questions']) * 100,
                    'processed_at' => now()->toISOString()
               ],
               'ip_address' => request()->ip(),
               'user_agent' => request()->header('User-Agent'),
               'created_at' => now()
          ]);
     }

     /**
      * Notify admin if score is suspicious or exceptional
      */
     private function notifyAdminIfNeeded($examSession, $scoreDetails)
     {
          // Notify if perfect score or very low score
          if ($scoreDetails['percentage'] == 100 || $scoreDetails['percentage'] < 30) {
               // You can implement email notification here
               Log::info('Exceptional score detected', [
                    'session_id' => $examSession->id,
                    'score' => $scoreDetails['percentage']
               ]);
          }
     }

     /**
      * Generate certificate for passing students
      */
     private function generateCertificateIfPassing($examSession, $scoreDetails)
     {
          if ($scoreDetails['percentage'] >= 70) { // Passing grade
               // You can implement certificate generation here
               Log::info('Student passed exam', [
                    'session_id' => $examSession->id,
                    'score' => $scoreDetails['percentage']
               ]);
          }
     }

     /**
      * Update student overall statistics
      */
     private function updateStudentStatistics($examSession, $scoreDetails)
     {
          // You can implement student statistics update here
          // For example, update total exams taken, average score, etc.
     }
}
