<?php

use App\Support\SchoolYear;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year', 9)->unique();
            $table->timestamps();
        });

        $years = collect();

        if (Schema::hasTable('school_settings')) {
            $years = $years->merge(
                DB::table('school_settings')->pluck('current_school_year')
            );
        }

        foreach (['sections', 'enrollments', 'grades', 'section_subject_teachers'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'school_year')) {
                $years = $years->merge(
                    DB::table($table)->whereNotNull('school_year')->distinct()->pluck('school_year')
                );
            }
        }

        $now = now();
        $years
            ->map(fn ($year) => SchoolYear::normalize((string) $year))
            ->filter()
            ->unique()
            ->each(function (string $year) use ($now) {
                DB::table('academic_years')->insertOrIgnore([
                    'year' => $year,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
