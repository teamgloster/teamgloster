<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\YearLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Junior High School Core Subjects (Grades 7-10)
        $juniorHighSubjects = [
            ['name' => 'Filipino', 'code_prefix' => 'FIL', 'type' => 'core'],
            ['name' => 'English', 'code_prefix' => 'ENG', 'type' => 'core'],
            ['name' => 'Mathematics', 'code_prefix' => 'MATH', 'type' => 'core'],
            ['name' => 'Science', 'code_prefix' => 'SCI', 'type' => 'core'],
            ['name' => 'Araling Panlipunan', 'code_prefix' => 'AP', 'type' => 'core'],
            ['name' => 'Edukasyon sa Pagpapakatao', 'code_prefix' => 'ESP', 'type' => 'core'],
            ['name' => 'Technology and Livelihood Education', 'code_prefix' => 'TLE', 'type' => 'core'],
            ['name' => 'MAPEH', 'code_prefix' => 'MAPEH', 'type' => 'core'],
        ];

        $juniorHighGrades = ['G7', 'G8', 'G9', 'G10'];

        foreach ($juniorHighGrades as $gradeCode) {
            $yearLevel = YearLevel::where('code', $gradeCode)->first();
            if ($yearLevel) {
                $gradeNum = str_replace('G', '', $gradeCode);
                foreach ($juniorHighSubjects as $subject) {
                    Subject::updateOrCreate(
                        ['code' => $subject['code_prefix'] . $gradeNum],
                        [
                            'name' => $subject['name'] . ' ' . $gradeNum,
                            'description' => $subject['name'] . ' for Grade ' . $gradeNum,
                            'year_level_id' => $yearLevel->id,
                            'subject_type' => $subject['type'],
                            'semester' => 'full_year',
                        ]
                    );
                }
            }
        }

        // Senior High School Core Subjects (Grades 11-12)
        $seniorHighCore = [
            ['name' => 'Oral Communication', 'code' => 'ORALCOMM', 'semester' => 'first'],
            ['name' => 'Reading and Writing', 'code' => 'READWRITE', 'semester' => 'second'],
            ['name' => 'Komunikasyon at Pananaliksik sa Wika at Kulturang Pilipino', 'code' => 'KOMFIL', 'semester' => 'first'],
            ['name' => 'Pagbasa at Pagsusuri ng Iba\'t Ibang Teksto', 'code' => 'PAGBASA', 'semester' => 'second'],
            ['name' => 'General Mathematics', 'code' => 'GENMATH', 'semester' => 'first'],
            ['name' => 'Statistics and Probability', 'code' => 'STATPROB', 'semester' => 'second'],
            ['name' => 'Earth and Life Science', 'code' => 'EARTHLIFE', 'semester' => 'first'],
            ['name' => 'Physical Science', 'code' => 'PHYSCI', 'semester' => 'second'],
            ['name' => 'Personal Development', 'code' => 'PERDEV', 'semester' => 'first'],
            ['name' => 'Understanding Culture, Society and Politics', 'code' => 'UCSP', 'semester' => 'second'],
            ['name' => 'Introduction to Philosophy of the Human Person', 'code' => 'PHILO', 'semester' => 'first'],
            ['name' => 'Physical Education and Health', 'code' => 'PEH', 'semester' => 'full_year'],
        ];

        $grade11 = YearLevel::where('code', 'G11')->first();
        if ($grade11) {
            foreach ($seniorHighCore as $subject) {
                Subject::updateOrCreate(
                    ['code' => $subject['code'] . '11'],
                    [
                        'name' => $subject['name'],
                        'description' => $subject['name'] . ' for Grade 11',
                        'year_level_id' => $grade11->id,
                        'subject_type' => 'core',
                        'semester' => $subject['semester'],
                    ]
                );
            }
        }

        $grade12 = YearLevel::where('code', 'G12')->first();
        if ($grade12) {
            // Grade 12 Core subjects
            $grade12Core = [
                ['name' => '21st Century Literature from the Philippines and the World', 'code' => '21STLIT12', 'semester' => 'first'],
                ['name' => 'Contemporary Philippine Arts from the Regions', 'code' => 'CONARTS12', 'semester' => 'first'],
                ['name' => 'Media and Information Literacy', 'code' => 'MIL12', 'semester' => 'second'],
                ['name' => 'Practical Research 1', 'code' => 'PR112', 'semester' => 'first'],
                ['name' => 'Practical Research 2', 'code' => 'PR212', 'semester' => 'second'],
                ['name' => 'Empowerment Technologies', 'code' => 'EMPTECH12', 'semester' => 'first'],
                ['name' => 'Entrepreneurship', 'code' => 'ENTREP12', 'semester' => 'second'],
                ['name' => 'Inquiries, Investigations and Immersion', 'code' => 'INQUIRE12', 'semester' => 'second'],
            ];

            foreach ($grade12Core as $subject) {
                Subject::updateOrCreate(
                    ['code' => $subject['code']],
                    [
                        'name' => $subject['name'],
                        'description' => $subject['name'] . ' for Grade 12',
                        'year_level_id' => $grade12->id,
                        'subject_type' => 'core',
                        'semester' => $subject['semester'],
                    ]
                );
            }
        }
    }
}
