<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    //
    public function index(){
        // fetch all data
        $exams = Exam::where('is_global','=', true)->get();

        return view('admin.view_examglobal', compact('exams'));
    }
}
