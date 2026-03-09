<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@Millénaire connect.local'],
            [
                'name' => 'Administrateur',
                'email' => 'admin@Millénaire connect.local',
                'password' => Hash::make('admin@123456'),
                'role' => 'admin',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1990-01-15',
            ]
        );

        // Teacher user (Professeur)
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@Millénaire connect.local'],
            [
                'name' => 'M. Jean Dupont',
                'email' => 'teacher@Millénaire connect.local',
                'password' => Hash::make('teacher@123456'),
                'role' => 'teacher',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1985-06-20',
            ]
        );

        // Create teacher relation if it doesn't exist
        if (!$teacher->teacher()->exists()) {
            Teacher::create([
                'user_id' => $teacher->id,
                'specialty' => 'Mathématiques',
                'experience_years' => 10,
                'is_active' => true,
            ]);
        }

        // Main teacher (Professeur Principal)
        $mainTeacher = User::firstOrCreate(
            ['email' => 'prof_principal@Millénaire connect.local'],
            [
                'name' => 'Mme Marie Martin',
                'email' => 'prof_principal@Millénaire connect.local',
                'password' => Hash::make('prof@123456'),
                'role' => 'teacher',
                'is_active' => true,
                'is_main_teacher' => true,
                'gender' => 'F',
                'date_of_birth' => '1988-03-10',
            ]
        );

        if (!$mainTeacher->teacher()->exists()) {
            Teacher::create([
                'user_id' => $mainTeacher->id,
                'specialty' => 'Français',
                'experience_years' => 12,
                'is_active' => true,
            ]);
        }

        // Parent user
        $parent = User::firstOrCreate(
            ['email' => 'parent@Millénaire connect.local'],
            [
                'name' => 'M. Pierre Bernard',
                'email' => 'parent@Millénaire connect.local',
                'password' => Hash::make('parent@123456'),
                'role' => 'parent',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1960-05-12',
            ]
        );

        if (!$parent->guardian()->exists()) {
            Guardian::create([
                'user_id' => $parent->id,
                'phone_number' => '+33612345678',
                'relation' => 'Père',
            ]);
        }

        // Student users
        $studentNames = [
            ['name' => 'Alice Duchamp', 'email' => 'alice@Millénaire connect.local'],
            ['name' => 'Bob Lefevre', 'email' => 'bob@Millénaire connect.local'],
            ['name' => 'Clara Martin', 'email' => 'clara@Millénaire connect.local'],
        ];

        foreach ($studentNames as $studentData) {
            $student = User::firstOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'email' => $studentData['email'],
                    'password' => Hash::make('student@123456'),
                    'role' => 'student',
                    'is_active' => true,
                    'gender' => fake()->randomElement(['M', 'F']),
                    'date_of_birth' => fake()->dateTimeBetween('-20 years')->format('Y-m-d'),
                ]
            );

            if (!$student->student()->exists()) {
                Student::create([
                    'user_id' => $student->id,
                    'matricule' => 'STU-' . str_pad($student->id, 6, '0', STR_PAD_LEFT),
                ]);
            }
        }

        $this->command->info('Default users seeded successfully!');
    }
}

