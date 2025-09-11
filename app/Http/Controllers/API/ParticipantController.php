<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\Student;
use Carbon\Carbon;


class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // get user data
        $student = Student::with(['user', 'school'])
            ->where('user_id', $user->id)
            ->first();

        // take exam
        $assigned = $user->preassigned()
            ->with(['exam.examType'])
            ->get()
            ->map(function ($preassigned) {
                return [
                    'exam_id' => $preassigned->exam->id,
                    'title' => $preassigned->exam->title,
                    'duration' => $preassigned->exam->duration,
                    'total_quest' => $preassigned->exam->total_quest,
                ];
            });

        return response()->json([
            'success' => true,
            'student' => $student,
            'assigned' => $assigned
        ]);
    }

    public function start(Request $request, $examId)
    {
        try {
            $user = $request->user();
            $sessionToken = bin2hex(random_bytes(16));

            $user->examSessions()->create([
                'user_id' => $user->id,
                'exam_id' => $examId,
                'session_token' => $sessionToken,
                'started_at' => Carbon::now(),
                'submited_at' => Carbon::now()->addMinutes(Exam::find($examId)->duration),
                'time_remaining' => Exam::find($examId)->duration * 60,
                'total_score' => 0,
                'status' => 'progress',
                'attempt_number' => 1,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);

            $question = Question::where('exam_id', $examId)
                ->limit(Exam::find($examId)->total_quest)
                ->get();

            return response()->json([
                'success' => true,
                'exam' => $question,
                'session_token' => $sessionToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal Load Soal" . $e->getMessage()
            ]);
        }
    }
}
