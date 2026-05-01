<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->integer('age')->nullable();
            $table->string('reading_level')->default('beginner'); // beginner, intermediate, advanced
            $table->json('interests')->nullable();
            $table->integer('gems')->default(0);
            $table->boolean('ai_enabled')->default(false);
            $table->integer('daily_ai_limit_minutes')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
