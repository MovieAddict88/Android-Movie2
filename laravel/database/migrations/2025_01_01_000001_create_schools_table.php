<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('logo_path')->nullable();
            $table->json('behavior_categories')->nullable();
            $table->string('database_name')->unique();
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('subdomain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
