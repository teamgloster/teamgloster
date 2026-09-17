<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('strands')->insert([
            [
                'name' => 'Science, Technology, Engineering, and Mathematics',
                'code' => 'STEM',
                'description' => 'Academic track for science, technology, engineering, and mathematics.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Humanities and Social Sciences',
                'code' => 'HUMSS',
                'description' => 'Academic track for the humanities and social sciences.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Accountancy, Business, and Management',
                'code' => 'ABM',
                'description' => 'Academic track for accountancy, business, and management.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'General Academic Strand',
                'code' => 'GAS',
                'description' => 'Academic track with a general academic curriculum.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Technical-Vocational-Livelihood',
                'code' => 'TVL',
                'description' => 'Technical-vocational track and specializations.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('strands');
    }
};
