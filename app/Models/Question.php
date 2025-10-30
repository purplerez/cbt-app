<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    //
    protected $table = 'exam_questions';

    protected $fillable = [
        'exam_id',
        'question_type_id',
        'question_text',
        'question_image',
        'choices',
        'choices_images',
        'answer_key',
        'points',
        'created_by',
    ];

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

    public function questionType(){
        return $this->belongsTo(QuestionTypes::class);
    }

    public function exam(){
        return $this->belongsTo(Examtype::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    /*
    public function answers(){
        return $this->hasMany(Answer::class);
    }
    */

}
