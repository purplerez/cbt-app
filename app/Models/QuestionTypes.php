<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionTypes extends Model
{
    //
    protected $table = 'qtypes';

    protected $fillable = [
        'name',
    ];

    public function questions(){

        return $this->hasMany(Question::class);
    }
}
