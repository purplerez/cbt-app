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
        // Add indexes on exam_sessions for frequently queried columns
        Schema::table('exam_sessions', function (Blueprint $table) {
            // Index for status queries (find sessions in progress)
            $table->index('status');

            // Compound index for user + status (find user's sessions by status)
            $table->index(['user_id', 'status']);

            // Compound index for exam + status (find participants in an exam)
            $table->index(['exam_id', 'status']);

            // Index for updated_at (recent activity queries)
            $table->index('updated_at');
        });

        // Add indexes on students for frequently queried columns
        Schema::table('students', function (Blueprint $table) {
            // Compound index for grade + school filters
            $table->index(['grade_id', 'school_id']);

            // Index for school queries (dashboard by school)
            $table->index('school_id');
        });

        // Add indexes on preassigned for frequently queried columns
        if (Schema::hasTable('preassigned')) {
            Schema::table('preassigned', function (Blueprint $table) {
                // Index for exam queries (find pre-assigned students for exam)
                $table->index('exam_id');

                // Compound index for user + exam
                $table->index(['user_id', 'exam_id']);
            });
        }

        // Add index on personal_access_tokens for token lookup
        if (Schema::hasTable('personal_access_tokens')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                // Compound index for valid token lookups
                $table->index(['user_id', 'expires_at']);
            });
        }
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
