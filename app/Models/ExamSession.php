<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSession extends Model
{
    protected $fillable = [
        'user_id',
        'exam_id',
        'session_token',
        'started_at',
        'submited_at',
        'time_remaining',
        'total_score',
        'status',
        'attempt_number',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submited_at' => 'datetime',
        'total_score' => 'decimal:2',
        'time_remaining' => 'integer',
        'attempt_number' => 'integer'
    ];

    /**
     * Get the user that owns the exam session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the student for this exam session
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'user_id', 'user_id');
    }

    /**
     * Get the exam that owns the session.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the student answers for this session
     */
    public function studentAnswer()
    {
        return $this->hasOne(StudentAnswer::class, 'session_id');
    }

    public function ExamLogs()
    {
        return $this->hasMany(ExamLog::class);
    }

    public function format(DateTime $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
