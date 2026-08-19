<?php

namespace Database\Seeders;

use App\Models\TeacherSubject;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class TeacherSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Assigns subjects to teachers based on their specialization patterns.
     */
    public function run(): void
    {
        $teachers = User::where('role', 'teacher')->get();
        $subjects = Subject::all();

        if ($teachers->isEmpty() || $subjects->isEmpty()) {
            $this->command->info('No teachers or subjects found. Skipping teacher-subject assignments.');
            return;
        }

        // Group subjects by type/category
        $mathSubjects = $subjects->filter(function ($s) {
            return str_contains(strtolower($s->code), 'math') || 
                   str_contains(strtolower($s->name), 'mathematics') ||
                   str_contains(strtolower($s->code), 'stat');
        });

        $scienceSubjects = $subjects->filter(function ($s) {
            return str_contains(strtolower($s->code), 'sci') || 
                   str_contains(strtolower($s->name), 'science') ||
                   str_contains(strtolower($s->code), 'earth') ||
                   str_contains(strtolower($s->code), 'phy');
        });

        $englishSubjects = $subjects->filter(function ($s) {
            return str_contains(strtolower($s->code), 'eng') || 
                   str_contains(strtolower($s->name), 'english') ||
                   str_contains(strtolower($s->code), 'oral') ||
                   str_contains(strtolower($s->code), 'read') ||
                   str_contains(strtolower($s->code), 'lit') ||
                   str_contains(strtolower($s->code), 'mil');
        });

        $filipinoSubjects = $subjects->filter(function ($s) {
            return str_contains(strtolower($s->code), 'fil') || 
                   str_contains(strtolower($s->name), 'filipino') ||
                   str_contains(strtolower($s->code), 'kom') ||
                   str_contains(strtolower($s->code), 'pagbasa');
        });

        $socialStudiesSubjects = $subjects->filter(function ($s) {
            return str_contains(strtolower($s->code), 'ap') || 
                   str_contains(strtolower($s->name), 'araling') ||
                   str_contains(strtolower($s->code), 'ucsp') ||
                   str_contains(strtolower($s->code), 'philo');
        });

        $tleSubjects = $subjects->filter(function ($s) {
            return str_contains(strtolower($s->code), 'tle') || 
                   str_contains(strtolower($s->name), 'technology') ||
                   str_contains(strtolower($s->code), 'emp') ||
                   str_contains(strtolower($s->code), 'entrep');
        });

        $mapehSubjects = $subjects->filter(function ($s) {
            return str_contains(strtolower($s->code), 'mapeh') || 
                   str_contains(strtolower($s->code), 'peh') ||
                   str_contains(strtolower($s->code), 'art') ||
                   str_contains(strtolower($s->name), 'arts');
        });

        $valuesSubjects = $subjects->filter(function ($s) {
            return str_contains(strtolower($s->code), 'esp') || 
                   str_contains(strtolower($s->name), 'edukasyon') ||
                   str_contains(strtolower($s->code), 'perdev');
        });

        $researchSubjects = $subjects->filter(function ($s) {
            return str_contains(strtolower($s->code), 'pr') || 
                   str_contains(strtolower($s->name), 'research') ||
                   str_contains(strtolower($s->code), 'inquire');
        });

        // Define specialization groups
        $specializations = [
            $mathSubjects,
            $scienceSubjects,
            $englishSubjects,
            $filipinoSubjects,
            $socialStudiesSubjects,
            $tleSubjects,
            $mapehSubjects,
            $valuesSubjects,
            $researchSubjects,
        ];

        // Assign teachers to subjects
        $teacherIndex = 0;
        foreach ($specializations as $specializationSubjects) {
            if ($specializationSubjects->isEmpty()) continue;

            // Get 2-3 teachers for each specialization
            $numTeachers = min(rand(2, 3), $teachers->count() - $teacherIndex);
            
            for ($i = 0; $i < $numTeachers && $teacherIndex < $teachers->count(); $i++) {
                $teacher = $teachers[$teacherIndex % $teachers->count()];
                
                foreach ($specializationSubjects as $subject) {
                    // Create assignment if not exists
                    TeacherSubject::firstOrCreate([
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                    ]);
                }
                
                $teacherIndex++;
            }
        }

        // Make sure each teacher has at least some subjects
        foreach ($teachers as $teacher) {
            $existingCount = TeacherSubject::where('teacher_id', $teacher->id)->count();
            
            if ($existingCount === 0) {
                // Assign 3-5 random subjects
                $randomSubjects = $subjects->random(min(rand(3, 5), $subjects->count()));
                foreach ($randomSubjects as $subject) {
                    TeacherSubject::firstOrCreate([
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                    ]);
                }
            }
        }

        $totalAssignments = TeacherSubject::count();
        $this->command->info("Created {$totalAssignments} teacher-subject assignments.");
    }
}
