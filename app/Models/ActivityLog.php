<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    //
    protected $table = "logs";

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'log_desc',
        'ip_address',
        'user_agent',
        'device',
        'created_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
