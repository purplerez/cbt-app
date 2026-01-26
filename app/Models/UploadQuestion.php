<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadQuestion extends Model
{
    protected $table = 'upload_questions';

    protected $fillable = [
        'question_text',
        'option_a',
        'option_a_image',
        'option_b',
        'option_b_image',
        'option_c',
        'option_c_image',
        'option_d',
        'option_d_image',
        'correct_answer',
        'points',
        'image_path',
        'original_filename',
    ];
}
