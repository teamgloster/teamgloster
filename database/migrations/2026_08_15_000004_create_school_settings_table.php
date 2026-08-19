<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('school_id')->nullable();
            $table->string('division')->nullable();
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('current_school_year', 20);
            $table->unsignedTinyInteger('current_term')->default(1);
            $table->boolean('enrollment_open')->default(true);
            $table->timestamps();
        });

        $month = (int) date('n');
        $year = (int) date('Y');
        $schoolYear = $month >= 8
            ? $year.'-'.($year + 1)
            : ($year - 1).'-'.$year;

        DB::table('school_settings')->insert([
            'school_name' => 'Tambo National High School',
            'school_id' => null,
            'division' => 'Schools Division of Camarines Sur',
            'district' => 'Buhi',
            'address' => 'Tambo, Buhi, Camarines Sur',
            'municipality' => 'Buhi',
            'province' => 'Camarines Sur',
            'contact_number' => null,
            'email' => 'admin@tnhs.edu.ph',
            'current_school_year' => $schoolYear,
            'current_term' => 1,
            'enrollment_open' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
