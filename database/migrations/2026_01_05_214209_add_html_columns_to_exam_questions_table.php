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
        Schema::table('exam_questions', function (Blueprint $table) {
            // Store question text with HTML formatting (bold, italic, underline, strikethrough)
            $table->text('question_html')->nullable()->after('question_text');

            // Store choice options with HTML formatting
            $table->json('choice_html')->nullable()->after('choices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn(['question_html', 'choice_html']);
        });
    }
};
