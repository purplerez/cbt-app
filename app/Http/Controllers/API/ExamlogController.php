<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExamLogResource;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamLog;
use App\Models\ExamSession;
use Illuminate\Http\Request;

class ExamlogController extends Controller
{
    //
    public function getAllLogs($userId){
        try{
            $session = ExamSession::where('user_id', $userId)
                                ->where('exam_id', session('perexamid'))
                                ->first();

            $log = ExamLog::with('examsession.user')->where('session_id', $session->id)->get();

            // Log
            return ExamLogResource::collection($log);
        }
        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stats: ' . $e->getMessage()
            ], 500);
        }
    }
}
