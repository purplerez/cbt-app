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
        Schema::create('upload_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->text('option_a')->nullable();
            $table->string('option_a_image')->nullable();
            $table->text('option_b')->nullable();
            $table->string('option_b_image')->nullable();
            $table->text('option_c')->nullable();
            $table->string('option_c_image')->nullable();
            $table->text('option_d')->nullable();
            $table->string('option_d_image')->nullable();
            $table->string('correct_answer', 1)->nullable();
            $table->integer('points')->default(1);
            $table->string('image_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_questions');
    }
};
