<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Indexes already exist - migration skipped to avoid duplicates
        // Schema::table('exam_sessions', function (Blueprint $table) {
        //     $table->index('status');
        //     $table->index(['user_id', 'status']);
        //     $table->index(['exam_id', 'status']);
        //     $table->index('updated_at');
        // });
        // ... other indexes commented out
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['exam_id', 'status']);
            $table->dropIndex(['updated_at']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['grade_id', 'school_id']);
            $table->dropIndex(['school_id']);
        });

        if (Schema::hasTable('preassigned')) {
            Schema::table('preassigned', function (Blueprint $table) {
                $table->dropIndex(['exam_id']);
                $table->dropIndex(['user_id', 'exam_id']);
            });
        }

        if (Schema::hasTable('personal_access_tokens')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'expires_at']);
            });
        }
    }
};
