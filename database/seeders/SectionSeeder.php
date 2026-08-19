<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\YearLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolYear = '2025-2026';

        // Create Year Levels
        $yearLevels = [
            [
                'name' => 'Grade 7',
                'code' => 'G7',
                'description' => 'First year of Junior High School',
                'level_type' => 'junior_high',
                'is_active' => true,
            ],
            [
                'name' => 'Grade 8',
                'code' => 'G8',
                'description' => 'Second year of Junior High School',
                'level_type' => 'junior_high',
                'is_active' => true,
            ],
            [
                'name' => 'Grade 9',
                'code' => 'G9',
                'description' => 'Third year of Junior High School',
                'level_type' => 'junior_high',
                'is_active' => true,
            ],
            [
                'name' => 'Grade 10',
                'code' => 'G10',
                'description' => 'Fourth year of Junior High School',
                'level_type' => 'junior_high',
                'is_active' => true,
            ],
            [
                'name' => 'Grade 11',
                'code' => 'G11',
                'description' => 'First year of Senior High School',
                'level_type' => 'senior_high',
                'is_active' => true,
            ],
            [
                'name' => 'Grade 12',
                'code' => 'G12',
                'description' => 'Second year of Senior High School',
                'level_type' => 'senior_high',
                'is_active' => true,
            ],
        ];

        foreach ($yearLevels as $levelData) {
            YearLevel::updateOrCreate(
                ['code' => $levelData['code']],
                $levelData
            );
        }

        $this->command->info('✓ Year levels created (Grade 7-12)');

        // Section names (Filipino flowers/values themed)
        $juniorHighSections = [
            'Sampaguita',
            'Rosal',
            'Camia',
            'Ilang-Ilang',
            'Gumamela',
        ];

        $seniorHighSections = [
            'STEM-A',
            'STEM-B',
            'ABM-A',
            'ABM-B',
            'HUMSS-A',
            'HUMSS-B',
            'GAS-A',
            'TVL-ICT',
            'TVL-HE',
        ];

        // Get created year levels
        $grade7 = YearLevel::where('code', 'G7')->first();
        $grade8 = YearLevel::where('code', 'G8')->first();
        $grade9 = YearLevel::where('code', 'G9')->first();
        $grade10 = YearLevel::where('code', 'G10')->first();
        $grade11 = YearLevel::where('code', 'G11')->first();
        $grade12 = YearLevel::where('code', 'G12')->first();

        // Create sections for Junior High (Grades 7-10)
        $juniorHighLevels = [$grade7, $grade8, $grade9, $grade10];

        foreach ($juniorHighLevels as $level) {
            if (!$level) continue;
            
            foreach ($juniorHighSections as $index => $sectionName) {
                $gradeNum = str_replace('G', '', $level->code);
                $code = $gradeNum . '-' . chr(65 + $index); // 7-A, 7-B, etc.
                
                Section::updateOrCreate(
                    [
                        'year_level_id' => $level->id,
                        'name' => $sectionName,
                        'school_year' => $schoolYear,
                    ],
                    [
                        'code' => $code,
                        'capacity' => 40,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('✓ Junior High sections created (5 sections per grade level)');

        // Create sections for Senior High (Grades 11-12)
        $seniorHighLevels = [$grade11, $grade12];

        foreach ($seniorHighLevels as $level) {
            if (!$level) continue;
            
            foreach ($seniorHighSections as $sectionName) {
                $gradeNum = str_replace('G', '', $level->code);
                $code = $gradeNum . '-' . $sectionName;
                
                Section::updateOrCreate(
                    [
                        'year_level_id' => $level->id,
                        'name' => $sectionName,
                        'school_year' => $schoolYear,
                    ],
                    [
                        'code' => $code,
                        'capacity' => 45,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('✓ Senior High sections created (9 sections per grade level)');

        // Summary
        $totalSections = Section::count();
        $totalYearLevels = YearLevel::count();
        
        $this->command->newLine();
        $this->command->info('=== Summary ===');
        $this->command->info("Total Year Levels: {$totalYearLevels}");
        $this->command->info("Total Sections: {$totalSections}");
        $this->command->info("School Year: {$schoolYear}");
    }
}
