<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->index(['exam_id', 'status'], 'idx_exam_sessions_exam_status');
            $table->index(['user_id', 'status'], 'idx_exam_sessions_user_status');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index(['school_id', 'grade_id'], 'idx_students_school_grade');
        });

        Schema::table('preassigneds', function (Blueprint $table) {
            $table->index(['exam_id', 'user_id'], 'idx_preassigneds_exam_user');
        });
    }

    public function down(): void
    {
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_exam_sessions_exam_status');
            $table->dropIndex('idx_exam_sessions_user_status');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_school_grade');
        });

        Schema::table('preassigneds', function (Blueprint $table) {
            $table->dropIndex('idx_preassigneds_exam_user');
        });
    }
};
