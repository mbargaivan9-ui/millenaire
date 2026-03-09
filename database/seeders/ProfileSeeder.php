<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\Classe;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Seed student, teacher, and guardian profiles based on existing users
     */
    public function run(): void
    {
        echo "\n========== CREATING USER PROFILES ==========\n\n";

        // Get a default class
        $defaultClass = Classe::first();
        if (!$defaultClass) {
            echo "❌ No classes found. Please run ClassSeeder first.\n";
            return;
        }

        // ==================== STUDENTS ====================
        echo "👨‍🎓 Creating Student Profiles...\n";
        
        $studentEmails = [
            'student1@millenniaire.test',
            'student2@millenniaire.test',
            'student3@millenniaire.test',
            'student4@millenniaire.test',
            'student5@millenniaire.test',
        ];

        foreach ($studentEmails as $index => $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                Student::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'matricule' => 'STU-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                        'financial_status' => 'current',
                        'enrollment_date' => now()->subMonths(6),
                        'is_active' => true,
                    ]
                );
                echo "✅ Student: {$user->name} ({$email})\n";
            }
        }

        // ==================== TEACHERS ====================
        echo "\n👨‍🏫 Creating Teacher Profiles...\n";

        $teacherEmails = [
            'prof.mathematiques@millenniaire.test',
            'prof.francais@millenniaire.test',
            'prof.physique@millenniaire.test',
        ];

        $isHeadOfClass = true;
        foreach ($teacherEmails as $index => $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                Teacher::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'matricule' => 'MAT-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                        'qualification' => 'Master',
                        'years_experience' => rand(1, 10),
                        'is_active' => true,
                    ]
                );
                echo "✅ Teacher: {$user->name} ({$email})\n";
                $isHeadOfClass = false;
            }
        }

        // ==================== GUARDIANS ====================
        echo "\n👨‍👩‍👧 Creating Guardian Profiles...\n";

        $guardianEmails = [
            'parent1@millenniaire.test',
            'parent2@millenniaire.test',
        ];

        foreach ($guardianEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                Guardian::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'profession' => 'Professional',
                        'is_active' => true,
                    ]
                );
                echo "✅ Guardian: {$user->name} ({$email})\n";
            }
        }

        echo "\n========== ✅ PROFILES CREATED SUCCESSFULLY ==========\n\n";
    }
}
