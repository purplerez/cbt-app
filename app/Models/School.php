<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'npsn',
        'address',
        'phone',
        'email',
        'code',
        'logo',
        'status',
    ];

    public function headmasters()
    {
        return $this->hasOne(Headmaster::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function rooms()
    {
        return $this->hasMany(Rooms::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
