<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    //
    protected $fillable = [
        'nip',
        'name',
        'gender',
        'address',
        'photo',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects');
    }

    /*yet
    public function classes()
    {
        return $this->belongsToMany(Classroom::class, 'teacher_classrooms');
    }
    public function students()
    {
        return $this->hasMany(Student::class);
        // or possibly: return $this->belongsToMany(Student::class);
    }
        */


}
