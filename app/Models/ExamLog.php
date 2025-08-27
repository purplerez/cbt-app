<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamLog extends Model
{
    //
    protected $table = 'exam_logs';

    protected $fillable = [
        'session_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
        'device'
    ];

    public function sessionExam(){
        return $this->belongsTo(ExamSession::class, 'session_id');
    }
}
