<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->onDelete('cascade');
            $table->string('reward_type'); // 'ai_story', 'ai_coloring', 'custom'
            $table->string('reward_name');
            $table->integer('cost_gems');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->json('meta')->nullable(); // Store AI generation results or custom details
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemptions');
    }
};
