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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // ID, Driver's License, Health Cert, etc.
            $table->string('file_path');
            $table->date('expiry_date')->nullable();
            $table->string('document_number')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->json('ai_extracted_data')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('compliance_score')->default(100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('compliance_score');
        });
    }
};
