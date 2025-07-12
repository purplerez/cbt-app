<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model
{
    //
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
}
