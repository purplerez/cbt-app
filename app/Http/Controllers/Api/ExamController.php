<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExamResource;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    //
    public function index()
    {
        $exam = Exam::get();

        if(count($exam) > 0 && $exam ){
            return ExamResource::collection($exam);
        }
        else {
            return response()->json([
                'message' => 'No exams found'
            ], 200);
        }
        // return response()->json(['message' => 'ExamController index method']);
    }

    public function getParticipantLogs($examId, $userId)
    {
        try {
            $session = \App\Models\ExamSession::where('user_id', $userId)
                ->where('exam_id', $examId)
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi ujian peserta tidak ditemukan'
                ], 404);
            }

            $logs = \App\Models\ExamLog::with('examsession.user')
                ->where('session_id', $session->id)
                ->get();

            return \App\Http\Resources\ExamLogResource::collection($logs);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching logs: ' . $e->getMessage()
            ], 500);
        }
    }
}
