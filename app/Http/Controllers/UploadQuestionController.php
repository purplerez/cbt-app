<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Exam;
use App\Services\WordParserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UploadQuestionController extends Controller
{
    protected $wordParser;

    public function __construct(WordParserService $wordParser)
    {
        $this->wordParser = $wordParser;
    }

    /**
     * Handle Word file upload and import questions directly to exam
     */
    public function import(Request $request)
    {
        $request->validate([
            'word_file' => 'required|mimes:doc,docx|max:10240', // Max 10MB
            'exam_id' => 'required|exists:exams,id',
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

            // Save questions directly to exam_questions
            $importedCount = 0;

            foreach ($questions as $questionData) {
                // Map question type: default to PG (0)
                $questionTypeId = '0'; // PG by default (ENUM needs string)

                // Create choices array and choices_images array in parallel
                $choices = [];
                $choicesImages = [];

                // Option A
                if (!empty($questionData['option_a'])) {
                    $choices[] = $questionData['option_a'];
                    $choicesImages[] = $questionData['option_a_image'] ?? null;
                }

                // Option B
                if (!empty($questionData['option_b'])) {
                    $choices[] = $questionData['option_b'];
                    $choicesImages[] = $questionData['option_b_image'] ?? null;
                }

                // Option C
                if (!empty($questionData['option_c'])) {
                    $choices[] = $questionData['option_c'];
                    $choicesImages[] = $questionData['option_c_image'] ?? null;
                }

                // Option D
                if (!empty($questionData['option_d'])) {
                    $choices[] = $questionData['option_d'];
                    $choicesImages[] = $questionData['option_d_image'] ?? null;
                }

                // Option E
                if (!empty($questionData['option_e'])) {
                    $choices[] = $questionData['option_e'];
                    $choicesImages[] = $questionData['option_e_image'] ?? null;
                }

                // Create answer key array first (needed for type detection)
                // correct_answer from parser: "C" (single) or "B,C,E" (multiple)
                // Split by comma so multi-answers are stored as separate letters.
                $letterOrder = ['A', 'B', 'C', 'D', 'E'];
                $answerKey = [];
                if (!empty($questionData['correct_answer'])) {
                    $answerKey = array_values(array_filter(
                        array_map(
                            fn($l) => strtoupper(trim($l)),
                            explode(',', $questionData['correct_answer'])
                        ),
                        fn($l) => in_array($l, $letterOrder)
                    ));
                }

                // Determine question type based on choices and answer key
                if (count($choices) == 2) {
                    $questionTypeId = '2'; // True/False
                } elseif (count($answerKey) > 1) {
                    $questionTypeId = '1'; // PG Kompleks (multiple correct answers)
                }

                // Prepare choices_images JSON (only if at least one image exists)
                $hasImages = !empty(array_filter($choicesImages));

                Question::create([
                    'exam_id' => $examId,
                    'question_type_id' => $questionTypeId,
                    'question_text' => $questionData['question_text'],
                    'question_image' => $questionData['image_path'] ?? null,
                    'choices' => json_encode($choices),
                    'choices_images' => $hasImages ? json_encode($choicesImages) : null,
                    'answer_key' => json_encode($answerKey),
                    'points' => $questionData['points'] ?? 1,
                    'created_by' => Auth::id(),
                ]);

                $importedCount++;
            }

            // Redirect back to manage exam banksoal
            $exam = Exam::find($examId);
            session([
                'perexamid' => $exam->id,
                'perexamname' => $exam->exam_name,
                'perexamstatus' => $exam->is_active
            ]);

            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Mengimport soal ke ujian : ' . $exam->exam_name);

            $roleRoutes =  [
                'admin' => 'admin.exams.manage.question',
                'super' => 'super.exams.manage.question',
            ];

            $role = auth()->user()->getRoleNames()->first();

            if (!isset($roleRoutes[$role])) {
                throw new \Exception('Anda tidak memiliki akses untuk mengimport soal');
            }

            return redirect()->route($roleRoutes[$role], ['exam' => $examId])
                ->with('success', "Berhasil mengimport {$importedCount} soal dari file {$originalFilename} ke ujian {$exam->exam_name}");
        } catch (\Exception $e) {
            Log::error('Error importing questions: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Terjadi kesalahan saat mengimport soal: ' . $e->getMessage());
        }
    }
}
