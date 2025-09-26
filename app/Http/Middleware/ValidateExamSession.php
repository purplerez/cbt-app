<?php

namespace App\Http\Middleware;

use App\Models\ExamSession;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateExamSession
{
     /**
      * Handle an incoming request.
      *
      * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
      */
     public function handle(Request $request, Closure $next): Response
     {
          // Check if session_token is provided
          $sessionToken = $request->input('session_token');

          if (!$sessionToken) {
               return response()->json([
                    'success' => false,
                    'message' => 'Token sesi ujian diperlukan'
               ], 422);
          }

          // Find the exam session
          $examSession = ExamSession::where('session_token', $sessionToken)
               ->where('user_id', $request->user()->id)
               ->first();

          if (!$examSession) {
               return response()->json([
                    'success' => false,
                    'message' => 'Sesi ujian tidak ditemukan'
               ], 404);
          }

          // Check if session is still valid (not completed or expired)
          if ($examSession->status === 'completed' || $examSession->status === 'auto_submitted') {
               return response()->json([
                    'success' => false,
                    'message' => 'Sesi ujian sudah selesai'
               ], 422);
          }

          // Check if exam time has expired
          if (Carbon::now()->isAfter($examSession->submited_at) && $examSession->status === 'progress') {
               return response()->json([
                    'success' => false,
                    'message' => 'Waktu ujian telah berakhir',
                    'expired' => true
               ], 422);
          }

          // Add exam session to request for use in controller
          $request->merge(['exam_session' => $examSession]);

          return $next($request);
     }
}
