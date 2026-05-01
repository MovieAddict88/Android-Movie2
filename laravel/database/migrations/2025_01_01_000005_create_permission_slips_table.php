<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_slips', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('cost', 8, 2)->default(0.00);
            $table->timestamp('due_date');
            $table->foreignId('creator_id');
            $table->timestamps();
        });

        Schema::create('permission_slip_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_slip_id')->constrained();
            $table->foreignId('student_id')->constrained();
            $table->foreignId('parent_id');
            $table->boolean('is_signed')->default(false);
            $table->string('digital_signature')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_slip_responses');
        Schema::dropIfExists('permission_slips');
    }
};
