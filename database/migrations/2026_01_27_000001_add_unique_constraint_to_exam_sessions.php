<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, delete duplicate 'progress' sessions
        // Keep only the earliest progress session for each user-exam combination
        DB::statement("
            DELETE es1 FROM exam_sessions es1
            INNER JOIN (
                SELECT user_id, exam_id, MIN(id) as min_id
                FROM exam_sessions
                WHERE status = 'progress'
                GROUP BY user_id, exam_id
                HAVING COUNT(*) > 1
            ) es2
            ON es1.user_id = es2.user_id 
            AND es1.exam_id = es2.exam_id
            AND es1.status = 'progress'
            AND es1.id > es2.min_id
        ");

        // Note: We cannot add a unique constraint on (user_id, exam_id, status) 
        // because students can have multiple 'submited' sessions (retakes)
        // 
        // Instead, we'll rely on:
        // 1. Application-level check with lockForUpdate() in the controller
        // 2. Database transaction to ensure atomic operations
        //
        // The duplicate cleanup above ensures existing data is clean
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to rollback since we didn't add constraints
    }
};
