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
        'choices',
        'answer_key',
        'points',
        'created_by',
    ];

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
