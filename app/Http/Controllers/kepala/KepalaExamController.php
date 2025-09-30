<?php

namespace App\Http\Controllers\kepala;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Examtype;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class KepalaExamController extends Controller
{
    //
    public function indexAll(){
        $exams = Examtype::all();

        return view('kepala.view_examglobal', compact('exams'));
    }

    public function manage(Request $request, $id){
        try{
            $exams = Examtype::findOrFail($id);

            session([
                'exam_id' => $exams->id,
                'exam_name' => $exams->title
            ]);

            return redirect()->route('kepala.exams.manage.view', $exams->id);
            // return view('kepala.view_exam', compact('exams'));
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withErrors(['error' => 'Ujian Gagal di Load : '.$e->getMessage()]);
        }
    }

    public function manageView(Examtype $examtype){
        try{
            if(!session('exam_id')){
                throw new \Exception('Ujian Tidak ditemukan');
            }

            $mapels = Exam::where('exam_id', session('exam_id'))->get();

            return view('kepala.manageexam', compact('mapel'));
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withErrors(['error' => 'Tidak bisa melakukan manage Ujian :'.$e->getMessage()]);
        }
    }


}
