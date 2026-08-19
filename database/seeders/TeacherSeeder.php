<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates sample teacher accounts
     */
    public function run(): void
    {
        // Sample Filipino teacher names
        $teachers = [
            ['first_name' => 'Maria', 'middle_name' => 'Santos', 'last_name' => 'Dela Cruz', 'gender' => 'female'],
            ['first_name' => 'Juan', 'middle_name' => 'Reyes', 'last_name' => 'Garcia', 'gender' => 'male'],
            ['first_name' => 'Ana', 'middle_name' => 'Flores', 'last_name' => 'Mendoza', 'gender' => 'female'],
            ['first_name' => 'Jose', 'middle_name' => 'Cruz', 'last_name' => 'Santos', 'gender' => 'male'],
            ['first_name' => 'Rosa', 'middle_name' => 'Aquino', 'last_name' => 'Reyes', 'gender' => 'female'],
            ['first_name' => 'Pedro', 'middle_name' => 'Bautista', 'last_name' => 'Torres', 'gender' => 'male'],
            ['first_name' => 'Elena', 'middle_name' => 'Villanueva', 'last_name' => 'Fernandez', 'gender' => 'female'],
            ['first_name' => 'Carlos', 'middle_name' => 'Gonzales', 'last_name' => 'Lopez', 'gender' => 'male'],
            ['first_name' => 'Sofia', 'middle_name' => 'Ramos', 'last_name' => 'Martinez', 'gender' => 'female'],
            ['first_name' => 'Miguel', 'middle_name' => 'Perez', 'last_name' => 'Rodriguez', 'gender' => 'male'],
            ['first_name' => 'Patricia', 'middle_name' => 'Sanchez', 'last_name' => 'Hernandez', 'gender' => 'female'],
            ['first_name' => 'Antonio', 'middle_name' => 'Rivera', 'last_name' => 'Morales', 'gender' => 'male'],
            ['first_name' => 'Carmen', 'middle_name' => 'Castro', 'last_name' => 'Diaz', 'gender' => 'female'],
            ['first_name' => 'Rafael', 'middle_name' => 'Romero', 'last_name' => 'Navarro', 'gender' => 'male'],
            ['first_name' => 'Lucia', 'middle_name' => 'Delgado', 'last_name' => 'Pascual', 'gender' => 'female'],
            ['first_name' => 'Fernando', 'middle_name' => 'Salvador', 'last_name' => 'Enriquez', 'gender' => 'male'],
            ['first_name' => 'Isabella', 'middle_name' => 'Aguilar', 'last_name' => 'Domingo', 'gender' => 'female'],
            ['first_name' => 'Gabriel', 'middle_name' => 'Mercado', 'last_name' => 'Santiago', 'gender' => 'male'],
            ['first_name' => 'Teresa', 'middle_name' => 'Luna', 'last_name' => 'Padilla', 'gender' => 'female'],
            ['first_name' => 'Manuel', 'middle_name' => 'Reyes', 'last_name' => 'Bello', 'gender' => 'male'],
        ];

        $createdTeachers = [];

        foreach ($teachers as $index => $teacher) {
            $email = strtolower($teacher['first_name'] . '.' . $teacher['last_name']) . '@tnhs.edu.ph';
            
            $createdTeacher = User::create([
                'first_name' => $teacher['first_name'],
                'middle_name' => $teacher['middle_name'],
                'last_name' => $teacher['last_name'],
                'email' => $email,
                'password' => Hash::make('teacher123'),
                'role' => 'teacher',
                'admission_status' => null,
                'gender' => $teacher['gender'],
                'date_of_birth' => now()->subYears(rand(28, 55))->subDays(rand(1, 365)),
                'phone_no' => '09' . rand(100000000, 999999999),
            ]);

            $createdTeachers[] = $createdTeacher;
        }

        // Assign some teachers as advisers to sections
        $sections = Section::all();
        $teacherIndex = 0;
        
        foreach ($sections as $section) {
            if ($teacherIndex < count($createdTeachers)) {
                $section->update(['adviser_id' => $createdTeachers[$teacherIndex]->id]);
                $teacherIndex++;
                
                // Reset index if we run out of teachers
                if ($teacherIndex >= count($createdTeachers)) {
                    $teacherIndex = 0;
                }
            }
        }

        $this->command->info("Created " . count($teachers) . " teacher accounts.");
        $this->command->info("Default password: teacher123");
        $this->command->info("Assigned teachers as advisers to sections.");
    }
}
