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
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // $table->foreignId('exam_id')->constrained('exams');
            $table->foreignId('school_id')->nullable()->constrained('schools');
            $table->foreignId('grade_id')->nullable()->constrained('grades');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('is_active', ['1','0'])->default('1');
            $table->enum('is_global', ['1','0'])->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
