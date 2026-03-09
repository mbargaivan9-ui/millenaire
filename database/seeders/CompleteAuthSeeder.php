<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\Classe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompleteAuthSeeder extends Seeder
{
    /**
     * Seed complete authentication test data for all user roles
     */
    public function run(): void
    {
        echo "\n========== INTÉGRATION AUTHENTIFICATION COMPLÈTE ==========\n\n";

        // ==================== ADMIN ROLES ====================
        echo "👨‍💻 Création des rôles administratifs...\n";

        // Admin Principal
        $admin = User::firstOrCreate(
            ['email' => 'admin@millenniaire.test'],
            [
                'name' => 'Administrateur Principal',
                'password' => Hash::make('Admin@12345'),
                'role' => 'admin',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1985-01-15',
                'address' => 'Douala - Centre',
                'city' => 'Douala',
                'country' => 'Cameroun',
                'phoneNumber' => '+237670000001',
            ]
        );
        echo "✅ Admin: admin@millenniaire.test / Admin@12345\n";

        // ==================== TEACHERS ====================
        echo "\n👨‍🏫 Création des Professeurs...\n";

        // Teacher 1 - Mathematics
        User::firstOrCreate(
            ['email' => 'prof.mathematiques@millenniaire.test'],
            [
                'name' => 'Mr. Emmanuel DONGMO',
                'password' => Hash::make('Prof@12345'),
                'role' => 'teacher',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1985-03-20',
                'address' => 'Douala',
                'city' => 'Douala',
                'country' => 'Cameroun',
                'phoneNumber' => '+237691234567',
            ]
        );
        echo "✅ Professeur Mathématiques: prof.mathematiques@millenniaire.test / Prof@12345\n";

        // Teacher 2 - French (Main teacher)
        User::firstOrCreate(
            ['email' => 'prof.francais@millenniaire.test'],
            [
                'name' => 'Mme ABANDA Marie',
                'password' => Hash::make('Prof@12345'),
                'role' => 'teacher',
                'is_active' => true,
                'gender' => 'F',
                'date_of_birth' => '1980-07-10',
                'address' => 'Douala',
                'city' => 'Douala',
                'country' => 'Cameroun',
                'phoneNumber' => '+237692234567',
            ]
        );
        echo "✅ Professeur Français: prof.francais@millenniaire.test / Prof@12345\n";

        // Teacher 3 - Physics
        User::firstOrCreate(
            ['email' => 'prof.physique@millenniaire.test'],
            [
                'name' => 'Mr. Alain NKODO',
                'password' => Hash::make('Prof@12345'),
                'role' => 'teacher',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1988-11-30',
                'address' => 'Douala',
                'city' => 'Douala',
                'country' => 'Cameroun',
                'phoneNumber' => '+237693234567',
            ]
        );
        echo "✅ Professeur Physique: prof.physique@millenniaire.test / Prof@12345\n";

        // ==================== PARENTS (Guardians) ====================
        echo "\n👨‍👩‍👧 Création des Parents...\n";

        // Parent 1
        User::firstOrCreate(
            ['email' => 'parent1@millenniaire.test'],
            [
                'name' => 'Mr. KOUADJE Ignace',
                'password' => Hash::make('Parent@12345'),
                'role' => 'parent',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1975-02-14',
                'address' => 'Douala - Akwa',
                'city' => 'Douala',
                'country' => 'Cameroun',
                'phoneNumber' => '+237671111111',
            ]
        );
        echo "✅ Parent 1: parent1@millenniaire.test / Parent@12345\n";

        // Parent 2
        User::firstOrCreate(
            ['email' => 'parent2@millenniaire.test'],
            [
                'name' => 'Mme BIHINA CHARLOTTE',
                'password' => Hash::make('Parent@12345'),
                'role' => 'parent',
                'is_active' => true,
                'gender' => 'F',
                'date_of_birth' => '1978-08-22',
                'address' => 'Douala - Bonamoussadi',
                'city' => 'Douala',
                'country' => 'Cameroun',
                'phoneNumber' => '+237672222222',
            ]
        );
        echo "✅ Parent 2: parent2@millenniaire.test / Parent@12345\n";

        // ==================== STUDENTS ====================
        echo "\n👨‍🎓 Création des Étudiants...\n";

        $studentsData = [
            [
                'email' => 'student1@millenniaire.test',
                'name' => 'KOUADJE Fabrice',
                'gender' => 'M',
                'dob' => '2010-05-15',
            ],
            [
                'email' => 'student2@millenniaire.test',
                'name' => 'BIHINA Vanessa',
                'gender' => 'F',
                'dob' => '2010-08-22',
            ],
            [
                'email' => 'student3@millenniaire.test',
                'name' => 'FOBA Cedric',
                'gender' => 'M',
                'dob' => '2009-11-30',
            ],
            [
                'email' => 'student4@millenniaire.test',
                'name' => 'NKUEPON Edith',
                'gender' => 'F',
                'dob' => '2010-03-12',
            ],
            [
                'email' => 'student5@millenniaire.test',
                'name' => 'AYUKETAM David',
                'gender' => 'M',
                'dob' => '2010-07-20',
            ],
        ];

        foreach ($studentsData as $index => $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('Student@12345'),
                    'role' => 'student',
                    'is_active' => true,
                    'gender' => $data['gender'],
                    'date_of_birth' => $data['dob'],
                    'address' => 'Douala',
                    'city' => 'Douala',
                    'country' => 'Cameroun',
                ]
            );

            echo "✅ {$data['name']}: {$data['email']} / Student@12345\n";
        }

        echo "\n========== ✅ AUTHENTIFICATION INTÉGRÉE AVEC SUCCÈS ==========\n\n";
        echo "📋 RÉSUMÉ DES COMPTES DE TEST:\n";
        echo "====================================\n";
        echo "🔹 ADMIN:\n";
        echo "   Email: admin@millenniaire.test\n";
        echo "   Mot de passe: Admin@12345\n";
        echo "\n🔹 PROFESSEUR MATHÉMATIQUES:\n";
        echo "   Email: prof.mathematiques@millenniaire.test\n";
        echo "   Mot de passe: Prof@12345\n";
        echo "\n🔹 PROFESSEUR FRANÇAIS:\n";
        echo "   Email: prof.francais@millenniaire.test\n";
        echo "   Mot de passe: Prof@12345\n";
        echo "\n🔹 PROFESSEUR PHYSIQUE:\n";
        echo "   Email: prof.physique@millenniaire.test\n";
        echo "   Mot de passe: Prof@12345\n";
        echo "\n🔹 PARENT 1:\n";
        echo "   Email: parent1@millenniaire.test\n";
        echo "   Mot de passe: Parent@12345\n";
        echo "\n🔹 PARENT 2:\n";
        echo "   Email: parent2@millenniaire.test\n";
        echo "   Mot de passe: Parent@12345\n";
        echo "\n🔹 ÉTUDIANTS:\n";
        echo "   Email: student1@millenniaire.test / Mot de passe: Student@12345\n";
        echo "   Email: student2@millenniaire.test / Mot de passe: Student@12345\n";
        echo "   Email: student3@millenniaire.test / Mot de passe: Student@12345\n";
        echo "   Email: student4@millenniaire.test / Mot de passe: Student@12345\n";
        echo "   Email: student5@millenniaire.test / Mot de passe: Student@12345\n";
        echo "====================================\n\n";
    }
}

