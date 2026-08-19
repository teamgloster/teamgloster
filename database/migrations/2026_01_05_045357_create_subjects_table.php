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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Mathematics", "English", "Science"
            $table->string('code')->unique(); // e.g., "MATH7", "ENG7", "SCI7"
            $table->text('description')->nullable();
            $table->foreignId('year_level_id')->nullable()->constrained('year_levels')->onDelete('set null');
            $table->enum('subject_type', ['core', 'specialized', 'applied', 'elective'])->default('core');
            $table->integer('units')->default(1);
            $table->integer('hours_per_week')->default(4);
            $table->enum('semester', ['first', 'second', 'full_year'])->default('full_year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
