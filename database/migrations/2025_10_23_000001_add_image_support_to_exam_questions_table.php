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
            // Add question image field - stores path to question image
            $table->string('question_image')->nullable()->after('question_text');
            
            // Add choices images field - stores JSON array of image paths for each choice
            // Format: {"1": "path/to/image1.jpg", "2": "path/to/image2.jpg", ...}
            $table->json('choices_images')->nullable()->after('choices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn(['question_image', 'choices_images']);
        });
    }
};
