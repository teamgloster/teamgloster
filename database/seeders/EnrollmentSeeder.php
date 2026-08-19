<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\YearLevel;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates test students with enrollments and GWA for testing auto-assign feature
     */
    public function run(): void
    {
        // Get year levels
        $yearLevels = YearLevel::all();
        
        if ($yearLevels->isEmpty()) {
            $this->command->warn('No year levels found. Please run SectionSeeder first.');
            return;
        }

        $currentSchoolYear = '2025-2026';
        
        // Sample Filipino names
        $firstNames = [
            'Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Rosa', 'Carlos', 'Elena',
            'Miguel', 'Sofia', 'Antonio', 'Lucia', 'Rafael', 'Carmen', 'Fernando',
            'Isabella', 'Gabriel', 'Patricia', 'Manuel', 'Victoria', 'Ricardo',
            'Angela', 'Eduardo', 'Teresa', 'Francisco', 'Margarita', 'Alberto',
            'Cristina', 'Rodrigo', 'Beatriz', 'Alejandro', 'Daniela', 'Jorge',
            'Paula', 'Luis', 'Andrea', 'Diego', 'Nicole', 'Oscar', 'Jasmine',
            'Marco', 'Samantha', 'Andres', 'Michelle', 'Enrique', 'Katherine',
            'Roberto', 'Stephanie', 'Arturo', 'Jennifer', 'Ramon', 'Christine'
        ];
        
        $lastNames = [
            'Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza', 'Torres', 'Flores',
            'Gonzales', 'Ramos', 'Aquino', 'Bautista', 'Villanueva', 'Fernandez',
            'Lopez', 'Martinez', 'Rodriguez', 'Hernandez', 'Perez', 'Sanchez',
            'Rivera', 'Morales', 'Castro', 'Diaz', 'Romero', 'Navarro', 'Delgado',
            'Pascual', 'Salvador', 'Enriquez', 'Aguilar'
        ];
        
        $middleNames = [
            'De La Cruz', 'Dela Rosa', 'San Juan', 'De Guzman', 'Del Rosario',
            'De Leon', 'San Pedro', 'De Los Santos', 'Santa Maria', 'San Miguel'
        ];

        $studentsPerYearLevel = 30; // 30 students per year level for testing
        $studentCount = 0;

        foreach ($yearLevels as $yearLevel) {
            $this->command->info("Creating students for {$yearLevel->name}...");
            
            for ($i = 1; $i <= $studentsPerYearLevel; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $middleName = $middleNames[array_rand($middleNames)];
                
                // Generate unique LRN (12 digits)
                $lrn = sprintf('%012d', 100000000000 + $studentCount + $i + ($yearLevel->id * 1000));
                
                // Create student user
                $student = User::create([
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'email' => strtolower($firstName . '.' . $lastName . $lrn) . '@student.tnhs.edu.ph',
                    'password' => Hash::make('password123'),
                    'role' => 'student',
                    'admission_status' => 'approved',
                    'lrn' => $lrn,
                    'gender' => rand(0, 1) ? 'male' : 'female',
                    'date_of_birth' => now()->subYears(rand(12, 18))->subDays(rand(1, 365)),
                    'phone_no' => '09' . rand(100000000, 999999999),
                    'guardian_full_name' => $lastNames[array_rand($lastNames)] . ', ' . $firstNames[array_rand($firstNames)],
                    'guardian_contact_no' => '09' . rand(100000000, 999999999),
                ]);

                // Generate realistic GWA (75-99)
                // Create a distribution: more students in 80-90 range
                $gwaBase = rand(75, 99);
                $gwa = min(99, max(75, $gwaBase + (rand(-5, 5) / 10)));
                
                Enrollment::create([
                    'user_id' => $student->id,
                    'year_level_id' => $yearLevel->id,
                    'section_id' => null,
                    'school_year' => $currentSchoolYear,
                    'previous_gwa' => number_format($gwa, 2),
                    'status' => 'pending',
                    'created_at' => now()->subDays(rand(1, 30)),
                ]);

                $studentCount++;
            }
        }

        $this->command->info("Created {$studentCount} students with pending enrollments.");
    }
}
