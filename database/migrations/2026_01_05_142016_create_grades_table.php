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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('school_year');
            $table->decimal('term_1', 5, 2)->nullable();
            $table->decimal('term_2', 5, 2)->nullable();
            $table->decimal('term_3', 5, 2)->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->timestamps();
            
            // Unique constraint: one grade record per student per subject per school year
            $table->unique(['student_id', 'subject_id', 'school_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
