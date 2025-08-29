<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
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

}
