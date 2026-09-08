<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherSubjectSeeder extends Seeder
{
    /**
     * Assign each subject to at most one teacher.
     */
    public function run(): void
    {
        $teachers = User::where('role', 'teacher')->orderBy('last_name')->get();
        $subjects = Subject::orderBy('year_level_id')->orderBy('name')->get();

        if ($teachers->isEmpty() || $subjects->isEmpty()) {
            $this->command->info('No teachers or subjects found. Skipping teacher-subject assignments.');

            return;
        }

        foreach ($subjects->values() as $index => $subject) {
            $teacher = $teachers[$index % $teachers->count()];

            TeacherSubject::updateOrCreate(
                ['subject_id' => $subject->id],
                ['teacher_id' => $teacher->id],
            );
        }

        $totalAssignments = TeacherSubject::count();
        $this->command->info("Created {$totalAssignments} teacher-subject assignments.");
    }
}
