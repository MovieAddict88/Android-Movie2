<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('student_id_number')->unique();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('photo_url')->nullable();
            $table->integer('total_points')->default(0);
            $table->integer('current_streak')->default(0);
            $table->integer('tardies_count')->default(0);
            $table->integer('missing_assignments_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
