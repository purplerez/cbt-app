<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function typeindex()
    {
        //
        $types = QuestionTypes::all();

        return view('admin.view_questiontypes', compact('types'));
    }

    public function store(Request $request){
        // dd($request->all());
        try{
            $choices = $request->input('choices', []);
            $answerKey = $request->input('answer_key', []);

            // type : 0 pilihan ganda, 1 pilihan ganda kompleks
            // type : 2 benar salah, 3 esai
            if(is_array($choices) && count($choices) > 2){
                if(count($answerKey) > 1){
                    $type = '1';
                }
                else {
                    $type = '0';
                }
            }
            else if(is_array($choices) && count($choices) == 2){
                if(count($answerKey) == 1){
                    $type = '2';
                }
            }
            // check if it is esai who doesn't have a choices
            else {
                $type = '3';
            }

            if($type != 3) {
                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'choices' => 'required|array|min:2',
                    'choices.*' => 'required|string|max:255',
                    'answer_key' => 'required|array|min:1',
                    'answer_key.*' => 'required|integer|in:' . implode(',', array_keys($choices)),
                    'points' => 'required|numeric|min:1'
                ]);
                $validated['choices'] = json_encode($validated['choices']);
                $validated['answer_key'] = json_encode($validated['answer_key']);
            }
            else {
                $validated = $request->validate([
                    'question_text' => 'required|string|max:255',
                    'answer_key' => 'required|string|max:255',
                    'points' => 'required|numeric|min:1'
                ]);
                $validated['choices'] = null;
            }

            $validated['exam_id'] = session('perexamid');
            $validated['created_by'] = auth()->user()->id;
            $validated['question_type_id'] = $type;

            Question::create($validated);

            return redirect()->route('admin.exams.manage.question', session('perexamid'))->with('success', 'Soal berhasil ditambahkan. <script>setTimeout(function(){ showTab(\'soal\'); }, 100);</script>');

        }
        catch (\Exception $e){
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal menambahkan soal : '.$e->getMessage()]);

        }
    }

    public function update(Request $request, $exam){
        // dd($request->all());
        DB::beginTransaction();
        try{
            $choices = $request->input('choices', []);
            $answerKey = $request->input('answer_key', []);
            $type = '';

            // type : 0 pilihan ganda, 1 pilihan ganda kompleks
            // type : 2 benar salah, 3 esai
            if(is_array($choices) && count($choices) > 2){
                if(count($answerKey) > 1){
                    $type = '1';
                }
                else {
                    $type = '0';
                }
            }
            else if(is_array($choices) && count($choices) == 2){
                if(count($answerKey) == 1){
                    $type = '2';
                }
            }
            // check if it is esai who doesn't have a choices
            else {
                $type = '3';
            }

            if($type != '3') {
                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'choices' => 'required|array|min:2',
                    'choices.*' => 'required|string|max:255',
                    'answer_key' => 'required|array|min:1',
                    'answer_key.*' => 'required|integer|in:' . implode(',', array_keys($choices)),
                    'points' => 'required|numeric|min:1'
                ]);
                $validated['choices'] = json_encode($validated['choices']);
                $validated['answer_key'] = json_encode($validated['answer_key']);
            }
            else {
                $validated = $request->validate([
                    'question_text' => 'required|string|max:255',
                    'answer_key' => 'required|string|max:255',
                    'points' => 'required|numeric|min:1'
                ]);
                $validated['choices'] = null;
            }

            $validated['exam_id'] = session('perexamid');
            $validated['created_by'] = auth()->user()->id;
            $validated['question_type_id'] = $type;

            $question = Question::findOrFail($exam);

            $question->update($validated);
            DB::commit();

            return redirect()->route('admin.exams.manage.question', session('perexamid'))->with('success', 'Soal berhasil dirubah. <script>setTimeout(function(){ showTab(\'banksoal\'); }, 100);</script>');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Merubah soal :'.$e->getMessage()]);
        }
    }

    public function destroy($exam){
        try{
            $question = Question::findOrFail($exam);
            $question->delete();

            return redirect()->route('admin.exams.manage.question', session('perexamid'))->with('success', 'Soal berhasil dihapus. <script>setTimeout(function(){ showTab(\'banksoal\'); }, 100);</script>');
        }
        catch(\Exception $e){
            return redirect()->route('admin.exams.manage.question', session('perexamid'))->withErrors(['error' => 'Gagal menghapus soal : '.$e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function typestore(Request $request)
    {
        //
        try{
            $validates = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            QuestionTypes::create($validates);

            return redirect()->route('admin.questions.types')->with('success', 'Jenis Soal berhasil ditambahkan.');
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal menambahkan jenis soal :', $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function typeedit(int $type)
    {
        //
        try{
            $type = QuestionTypes::findOrFail($type);

            return view('admin.editquestiontype', compact('type'));
        }
        catch(\Exception $e){

            return redirect()->route('admin.questions.types')->withErrors(['error' => 'Jenis Soal tidak ditemukan : '.$e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function typeupdate(Request $request, string $id)
    {
        //
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $type = QuestionTypes::findOrFail($id);
            $type->update($validated);

            return redirect()->route('admin.questions.types')->with('success', 'Jenis Soal berhasil dirubah');
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withInput()->withErrors((['error' => 'Gagal Merubah Jenis Soal : '.$e->getMessage()]));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function typedestroy(string $id)
    {
        //
        try{
            $type = QuestionTypes::findOrFail($id);
            $type->delete();

            return redirect()->route('admin.questions.types')->with('success', 'Jenis Soal berhasil dihapus');
        }
        catch(\Exception $e)
        {

            return redirect()->route('admin.question.types')->withErrors(['error' => 'Gagal menghapus jenis soal :'.$e->getMessage()]);
        }
    }
}
