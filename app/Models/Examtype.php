<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Examtype extends Model
{
    //
    protected $table = 'exam_types';
    protected $fillable = [
        'title',
        'school_id',
        'grade_id',
        'start_time',
        'end_time',
        'is_active',
        'is_global',
    ];

    public function rooms()
    {
        return $this->hasMany(Rooms::class);
    }
}
