<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionTypes;
use App\Exports\QuestionTemplateExport;
use App\Imports\QuestionsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;

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

    public function store(Request $request)
    {
        //dd($request->all());
        try {
            $choices = $request->input('choices', []);
            $answerKey = $request->input('answer_key', []);

            // Cast answer_key values to integers ONLY if it's an array (multiple choice)
            if (is_array($answerKey)) {
                $answerKey = array_map('intval', $answerKey);
                $request->merge(['answer_key' => $answerKey]);
            }

            // type : 0 pilihan ganda, 1 pilihan ganda kompleks
            // type : 2 benar salah, 3 esai
            if (is_array($choices) && count($choices) > 2) {
                if (count($answerKey) > 1) {
                    $type = '1';
                } else {
                    $type = '0';
                }
            } else if (is_array($choices) && count($choices) == 2) {
                if (count($answerKey) == 1) {
                    $type = '2';
                }
            }
            // check if it is esai who doesn't have a choices
            else {
                $type = '3';
            }

            if ($type != 3) {
                // Build validation rules with proper in: rule for answer_key
                $validChoiceKeys = implode(',', array_keys($choices));

                $rules = [
                    'question_text' => 'required_without:question_image|string|nullable',
                    'question_image' => 'required_without:question_text|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'choices' => 'required|array|min:2',
                    'choices.*' => 'required_without:choice_images.*|string|nullable|max:255',
                    'choice_images.*' => 'required_without:choices.*|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'answer_key' => 'required|array|min:1',
                    'answer_key.*' => 'required|integer|in:' . $validChoiceKeys,
                    'points' => 'required|numeric|min:1'
                ];

                // Custom validation messages
                $messages = [
                    'question_text.required_without' => 'Soal harus memiliki teks atau gambar',
                    'question_image.required_without' => 'Soal harus memiliki teks atau gambar',
                    'choices.*.required_without' => 'Setiap pilihan harus memiliki teks atau gambar',
                    'choice_images.*.required_without' => 'Setiap pilihan harus memiliki teks atau gambar',
                ];

                $validated = $request->validate($rules, $messages);
                $validated['choices'] = json_encode($validated['choices']);

                // Sort answer_key numerically to maintain consistent order regardless of checkbox click order
                $answerKeyArray = $validated['answer_key'];
                sort($answerKeyArray, SORT_NUMERIC);
                $validated['answer_key'] = json_encode($answerKeyArray);
            } else {
                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'answer_key' => 'required|string|max:255',
                    'points' => 'required|numeric'
                ]);
                $validated['choices'] = null;
            }

            // Handle question image upload
            if ($request->hasFile('question_image')) {
                $questionImage = $request->file('question_image');
                $questionImagePath = $questionImage->store('question_images', 'public');
                $validated['question_image'] = $questionImagePath;
            }

            // Handle choice images upload
            $choicesImages = [];
            if ($request->hasFile('choice_images')) {
                foreach ($request->file('choice_images') as $choiceId => $image) {
                    if ($image) {
                        $choiceImagePath = $image->store('choice_images', 'public');
                        $choicesImages[$choiceId] = $choiceImagePath;
                    }
                }
            }

            if (!empty($choicesImages)) {
                $validated['choices_images'] = json_encode($choicesImages);
            }

            $validated['exam_id'] = session('perexamid');
            $validated['created_by'] = auth()->user()->id;
            $validated['question_type_id'] = $type;

            $roleRoutes =  [
                'admin' => 'admin.exams.manage.question',
                'super' => 'super.exams.manage.question',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if (!isset($roleRoutes[$role])) {
                throw new \Exception('Anda tidak memiliki akses untuk menambah ujian');
            }
            // dd($validated);
            Question::create($validated);

            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil Membuat Soal Baru' . session('perexamname'));


            return redirect()->route($roleRoutes[$role], session('perexamid'))->with('success', 'Soal berhasil ditambahkan. <script>setTimeout(function(){ showTab(\'soal\'); }, 100);</script>');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal menambahkan soal : ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $exam)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            $question = Question::findOrFail($exam);

            $choices = $request->input('choices', []);
            $answerKey = $request->input('answer_key', []);

            // Cast answer_key values to integers ONLY if it's an array (multiple choice)
            if (is_array($answerKey)) {
                $answerKey = array_map('intval', $answerKey);
                $request->merge(['answer_key' => $answerKey]);
            }

            // Preserve existing choice text if not provided in update
            $existingChoices = $question->choices ? json_decode($question->choices, true) : [];
            foreach ($choices as $key => $choice) {
                if (empty($choice) && isset($existingChoices[$key])) {
                    $choices[$key] = $existingChoices[$key];
                }
            }

            // Ensure choice keys are strings for consistency with form input names
            $choices = array_combine(
                array_map('strval', array_keys($choices)),
                array_values($choices)
            );

            $request->merge(['choices' => $choices]);

            $type = '';

            // type : 0 pilihan ganda, 1 pilihan ganda kompleks
            // type : 2 benar salah, 3 esai
            if (is_array($choices) && count($choices) > 2) {
                if (count($answerKey) > 1) {
                    $type = '1';
                } else {
                    $type = '0';
                }
            } else if (is_array($choices) && count($choices) == 2) {
                if (count($answerKey) == 1) {
                    $type = '2';
                }
            }
            // check if it is esai who doesn't have a choices
            else {
                $type = '3';
            }

            if ($type != '3') {
                // Build validation rules with proper in: rule for answer_key
                $validChoiceKeys = implode(',', array_keys($choices));

                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'choices' => 'required|array|min:2',
                    'choices.*' => 'required|string|max:255',
                    'choice_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'answer_key' => 'required|array|min:1',
                    'answer_key.*' => 'required|integer|in:' . $validChoiceKeys,
                    'points' => 'required|numeric|min:1'
                ]);
                $validated['choices'] = json_encode($validated['choices']);

                // Sort answer_key numerically to maintain consistent order regardless of checkbox click order
                $answerKeyArray = $validated['answer_key'];
                sort($answerKeyArray, SORT_NUMERIC);
                $validated['answer_key'] = json_encode($answerKeyArray);
            } else {
                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'answer_key' => 'required|string|max:255',
                    'points' => 'required|numeric'
                ]);
                $validated['choices'] = null;
            }

            $validated['exam_id'] = session('perexamid');
            $validated['created_by'] = auth()->user()->id;
            $validated['question_type_id'] = $type;

            $roleRoutes =  [
                'admin' => 'admin.exams.manage.question',
                'super' => 'super.exams.manage.question',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if (!isset($roleRoutes[$role])) {
                throw new \Exception('Anda tidak memiliki akses untuk merubah soal');
            }

            // Handle question image upload
            if ($request->hasFile('question_image')) {
                // Delete old image if exists
                if ($question->question_image) {
                    Storage::disk('public')->delete($question->question_image);
                }
                $questionImage = $request->file('question_image');
                $questionImagePath = $questionImage->store('question_images', 'public');
                $validated['question_image'] = $questionImagePath;
            } elseif ($request->input('remove_question_image') == '1') {
                // Handle removal of question image
                if ($question->question_image) {
                    Storage::disk('public')->delete($question->question_image);
                }
                $validated['question_image'] = null;
            }

            // Handle choice images upload
            $existingChoicesImages = $question->choices_images ? json_decode($question->choices_images, true) : [];
            $choicesImages = $existingChoicesImages;

            // Handle removal of choice images
            if ($request->has('remove_choice_images')) {
                foreach ($request->input('remove_choice_images') as $choiceId) {
                    if (isset($choicesImages[$choiceId])) {
                        Storage::disk('public')->delete($choicesImages[$choiceId]);
                        unset($choicesImages[$choiceId]);
                    }
                }
            }

            // Handle new choice images
            if ($request->hasFile('choice_images')) {
                foreach ($request->file('choice_images') as $choiceId => $image) {
                    if ($image) {
                        // Delete old image if exists
                        if (isset($choicesImages[$choiceId])) {
                            Storage::disk('public')->delete($choicesImages[$choiceId]);
                        }
                        $choiceImagePath = $image->store('choice_images', 'public');
                        $choicesImages[$choiceId] = $choiceImagePath;
                    }
                }
            }

            $validated['choices_images'] = !empty($choicesImages) ? json_encode($choicesImages) : null;

            $question->update($validated);

            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil merubah data soal  ');


            DB::commit();

            return redirect()->route($roleRoutes[$role], session('perexamid'))->with('success', 'Soal berhasil dirubah. <script>setTimeout(function(){ showTab(\'banksoal\'); }, 100);</script>');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal Merubah soal :' . $e->getMessage()]);
        }
    }

    public function destroy($exam)
    {
        try {
            $question = Question::findOrFail($exam);

            // Delete question image if exists
            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }

            // Delete choice images if exist
            if ($question->choices_images) {
                $choicesImages = json_decode($question->choices_images, true);
                if (is_array($choicesImages)) {
                    foreach ($choicesImages as $imagePath) {
                        Storage::disk('public')->delete($imagePath);
                    }
                }
            }

            $question->delete();

            $roleRoutes =  [
                'admin' => 'admin.exams.manage.question',
                'super' => 'super.exams.manage.question',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if (!isset($roleRoutes[$role])) {
                throw new \Exception('Anda tidak memiliki akses untuk menghapus soal');
            }

            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil menghapus soal  ' . session('perexamname'));

            return redirect()->route($roleRoutes[$role], session('perexamid'))->with('success', 'Soal berhasil dihapus. <script>setTimeout(function(){ showTab(\'banksoal\'); }, 100);</script>');
        } catch (\Exception $e) {
            return redirect()->route($roleRoutes[$role], session('perexamid'))->withErrors(['error' => 'Gagal menghapus soal : ' . $e->getMessage()]);
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
     * Download template Excel untuk import soal
     */
    // public function downloadTemplate()
    // {
    //     return Excel::download(new QuestionTemplateExport, 'template_soal.xlsx');
    // }

    /**
     * Import soal dari file Excel
     */
    // public function import(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'file' => 'required|mimes:xlsx,xls',
    //         ], [
    //             'file.required' => 'File Excel wajib diupload',
    //             'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls)',
    //         ]);

    //         DB::beginTransaction();

    //         Excel::import(
    //             new QuestionsImport(
    //                 session('perexamid'),
    //                 auth()->user()->id
    //             ),
    //             $request->file('file')
    //         );

    //         DB::commit();

    //         $user = auth()->user();
    //         logActivity($user->name.' (ID: '.$user->id.') Berhasil mengimport soal untuk ujian '.session('perexamname'));

    //         return redirect()
    //             ->route('admin.exams.manage.question', session('perexamid'))
    //             ->with('success', 'Soal berhasil diimport. <script>setTimeout(function(){ showTab(\'banksoal\'); }, 100);</script>');

    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         return redirect()
    //             ->back()
    //             ->withErrors(['error' => 'Error validasi: ' . implode(', ', $e->errors())]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()
    //             ->back()
    //             ->withErrors(['error' => 'Gagal mengimport soal: ' . $e->getMessage()]);
    //     }
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function typestore(Request $request)
    {
        //
        try {
            $validates = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            QuestionTypes::create($validates);

            return redirect()->route('admin.questions.types')->with('success', 'Jenis Soal berhasil ditambahkan.');
        } catch (\Exception $e) {
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
        try {
            $type = QuestionTypes::findOrFail($type);

            return view('admin.editquestiontype', compact('type'));
        } catch (\Exception $e) {

            return redirect()->route('admin.questions.types')->withErrors(['error' => 'Jenis Soal tidak ditemukan : ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function typeupdate(Request $request, string $id)
    {
        //
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $type = QuestionTypes::findOrFail($id);
            $type->update($validated);

            return redirect()->route('admin.questions.types')->with('success', 'Jenis Soal berhasil dirubah');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors((['error' => 'Gagal Merubah Jenis Soal : ' . $e->getMessage()]));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function typedestroy(string $id)
    {
        //
        try {
            $type = QuestionTypes::findOrFail($id);
            $type->delete();

            return redirect()->route('admin.questions.types')->with('success', 'Jenis Soal berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('admin.question.types')->withErrors(['error' => 'Gagal menghapus jenis soal :' . $e->getMessage()]);
        }
    }

    /**
     * Download the template for question import
     */
    public function downloadTemplate($exam)
    {
        try {
            return Excel::download(new QuestionTemplateExport(), 'template-soal.xlsx');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal mengunduh template: ' . $e->getMessage()]);
        }
    }

    /**
     * Import questions from Excel file
     */
    public function import(Request $request, $exam)
    {
        try {
            $request->validate([
                'excel_file' => 'required|mimes:xlsx,xls',
            ]);

            $userId = Auth::id();
            Excel::import(new QuestionsImport($exam, $userId), $request->file('excel_file'));

            $roleRoute = auth()->user()->hasRole('super') ? 'super.exams.manage.question' : 'admin.exams.manage.question';
            return redirect()->route($roleRoute, $exam)->with('success', 'Soal berhasil diimport');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = collect($failures)->map(function ($failure) {
                return "Baris {$failure->row()}: {$failure->errors()[0]}";
            })->join('<br>');

            return redirect()->back()->withErrors(['error' => 'Error saat import: <br>' . $errors]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal import soal: ' . $e->getMessage()]);
        }
    }

    /**
     * Export questions to Excel file
     */
    public function export($exam)
    {
        try {
            $filename = 'soal-' . date('Y-m-d-His') . '.xlsx';
            return Excel::download(new QuestionTemplateExport($exam), $filename);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal export soal: ' . $e->getMessage()]);
        }
    }
}
