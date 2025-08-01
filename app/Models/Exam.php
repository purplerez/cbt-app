<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    //
    protected $fillable = [
        'subject_id',
        'title',
        'deskripsi',
        'durasi',
        'total_soal',
        'score_minimal',
        'created_by',
        'school_id',
        'is_global',
        'is_active',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
