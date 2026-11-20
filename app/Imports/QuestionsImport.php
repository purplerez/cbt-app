<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class QuestionsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $examId;
    protected $userId;

    public function __construct($examId, $userId)
    {
        $this->examId = $examId;
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        // Filter out instruction, example, and empty rows
        $dataRows = $rows->filter(function ($row) {
            return !$this->isInstructionOrEmptyRow($row);
        });

        foreach ($dataRows as $index => $row) {
            $soalText = trim((string)($row['soal'] ?? ''));
            $kunciJawaban = trim((string)($row['kunci_jawaban'] ?? ''));

            if ($soalText === '') {
                continue;
            }

            if ($kunciJawaban === '') {
                throw new \Exception("Baris " . ($index + 2) . ": Kolom kunci jawaban wajib diisi untuk soal \"{$soalText}\"");
            }

            // Handle choices and answer key based on question type
            $type = $this->determineQuestionType($row);
            $choices = $this->formatChoices($row, $type);
            $answerKey = $this->formatAnswerKey($row, $type);

            Question::create([
                'exam_id' => $this->examId,
                'question_type_id' => $type,
                'question_text' => $soalText,
                'choices' => $choices,
                'answer_key' => $answerKey,
                'points' => !empty($row['poin']) && is_numeric($row['poin']) ? (float)$row['poin'] : 1,
                'created_by' => $this->userId
            ]);
        }
    }

    private function isInstructionOrEmptyRow($row): bool
    {
        $soal = trim((string)($row['soal'] ?? ''));
        if ($soal === '') {
            return true;
        }

        // Check comment or instruction markers
        if (str_starts_with($soal, '//') || str_starts_with($soal, '===') || str_starts_with($soal, '#')) {
            return true;
        }

        if (stripos($soal, 'PETUNJUK') !== false) {
            return true;
        }

        return false;
    }

    private function determineQuestionType($row)
    {
        if (empty($row['pilihan_a'])) {
            return '3'; // Essay
        }

        if (!empty($row['pilihan_a']) && !empty($row['pilihan_b']) && empty($row['pilihan_c'])) {
            return '2'; // True/False
        }

        // Check if multiple answers
        $answers = explode(',', (string)$row['kunci_jawaban']);
        if (count($answers) > 1) {
            return '1'; // Complex Multiple Choice
        }

        return '0'; // Regular Multiple Choice
    }

    private function formatChoices($row, $type)
    {
        if ($type == '3') { // Essay
            return null;
        }

        $choices = [];
        $options = ['a', 'b', 'c', 'd', 'e'];

        foreach ($options as $index => $option) {
            $columnName = 'pilihan_' . $option;
            if (isset($row[$columnName]) && !empty(trim((string)$row[$columnName]))) {
                $choices[$index + 1] = trim((string)$row[$columnName]);
            }
        }

        return json_encode($choices);
    }

    private function formatAnswerKey($row, $type)
    {
        if ($type == '3') { // Essay
            return $row['kunci_jawaban'];
        }

        // Split answers by comma and trim
        $answers = array_map('trim', explode(',', $row['kunci_jawaban']));

        // Convert letters to uppercase and keep them as letters (A, B, C, D, E, T, F)
        $formattedAnswers = array_map(function ($answer) use ($type) {
            $answer = strtoupper(trim($answer));

            // For True/False (type 2), ensure only T or F
            if ($type == '2') {
                return $answer;  // Keep as T or F
            }

            // For multiple choice, keep as letters (A, B, C, D, E)
            return $answer;
        }, $answers);

        // Return single value or array based on count
        if (count($formattedAnswers) === 1) {
            return $formattedAnswers[0];  // Return single letter like 'A' or 'T'
        }

        return json_encode($formattedAnswers);  // Return JSON array like ["A","C"] for complex multiple choice
    }

    public function rules(): array
    {
        return [
            '*.poin' => 'nullable|numeric|min:1',
            '*.pilihan_a' => 'nullable|string',
            '*.pilihan_b' => 'nullable|string',
            '*.pilihan_c' => 'nullable|string',
            '*.pilihan_d' => 'nullable|string',
            '*.pilihan_e' => 'nullable|string',
        ];
    }

    // public function customValidationMessages()
    // {
    //     return [
    //         '*.soal.required_unless' => 'Kolom soal harus diisi',
    //         '*.kunci_jawaban.required_unless' => 'Kolom kunci jawaban harus diisi'
    //     ];
    // }

    public function customValidationMessages()
    {
        return [
            'soal.required' => 'Kolom soal harus diisi',
            'kunci_jawaban.required' => 'Kolom kunci jawaban harus diisi',
            'poin.numeric' => 'Kolom poin harus berupa angka',
            'poin.min' => 'Kolom poin minimal 1',
        ];
    }
}
