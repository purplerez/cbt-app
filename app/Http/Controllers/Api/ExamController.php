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
}
