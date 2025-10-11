<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentRooms extends Model
{
    protected $table = 'studentrooms';

    protected $fillable = [
        'student_id',
        'room_id',
        'exam_type_id',
    ];

    /**
     * Relationship to Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relationship to Room
     */
    public function room()
    {
        return $this->belongsTo(Rooms::class, 'room_id');
    }

    /**
     * Relationship to ExamType
     */
    public function examType()
    {
        return $this->belongsTo(Examtype::class, 'exam_type_id');
    }
}
