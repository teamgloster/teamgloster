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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('year_level_id')->constrained('year_levels')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->string('school_year'); // e.g., "2025-2026"
            $table->enum('semester', ['first', 'second'])->default('first');
            $table->enum('status', ['pending', 'approved', 'enrolled', 'rejected', 'dropped'])->default('pending');
            $table->enum('enrollment_type', ['new', 'old', 'transferee', 'returnee'])->default('new');
            $table->decimal('previous_gwa', 5, 2)->nullable();
            $table->string('previous_school')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['user_id', 'school_year', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
