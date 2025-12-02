<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    //
    protected $fillable = [
        'exam_type_id',
        // 'subject_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'duration',
        'total_quest',
        'score_minimal',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'duration' => 'integer',
        'total_quest' => 'integer',
        'score_minimal' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // public function school()
    // {
    //     return $this->belongsTo(School::class);
    // }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function examType()
    {
        return $this->belongsTo(Examtype::class);
    }

    public function preassigneds()
    {
        return $this->hasMany(Preassigned::class);
    }

    public function examSessions()
    {
        return $this->hasMany(ExamSession::class);
    }
}
