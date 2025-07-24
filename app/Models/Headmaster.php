<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Headmaster extends Model
{
    //
    protected $table = 'headmasters';

    protected $fillable = [
        'name',
        'nip',
        'email',
        'phone',
        'school_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
