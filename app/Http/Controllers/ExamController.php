<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    //
    public function index(){
        // fetch all data
        $exams = Exam::where('is_global', true)->get();

        return view('admin.view_examglobal', compact('exams'));
    }

    public function create(){

        return view('admin.input_exams');
    }

    public function globalstore(Request $request){

        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'deskripsi' => 'nullable|string|max:255',
                'durasi' => 'required|integer',
                'total_soal' => 'required|integer',
                'skor' => 'required|integer',
            ]);

            $validated['is_global'] = true;
            $validated['is_active'] = true;
            $validated['subject_id'] = null;
            $validated['school_id'] = null;
            $validated['created_by'] = auth()->user()->id;
            $validated['title'] = $validated['name'];

            Exam::create($validated);

            return redirect()->route('admin.exams')->with('success', 'Data Ujian Bersama telah dibuat');
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Membuat Ujian : '.$e->getMessage()]);
        }
    }

    public function manage(Request $request, $exam){
        try{
            $ex = Exam::findOrFail($exam);

            // set session exam
            session([
                'examid' => $ex->i,
                'examname' => $ex->name,
            ]);

            return redirect()->route('admin.exams.manage.view', $ex);
        }
        catch(\Exception $e)
        {
            return redirect()->route('admin.exams')->withErrors(['error' => 'Ujian Gagal di Load : '.$e->getMessage()]);
        }
    }
}
