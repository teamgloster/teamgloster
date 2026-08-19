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
        // Pivot table: which subjects a teacher CAN teach
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['teacher_id', 'subject_id']);
        });

        // Assignment table: which teacher teaches which subject in which section
        Schema::create('section_subject_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('school_year');
            $table->enum('semester', ['first', 'second', 'full_year'])->default('full_year');
            $table->string('schedule')->nullable(); // e.g., "MWF 8:00-9:00 AM"
            $table->string('room')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['section_id', 'subject_id', 'school_year', 'semester'], 'section_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_subject_teachers');
        Schema::dropIfExists('teacher_subjects');
    }
};
