<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //
    protected $fillable = [
        'user_id',
        'school_id',
        'nis',
        'name',
        'gender',
        'grade_id',
        'p_birth',
        'd_birth',
        'address',
        'photo',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    /* yet
    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
    public function scores()
    {
        return $this->hasMany(Score::class);
    }
        */

}
