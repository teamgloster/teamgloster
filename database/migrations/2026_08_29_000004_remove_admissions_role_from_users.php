<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'admissions')
            ->orWhere('email', 'admissions@tnhs.edu.ph')
            ->delete();

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('administrator', 'teacher', 'student', 'registrar') NOT NULL DEFAULT 'student'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('administrator', 'teacher', 'student', 'registrar', 'admissions') NOT NULL DEFAULT 'student'");
    }
};
