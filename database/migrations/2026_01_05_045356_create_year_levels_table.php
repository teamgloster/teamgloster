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
        Schema::create('year_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Grade 7", "Grade 8", etc.
            $table->string('code')->unique(); // e.g., "G7", "G8", "G9", "G10", "G11", "G12"
            $table->text('description')->nullable();
            $table->enum('level_type', ['junior_high', 'senior_high'])->default('junior_high');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('year_levels');
    }
};
