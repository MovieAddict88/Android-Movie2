<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('gem_reward')->default(0);
            $table->enum('recurrence', ['none', 'daily', 'weekly'])->default('none');
            $table->boolean('requires_approval')->default(true);
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('chore_child', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chore_id')->constrained()->onDelete('cascade');
            $table->foreignId('child_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'completed', 'verified'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chore_child');
        Schema::dropIfExists('chores');
    }
};
