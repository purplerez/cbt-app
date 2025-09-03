<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\Student;
use Carbon\Carbon;


class ParticipantController extends Controller
{
    //
    public function index(Request $request){
        $user = $request->user();

        // get user data
        $student = Student::with(['user','school'])
                    ->where('user_id', $user->id)
                    ->first();

        // take exam
        $assigned = $user->preassigned()
                    ->with(['exam.examType'])
                    ->get()
                    ->map(function($preassigned){
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
            'assigned' => $assigned->get()
        ]);
    }

    public function start(Request $request){
        try{
            $user = $request->user();
            $exam_id = $request->exam_id;
            $user->examSessions()->create([
                'user_id' => $user,
                'exam_id' => $exam_id,
                'started_at' => Carbon::now(),
                'status' => 'progress',
                'attempt_number' => 1,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent')
            ]);
            
            $question = Question::where('exam_id', $exam_id)
                    ->limit(Exam::find($exam_id)->total_quest)
                    ->get();


            return response()->json([
                'success' => true,
                'exam' => $question
            ]);
        }
        catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => "Gagal Load Soal".$e->getMessage()
            ]);
        }
    }

}
