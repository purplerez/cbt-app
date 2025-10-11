<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rooms extends Model
{
    //
    protected $table = 'rooms';

    protected $fillable = [
        'name',
        'capacity',
        'school_id',
        'exam_type_id'
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function examtype()
    {
        return $this->belongsTo(Examtype::class, 'exam_type_id');
    }

    /**
     * Relationship to Students (many-to-many through student_rooms)
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_rooms', 'room_id', 'student_id')
                    ->withPivot('exam_type_id')
                    ->withTimestamps();
    }

    /**
     * Get students for a specific exam type
     */
    public function getStudentsForExamType($examTypeId)
    {
        return $this->students()->wherePivot('exam_type_id', $examTypeId)->get();
    }
}
