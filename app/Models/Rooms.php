<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rooms extends Model
{
    //
    protected $table = 'rooms';

    protected $fillable = [
        'name', 'school_id', 'exam_type_id'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function examtype()
    {
        return $this->belongsTo(Examtype::class);
    }
}
