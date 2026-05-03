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
        Schema::create('job_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled', 'failed'])->default('completed');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('latitude_start', 10, 8)->nullable();
            $table->decimal('longitude_start', 11, 8)->nullable();
            $table->decimal('latitude_end', 10, 8)->nullable();
            $table->decimal('longitude_end', 11, 8)->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('sync_id')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('location_id');
            $table->index('worker_id');
            $table->index('status');
            $table->index('completed_at');
            $table->index('sync_id');
            $table->index(['company_id', 'completed_at']);
            $table->index(['worker_id', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_logs');
    }
};
