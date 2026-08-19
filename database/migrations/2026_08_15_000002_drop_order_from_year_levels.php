<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('year_levels') && Schema::hasColumn('year_levels', 'order')) {
            Schema::table('year_levels', function (Blueprint $table) {
                $table->dropColumn('order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('year_levels') && ! Schema::hasColumn('year_levels', 'order')) {
            Schema::table('year_levels', function (Blueprint $table) {
                $table->integer('order')->default(0);
            });
        }
    }
};
