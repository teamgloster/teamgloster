<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY admission_status VARCHAR(20) NULL DEFAULT NULL');

        DB::table('users')
            ->where('role', '!=', 'student')
            ->update([
                'admission_status' => null,
                'admission_remarks' => null,
                'admission_reviewed_by' => null,
                'admission_reviewed_at' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->whereNull('admission_status')
            ->update(['admission_status' => 'pending']);

        DB::statement("ALTER TABLE users MODIFY admission_status VARCHAR(20) NOT NULL DEFAULT 'pending'");
    }
};
