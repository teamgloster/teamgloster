<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('admission_status', 20)->nullable()->after('role');
            $table->text('admission_remarks')->nullable()->after('admission_status');
            $table->unsignedBigInteger('admission_reviewed_by')->nullable()->after('admission_remarks');
            $table->timestamp('admission_reviewed_at')->nullable()->after('admission_reviewed_by');

            $table->foreign('admission_reviewed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // Existing students should remain able to enroll.
        DB::table('users')
            ->where('role', 'student')
            ->update(['admission_status' => 'approved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['admission_reviewed_by']);
            $table->dropColumn([
                'admission_status',
                'admission_remarks',
                'admission_reviewed_by',
                'admission_reviewed_at',
            ]);
        });
    }
};
