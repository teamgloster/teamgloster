<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->string('region')->nullable()->after('school_id');
            $table->string('school_head')->nullable()->after('email');
        });

        DB::table('school_settings')->whereNull('region')->update([
            'region' => 'Region V (Bicol)',
        ]);
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['region', 'school_head']);
        });
    }
};
