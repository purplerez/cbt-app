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
        Schema::create('berita_acara', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke exam type (ujian global)
            $table->foreignId('exam_type_id')->constrained('exam_types')->cascadeOnDelete();
            
            // Relasi ke exam (mata pelajaran ujian)
            $table->foreignId('exam_id')->nullable()->constrained('exams')->cascadeOnDelete();
            
            // Relasi ke sekolah
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            
            // Relasi ke ruangan (optional)
            $table->foreignId('room_id')->nullable()->constrained('rooms')->cascadeOnDelete();
            
            // Nomor berita acara (auto-generated)
            $table->string('nomor_ba')->unique();
            
            // Tanggal pelaksanaan ujian
            $table->date('tanggal_pelaksanaan');
            
            // Waktu mulai dan selesai
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            
            // Jumlah peserta
            $table->integer('jumlah_peserta_hadir')->default(0);
            $table->integer('jumlah_peserta_tidak_hadir')->default(0);
            $table->integer('jumlah_peserta_terdaftar')->default(0);
            
            // Pengawas/Proctor
            $table->json('pengawas')->nullable()->comment('Array of teacher/headmaster IDs who supervised');
            
            // Catatan kejadian
            $table->text('catatan_khusus')->nullable()->comment('Special notes/incidents during exam');
            
            // Kondisi pelaksanaan
            $table->enum('kondisi_pelaksanaan', ['lancar', 'ada_kendala', 'terganggu'])->default('lancar');
            $table->text('kendala')->nullable()->comment('Details of any problems');
            
            // Kondisi ruangan dan peralatan
            $table->enum('kondisi_ruangan', ['baik', 'cukup', 'kurang'])->default('baik');
            $table->enum('kondisi_peralatan', ['baik', 'cukup', 'kurang'])->default('baik');
            
            // Status berita acara
            $table->enum('status', ['draft', 'finalized', 'approved', 'archived'])->default('draft');
            
            // Tanda tangan digital (base64 atau path ke file)
            $table->text('ttd_pengawas_1')->nullable();
            $table->text('ttd_pengawas_2')->nullable();
            $table->text('ttd_kepala_sekolah')->nullable();
            
            // Metadata
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes untuk performa
            $table->index(['exam_type_id', 'school_id', 'tanggal_pelaksanaan']);
            $table->index(['status', 'created_at']);
            $table->index('nomor_ba');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita_acara');
    }
};
