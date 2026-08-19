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
        Schema::create('student_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('requirement_type'); // form_137, picture_2x2, birth_certificate, good_moral_certificate
            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->enum('status', ['pending', 'submitted', 'verified', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'requirement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_requirements');
    }
};
