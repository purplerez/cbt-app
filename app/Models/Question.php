<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\AnswerKeyHelper;

class Question extends Model
{
    //
    protected $table = 'exam_questions';

    protected $fillable = [
        'exam_id',
        'question_type_id',
        'question_text',
        'question_html',
        'question_image',
        'choices',
        'choice_html',
        'choices_images',
        'answer_key',
        'points',
        'created_by',
    ];

    /**
     * Mutator: Convert answer key to letter format before saving
     * Handles conversion from index [0,1,2] to letter ['A','B','C']
     * For True/False: converts [0] to ['T'] or [1] to ['F']
     *
     * @param mixed $value
     * @return void
     */
    public function setAnswerKeyAttribute($value)
    {
        // If it's already a string (essay type), store as-is
        if (is_string($value) && !$this->isJsonString($value)) {
            $this->attributes['answer_key'] = $value;
            return;
        }

        // Decode JSON string if needed
        if (is_string($value) && $this->isJsonString($value)) {
            $value = json_decode($value, true);
        }

        // Handle array (multiple choice types)
        if (is_array($value)) {
            $questionType = $this->question_type_id ?? $this->attributes['question_type_id'] ?? null;

            // Convert indices to letters based on question type
            $converted = array_map(function ($item) use ($questionType) {
                // If already a letter, return as-is
                if (!is_numeric($item)) {
                    return strtoupper(trim($item));
                }

                // For True/False (type 2), convert 0 to T, 1 to F
                if ($questionType == '2') {
                    return $item == 0 ? 'T' : 'F';
                }

                // For other types, convert index to letter (0->A, 1->B, etc)
                return AnswerKeyHelper::indexToLetter((int)$item);
            }, $value);

            // Store as JSON array for multiple answers or single value for one answer
            if (count($converted) === 1) {
                $this->attributes['answer_key'] = $converted[0];
            } else {
                $this->attributes['answer_key'] = json_encode($converted);
            }
        } else {
            // Single value
            $questionType = $this->question_type_id ?? $this->attributes['question_type_id'] ?? null;

            if (is_numeric($value)) {
                // For True/False (type 2), convert 0 to T, 1 to F
                if ($questionType == '2') {
                    $this->attributes['answer_key'] = $value == 0 ? 'T' : 'F';
                } else {
                    $this->attributes['answer_key'] = AnswerKeyHelper::indexToLetter((int)$value);
                }
            } else {
                $this->attributes['answer_key'] = strtoupper(trim($value));
            }
        }
    }

    /**
     * Accessor: Always return answer key in letter format
     *
     * @param mixed $value
     * @return string|array
     */
    public function getAnswerKeyAttribute($value)
    {
        // If it's empty, return as-is
        if (empty($value)) {
            return $value;
        }

        // Check if it's a JSON string
        if ($this->isJsonString($value)) {
            $decoded = json_decode($value, true);

            // Ensure all values are letters (handle legacy data with indices)
            if (is_array($decoded)) {
                return array_map(function ($item) {
                    if (is_numeric($item)) {
                        return AnswerKeyHelper::indexToLetter((int)$item);
                    }
                    return strtoupper(trim($item));
                }, $decoded);
            }

            return $decoded;
        }

        // Single value - ensure it's in letter format
        if (is_numeric($value)) {
            $questionType = $this->question_type_id ?? $this->attributes['question_type_id'] ?? null;

            if ($questionType == '2') {
                return $value == 0 ? 'T' : 'F';
            }

            return AnswerKeyHelper::indexToLetter((int)$value);
        }

        return strtoupper(trim($value));
    }

    /**
     * Check if a string is valid JSON
     *
     * @param string $string
     * @return bool
     */
    private function isJsonString($string)
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function getQuestionTypeLabelAttribute()
    {
        $arrJenis = [
            '0' => "Pilihan Ganda",
            '1' => "Pilihan Ganda Kompleks",
            '2' => "Benar Salah",
            '3' => "Essay"
        ];

        return $arrJenis[(string)$this->question_type_id] ?? 'Tidak Diketahui';
    }

    public function questionType()
    {
        return $this->belongsTo(QuestionTypes::class);
    }

    public function exam()
    {
        return $this->belongsTo(Examtype::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    public function answers(){
        return $this->hasMany(Answer::class);
    }
    */
}
