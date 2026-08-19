<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('grades') || ! Schema::hasColumn('grades', 'q1')) {
            return;
        }

        Schema::table('grades', function (Blueprint $table) {
            if (! Schema::hasColumn('grades', 'term_1')) {
                $table->decimal('term_1', 5, 2)->nullable()->after('school_year');
            }
            if (! Schema::hasColumn('grades', 'term_2')) {
                $table->decimal('term_2', 5, 2)->nullable()->after('term_1');
            }
            if (! Schema::hasColumn('grades', 'term_3')) {
                $table->decimal('term_3', 5, 2)->nullable()->after('term_2');
            }
        });

        DB::table('grades')->update([
            'term_1' => DB::raw('q1'),
            'term_2' => DB::raw('q2'),
            'term_3' => DB::raw('q3'),
        ]);

        Schema::table('grades', function (Blueprint $table) {
            $drop = array_values(array_filter(['q1', 'q2', 'q3', 'q4'], function ($column) {
                return Schema::hasColumn('grades', $column);
            }));

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('grades') || ! Schema::hasColumn('grades', 'term_1')) {
            return;
        }

        Schema::table('grades', function (Blueprint $table) {
            $table->decimal('q1', 5, 2)->nullable();
            $table->decimal('q2', 5, 2)->nullable();
            $table->decimal('q3', 5, 2)->nullable();
            $table->decimal('q4', 5, 2)->nullable();
        });

        DB::table('grades')->update([
            'q1' => DB::raw('term_1'),
            'q2' => DB::raw('term_2'),
            'q3' => DB::raw('term_3'),
        ]);

        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['term_1', 'term_2', 'term_3']);
        });
    }
};
