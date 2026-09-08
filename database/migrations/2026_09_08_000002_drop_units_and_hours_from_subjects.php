<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter([
            Schema::hasColumn('subjects', 'units') ? 'units' : null,
            Schema::hasColumn('subjects', 'hours_per_week') ? 'hours_per_week' : null,
        ]));

        if ($columns === []) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (! Schema::hasColumn('subjects', 'units')) {
                $table->integer('units')->default(1);
            }
            if (! Schema::hasColumn('subjects', 'hours_per_week')) {
                $table->integer('hours_per_week')->default(4);
            }
        });
    }
};
