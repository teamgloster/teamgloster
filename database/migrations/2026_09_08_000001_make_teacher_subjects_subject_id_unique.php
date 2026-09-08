<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateSubjectIds = DB::table('teacher_subjects')
            ->select('subject_id')
            ->groupBy('subject_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('subject_id');

        foreach ($duplicateSubjectIds as $subjectId) {
            $ids = DB::table('teacher_subjects')
                ->where('subject_id', $subjectId)
                ->orderBy('id')
                ->pluck('id');

            $ids->shift();

            if ($ids->isNotEmpty()) {
                DB::table('teacher_subjects')->whereIn('id', $ids)->delete();
            }
        }

        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->unique('subject_id');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropUnique(['subject_id']);
        });
    }
};
