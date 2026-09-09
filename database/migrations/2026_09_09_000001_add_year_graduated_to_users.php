<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'year_graduated')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('year_graduated')->nullable()->after('previous_school');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'year_graduated')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('year_graduated');
        });
    }
};
