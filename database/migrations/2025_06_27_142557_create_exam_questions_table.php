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
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->enum('question_type_id', ['0', '1', '2', '3', '4'])->default('0'); // 0: Essay, 1: Multiple Choice, 2: True/False, 3: Fill in the Blank, 4: Matching
            $table->text('question_text');
            $table->json('choices')->nullable();
            $table->text('answer_key');
            $table->decimal('points', 5, 2)->default(1.00);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            // $table->foreignId('school_id')->constrained('schools')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
