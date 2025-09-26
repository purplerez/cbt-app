<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamLog extends Model
{
    protected $table = 'exam_logs';

    // Disable updated_at since migration only has created_at
    public $timestamps = false;

    protected $dates = ['created_at'];

    protected $fillable = [
        'session_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
        'device',
        'created_at'
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime'
    ];

    public function sessionExam()
    {
        return $this->belongsTo(ExamSession::class, 'session_id');
    }
}
