<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Examtype;
use App\Models\Grade;
use App\Models\Question;
use App\Models\QuestionTypes;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    //
    public function index(){
        // fetch all data
        $exams = Examtype::where('is_global', true)->get();
        $grade = Grade::all();

        return view('admin.view_examglobal', compact('exams', 'grade'));
    }

    public function create(){

        return view('admin.input_exams');
    }

    public function globalstore(Request $request){

        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'start' => 'required|date',
                'end' => 'required|date|after_or_equal:start',
            ]);

            $roleRoutes =  [
                'admin' => 'admin.exams',
                'super' => 'super.exams',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if(!isset($roleRoutes[$role])) {
                throw new \Exception ('Anda tidak memiliki akses untuk menambah ujian');
            }



            $validated['school_id'] = null;
            $validated['grade_id'] = null;
            $validated['title'] = $validated['name'];

            Examtype::create([
                'title' => $validated['title'],
                'school_id' => null,
                'grade_id' => null,
                'start_time' => $validated['start'],
                'end_time' => $validated['end'],
                'is_active' => true,
                'is_global' => true,
            ])->save();

            return redirect()->route($roleRoutes[$role])->with('success', 'Data Ujian Bersama telah dibuat');
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Membuat Ujian : '.$e->getMessage()]);
        }
    }

    public function manage(Request $request, $exam){
        try{
            $ex = Examtype::findOrFail($exam);

            $roleRoutes =  [
                'admin' => 'admin.exams.manage.view',
                'super' => 'super.exams.manage.view',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if(!isset($roleRoutes[$role])) {
                throw new \Exception ('Anda tidak memiliki akses untuk menambah ujian');
            }

            // set session exam
            session([
                'examid' => $ex->id,
                'examname' => $ex->title,
                'is_active' => $ex->is_active,
            ]);

            return redirect()->route($roleRoutes[$role], $ex);
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withErrors(['error' => 'Ujian Gagal di Load : '.$e->getMessage()]);
        }
    }

    public function manageView(Examtype $examtype){
        try{
            if(!session('examid')){
                throw new \Exception('Ujian Tidak ditemukan');
            }
            // dd();

            $soal = Question::where('exam_id', session('examid'))->get();
            $exam = Exam::where('exam_type_id', session('examid'))->get();

            return view('admin.manageexams', compact('soal', 'exam'));
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withErrors(['error' => 'Tidak bisa melakukan manage Ujian :'.$e->getMessage()]);
        }
    }

    public function examstore(Request $request){
        try{
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'duration' => 'required|integer',
                'total_quest' => 'required|integer',
                'score_minimal' => 'required|integer',
            ]);

            $validated['exam_type_id'] = session('examid');
            $validated['created_by'] = auth()->user()->id;
            $validated['is_active'] = true;

            $roleRoutes =  [
                'admin' => 'admin.exams.manage.view',
                'super' => 'super.exams.manage.view',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if(!isset($roleRoutes[$role])) {
                throw new \Exception ('Anda tidak memiliki akses untuk menambah ujian');
            }

            Exam::create($validated)->save();

            return redirect()->route($roleRoutes[$role], session('examid'))
                            ->with('success', 'Data Mapel Ujian berhasil ditambahkan <script>setTimeout(function(){ showTab(\'ujian\'); }, 100);</script>');
        }
        catch (\Exception $e){
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Membuat Ujian : '.$e->getMessage()]);
        }
    }

    public function examquestion(Request $request, $exam){
        try{
            $exam = Exam::findOrFail($exam);

            $roleRoutes =  [
                'admin' => 'admin.exams.manage.question',
                'super' => 'super.exams.manage.question',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if(!isset($roleRoutes[$role])) {
                throw new \Exception ('Anda tidak memiliki akses untuk menambah ujian');
            }


            session([
                'perexamid' => $exam->id,
                'perexamname' => $exam->title,
            ]);

            return redirect()->route($roleRoutes[$role], $exam);
        }
        catch(\Exception $e){
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Membuka Ujian : '.$e->getMessage()]);
        }
    }

    public function banksoal(Exam $exam){
        try{
            $questions = Question::where('exam_id', session('perexamid'))
                                    ->get();

            $schools = School::where('status', '1')->get();

            return view('admin.manageperexam', compact('questions', 'schools', 'exam'));
        }
        catch(\Exception $e){
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Membuka Ujian : '.$e->getMessage()]);
        }
    }

    public function exitbanksoal(){
        session()->forget(['perexamid', 'perexamname']);

        return redirect()->route('admin.exams.manage.view', session('examid'));
    }




}
