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
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('alert_type', [
                'SUSPICIOUS_API_USAGE',
                'BULK_REQUESTS',
                'UNAUTHORIZED_ACCESS',
                'RATE_LIMIT_EXCEEDED',
                'EXTERNAL_ATTACK_SUSPECTED',
            ]);
            $table->string('ip_address', 45); // IPv6 support
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('endpoint')->nullable();
            $table->text('details')->nullable(); // JSON details
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('is_resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            // Indexes for quick querying
            $table->index(['ip_address', 'created_at']);
            $table->index(['alert_type', 'created_at']);
            $table->index(['is_resolved', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};
