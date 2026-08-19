<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sections') && Schema::hasColumn('sections', 'room_id')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropConstrainedForeignId('room_id');
            });
        }

        Schema::dropIfExists('rooms');
    }

    public function down(): void
    {
        if (! Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('building')->nullable();
                $table->string('floor')->nullable();
                $table->integer('capacity')->nullable();
                $table->enum('room_type', [
                    'classroom',
                    'laboratory',
                    'computer_lab',
                    'library',
                    'auditorium',
                    'gymnasium',
                    'office',
                    'other',
                ])->default('classroom');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('sections') && ! Schema::hasColumn('sections', 'room_id')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            });
        }
    }
};
