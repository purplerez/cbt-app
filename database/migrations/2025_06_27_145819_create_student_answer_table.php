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
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('exam_sessions')->cascadeOnDelete();

            // JSON field to store all answers efficiently
            // Format: {"1":"A","2":"B","3":"C","4":"D"}
            $table->json('answers')->comment('JSON object containing question_id as key and answer as value (A,B,C,D)');

            // For essay answers (separate JSON for longer text)
            $table->json('essay_answers')->nullable()->comment('JSON object for essay type answers');

            // Metadata for tracking
            $table->integer('total_answered')->default(0)->comment('Count of answered questions');
            $table->timestamp('last_answered_at')->nullable()->comment('Last time student answered a question');

            // Auto-save tracking
            $table->timestamp('last_backup_at')->useCurrent()->comment('Last backup/save timestamp');
            $table->timestamps();

            // Ensure one record per session (one student, one exam session)
            $table->unique('session_id');

            // Index for performance
            $table->index(['session_id', 'last_backup_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
