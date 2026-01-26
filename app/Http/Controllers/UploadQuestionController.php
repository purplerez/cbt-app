<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadQuestion;
use App\Models\Question;
use App\Models\Exam;
use App\Services\WordParserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UploadQuestionController extends Controller
{
    protected $wordParser;

    public function __construct(WordParserService $wordParser)
    {
        $this->wordParser = $wordParser;
    }

    /**
     * Show upload form
     */
    public function index()
    {
        $questions = UploadQuestion::latest()->paginate(10);
        return view('admin.questions.index', compact('questions'));
    }

    /**
     * Show upload form
     */
    public function create(Request $request)
    {
        $examId = $request->query('exam_id');
        return view('admin.questions.upload', compact('examId'));
    }

    /**
     * Handle Word file upload and import questions
     */
    public function import(Request $request)
    {
        $request->validate([
            'word_file' => 'required|mimes:doc,docx|max:10240', // Max 10MB
            'exam_id' => 'nullable|exists:exams,id',
        ]);

        try {
            $file = $request->file('word_file');
            $originalFilename = $file->getClientOriginalName();
            $examId = $request->input('exam_id');

            // Save uploaded file temporarily
            $tempPath = $file->getRealPath();

            Log::info('Starting to parse Word file: ' . $originalFilename);
            Log::info('Temp path: ' . $tempPath);
            Log::info('Exam ID: ' . $examId);

            // Parse the Word document
            $questions = $this->wordParser->parseWordDocument($tempPath);

            Log::info('Found ' . count($questions) . ' questions');
            Log::info('Questions data: ' . json_encode($questions));

            if (empty($questions)) {
                return back()->with('error', 'Tidak ada soal yang ditemukan dalam file Word. Pastikan format file sesuai dengan contoh yang diberikan.');
            }

            // Save questions to database
            $importedCount = 0;

            if ($examId) {
                // Save directly to exam_questions
                foreach ($questions as $questionData) {
                    // Map question type: default to PG (0)
                    $questionTypeId = '0'; // PG by default (ENUM needs string)

                    // Create choices array
                    $choices = [];
                    if (!empty($questionData['option_a'])) $choices[] = $questionData['option_a'];
                    if (!empty($questionData['option_b'])) $choices[] = $questionData['option_b'];
                    if (!empty($questionData['option_c'])) $choices[] = $questionData['option_c'];
                    if (!empty($questionData['option_d'])) $choices[] = $questionData['option_d'];

                    // Determine question type based on choices
                    if (count($choices) == 2) {
                        $questionTypeId = '2'; // True/False (ENUM needs string)
                    }

                    // Create answer key array
                    $answerKey = [];
                    if (!empty($questionData['correct_answer'])) {
                        $answerKey = [strtoupper($questionData['correct_answer'])];
                    }

                    Question::create([
                        'exam_id' => $examId,
                        'question_type_id' => $questionTypeId,
                        'question_text' => $questionData['question_text'],
                        'question_image' => $questionData['image_path'] ?? null,
                        'choices' => json_encode($choices),
                        'answer_key' => json_encode($answerKey),
                        'points' => $questionData['points'] ?? 1,
                        'created_by' => Auth::id(),
                    ]);

                    $importedCount++;
                }

                // Redirect back to manage exam
                $exam = Exam::find($examId);
                session([
                    'perexamid' => $exam->id,
                    'perexamname' => $exam->exam_name,
                    'perexamstatus' => $exam->is_active
                ]);

                return redirect()->route('admin.exams.manage', ['exam' => $examId])
                    ->with('success', "Berhasil mengimport {$importedCount} soal dari file {$originalFilename} ke ujian {$exam->exam_name}");
            } else {
                // Save to upload_questions (bank soal)
                foreach ($questions as $questionData) {
                    $questionData['original_filename'] = $originalFilename;
                    UploadQuestion::create($questionData);
                    $importedCount++;
                }

                return redirect()->route('admin.questions.index')
                    ->with('success', "Berhasil mengimport {$importedCount} soal dari file {$originalFilename}");
            }
        } catch (\Exception $e) {
            Log::error('Error importing questions: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Terjadi kesalahan saat mengimport soal: ' . $e->getMessage());
        }
    }

    /**
     * Display a single question
     */
    public function show($id)
    {
        $question = UploadQuestion::findOrFail($id);
        return view('admin.questions.show', compact('question'));
    }

    /**
     * Delete a question
     */
    public function destroy($id)
    {
        $question = UploadQuestion::findOrFail($id);

        // Delete associated images if exist
        $imageFields = ['image_path', 'option_a_image', 'option_b_image', 'option_c_image', 'option_d_image'];

        foreach ($imageFields as $field) {
            if ($question->$field && Storage::disk('public')->exists($question->$field)) {
                Storage::disk('public')->delete($question->$field);
            }
        }

        $question->delete();

        return redirect()->route('admin.questions.index')
            ->with('success', 'Soal berhasil dihapus.');
    }
}
