<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Administrator Account
        User::updateOrCreate(
            ['email' => 'admin@tnhs.edu.ph'],
            [
                'first_name' => 'System',
                'middle_name' => null,
                'last_name' => 'Administrator',
                'suffix' => null,
                'email' => 'admin@tnhs.edu.ph',
                'phone_no' => '09123456789',
                'date_of_birth' => '1990-01-01',
                'gender' => 'male',
                'province' => 'Batangas',
                'municipality' => 'Tanauan',
                'barangay' => 'Poblacion',
                'lrn' => '000000000001',
                'role' => 'administrator',
                'admission_status' => null,
                'password' => Hash::make('admin123'),
            ]
        );

        $this->command->info('✓ Administrator account created!');
        $this->command->info('  Email: admin@tnhs.edu.ph');
        $this->command->info('  Password: admin123');
    }
}
