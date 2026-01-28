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

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') membuat ujian global : '.$validated['name']);


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
            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') gagal membuat ujian : '.$request->name);

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

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Berhasil membuat mata pelajaran ujian : '.$validated['title']);


            return redirect()->route($roleRoutes[$role], session('examid'))
                            ->with('success', 'Data Mapel Ujian berhasil ditambahkan <script>setTimeout(function(){ showTab(\'ujian\'); }, 100);</script>');
        }
        catch (\Exception $e){
            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') gagal membuat mata pelajaran ujian : '.$request['title']);

            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Membuat Ujian : '.$e->getMessage()]);
        }
    }

    public function edit($exam){
        try{
            $exam = Exam::findOrFail($exam);

            return view('admin.edit_exams', compact('exam'));
        }
        catch(\Exception $e){
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Membuka Ujian : '.$e->getMessage()]);
        }
    }

    public function update(Request $request){
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

            $exam = Exam::findOrFail($request->examid);
            $exam->update($validated);

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Mengubah mata pelajaran ujian : '.$validated['title']);

            return redirect()->route('admin.exams.manage.view', session('examid'))
                            ->with('success', 'Data Mapel Ujian berhasil diubah <script>setTimeout(function(){ showTab(\'ujian\'); }, 100);</script>');
        }
        catch(\Exception $e){
            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Gagal Mengubah mata pelajaran ujian : '.$request['title']);

            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Mengubah Ujian : '.$e->getMessage()]);
        }

    }

    public function archive(Request $request){
        try{
            $exam = Exam::findOrFail($request->examid);
            $exam->is_active = !$exam->is_active;
            $exam->save();

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Mengarsipkan mata pelajaran ujian : '.$exam->title);

            return redirect()->route('admin.exams.manage.view', session('examid'))
                            ->with('success', 'Data Mapel Ujian berhasil diarsipkan <script>setTimeout(function(){ showTab(\'ujian\'); }, 100);</script>');
        }
        catch(\Exception $e){
            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Gagal Mengarsipkan mata pelajaran ujian : '.$exam->title);

            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Mengarsipkan Ujian : '.$e->getMessage()]);
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
                'perexamstatus' => $exam->is_active,
            ]);

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Membuka Bank Soal  : '.$exam->title);

            return redirect()->route($roleRoutes[$role], $exam);
        }
        catch(\Exception $e){
            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Gagal Membuka Bank Soal  : '.$exam->title);
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
        try{
            $roleRoute = [
                'admin' => 'admin.exams.manage.view',
                'super' => 'super.exams.manage.view',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if(!isset($roleRoute[$role])) {
                throw new \Exception ('Anda tidak memiliki akses untuk menambah ujian');
            }

            session()->forget(['perexamid', 'perexamname']);

            return redirect()->route($roleRoute[$role], session('examid'));
        }
        catch(\Exception $e){
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Keluar dari Bank Soal : '.$e->getMessage()]);
        }
    }


    public function inactiveExam(Request $request){
        // dd($request->all());
        try{
            $roleRoute = [
                'admin' => 'admin.exams.manage.view',
                'super' => 'super.exams.manage.view',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if(!isset($roleRoute[$role])) {
                throw new \Exception ('Anda tidak memiliki akses untuk menambah ujian');
            }

            $exam = Examtype::findOrFail($request->examid);
            $exam->is_active = '0';
            $exam->save();

            session([
                'is_active' => $exam->is_active
            ]);

            // dd($exam);

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Menonaktifkan Mata Pelajaran Ujian : '.$exam->title);

            return redirect()->route($roleRoute[$role], session('examid'))
                            ->with('success', 'Data Mapel Ujian berhasil dinonaktifkan <script>setTimeout(function(){ showTab(\'ujian\'); }, 100);</script>');
        }
        catch (\Exception $e){
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Menonaktifkan Ujian : '.$e->getMessage()]);
        }
    }

    public function activeExam($id){
        try{
            $roleRoute = [
                'admin' => 'admin.exams.manage.view',
                'super' => 'super.exams.manage.view',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if(!isset($roleRoute[$role])) {
                throw new \Exception ('Anda tidak memiliki akses untuk menambah ujian');
            }

            $exam = Examtype::findOrFail($id);
            $exam->is_active = '1';
            $exam->save();

            session([
                'is_active' => $exam->is_active
            ]);

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Mengaktifkan Mata Pelajaran Ujian : '.$exam->title);

            return redirect()->route($roleRoute[$role], session('examid'))
                            ->with('success', 'Data Mapel Ujian berhasil diaktifkan <script>setTimeout(function(){ showTab(\'ujian\'); }, 100);</script>');
        }
        catch (\Exception $e){
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Mengaktifkan Ujian : '.$e->getMessage()]);
        }
    }

    /**
     * Load edit modal HTML via AJAX
     * Returns modal content for editing a question
     */
    public function getEditModalContent($questionId)
    {
        try {
            $question = Question::findOrFail($questionId);

            // Decode JSON data
            $choices = $question->choices ? json_decode($question->choices, true) : [];
            $choicesImages = $question->choices_images ? json_decode($question->choices_images, true) : [];
            $answerKey = is_array($question->answer_key) ? $question->answer_key : json_decode($question->answer_key ?? '[]', true);

            // Return modal HTML
            return view('admin.partials.edit-question-modal', compact('question', 'choices', 'choicesImages', 'answerKey'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Question not found'], 404);
        }
    }

}
