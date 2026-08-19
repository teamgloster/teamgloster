<?php

namespace Database\Seeders;

use App\Models\YearLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class YearLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $yearLevels = [
            [
                'name' => 'Grade 7',
                'code' => 'G7',
                'description' => 'First year of Junior High School',
                'level_type' => 'junior_high',
            ],
            [
                'name' => 'Grade 8',
                'code' => 'G8',
                'description' => 'Second year of Junior High School',
                'level_type' => 'junior_high',
            ],
            [
                'name' => 'Grade 9',
                'code' => 'G9',
                'description' => 'Third year of Junior High School',
                'level_type' => 'junior_high',
            ],
            [
                'name' => 'Grade 10',
                'code' => 'G10',
                'description' => 'Fourth year of Junior High School',
                'level_type' => 'junior_high',
            ],
            [
                'name' => 'Grade 11',
                'code' => 'G11',
                'description' => 'First year of Senior High School',
                'level_type' => 'senior_high',
            ],
            [
                'name' => 'Grade 12',
                'code' => 'G12',
                'description' => 'Second year of Senior High School',
                'level_type' => 'senior_high',
            ],
        ];

        foreach ($yearLevels as $level) {
            YearLevel::updateOrCreate(
                ['code' => $level['code']],
                $level
            );
        }
    }
}
