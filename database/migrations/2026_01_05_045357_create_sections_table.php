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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('year_level_id')->constrained('year_levels')->onDelete('cascade');
            $table->string('name'); // e.g., "Section A", "Section B", "Sampaguita", "Rose"
            $table->string('code')->nullable(); // e.g., "7A", "7B"
            $table->foreignId('adviser_id')->nullable()->constrained('users')->onDelete('set null'); // Teacher adviser
            $table->integer('capacity')->default(40); // Max students
            $table->string('school_year')->nullable(); // e.g., "2025-2026"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['year_level_id', 'name', 'school_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
