<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classe;
use App\Models\Guardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Sequence;

class AuthTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n========== CRÉATION DES UTILISATEURS DE TEST ==========\n\n";

        // ==================== ADMIN ====================
        echo "👨‍💼 Création Admin...\n";
        $admin = User::firstOrCreate(
            ['email' => 'admin@millenniaire.test'],
            [
                'name' => 'Administrateur Principal',
                'password' => Hash::make('Admin@12345'),
                'role' => 'admin',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1990-05-15',
                'address' => 'Douala',
                'city' => 'Douala',
                'country' => 'Cameroun',
                'phoneNumber' => '+237671234567',
            ]
        );
        echo "✅ Admin: admin@millenniaire.test / Admin@12345\n";

        // ==================== PROFESSEURS ====================
        echo "\n👨‍🏫 Création Professeurs...\n";
        
        $teacher1 = User::firstOrCreate(
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

        Teacher::firstOrCreate(
            ['user_id' => $teacher1->id],
            [
                'matricule' => 'PROF-001',
                'qualification' => 'Master en Mathématiques',
                'years_experience' => 5,
                'is_prof_principal' => false,
                'is_active' => true,
            ]
        );
        echo "✅ Professeur Maths: prof.mathematiques@millenniaire.test / Prof@12345\n";

        $teacher2 = User::firstOrCreate(
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

        Teacher::firstOrCreate(
            ['user_id' => $teacher2->id],
            [
                'matricule' => 'PROF-002',
                'qualification' => 'Licence en Français',
                'years_experience' => 8,
                'is_prof_principal' => true,
                'is_active' => true,
            ]
        );
        echo "✅ Prof Principal Français: prof.francais@millenniaire.test / Prof@12345\n";

        $teacher3 = User::firstOrCreate(
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

        Teacher::firstOrCreate(
            ['user_id' => $teacher3->id],
            [
                'matricule' => 'PROF-003',
                'qualification' => 'Master en Physique',
                'years_experience' => 3,
                'is_prof_principal' => false,
                'is_active' => true,
            ]
        );
        echo "✅ Professeur Physique: prof.physique@millenniaire.test / Prof@12345\n";

        // ==================== PARENTS ====================
        echo "\n👨‍👩‍👧 Création Parents...\n";

        $parent1 = User::firstOrCreate(
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

        $parent2 = User::firstOrCreate(
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

        // ==================== ÉTUDIANTS ====================
        echo "\n👨‍🎓 Création Étudiants...\n";

        // Create or get 6e A class
        $class6e = Classe::firstOrCreate(
            ['name' => '6e A'],
            [
                'level' => '6e',
                'section' => 'A',
                'capacity' => 40,
                'prof_principal_id' => $teacher2->id,
                'is_active' => true,
            ]
        );

        $students_data = [
            [
                'email' => 'student1@millenniaire.test',
                'name' => 'KOUADJE Fabrice',
                'gender' => 'M',
                'dob' => '2010-05-15',
                'matricule' => 'STU-000001',
            ],
            [
                'email' => 'student2@millenniaire.test',
                'name' => 'BIHINA Vanessa',
                'gender' => 'F',
                'dob' => '2010-08-22',
                'matricule' => 'STU-000002',
            ],
            [
                'email' => 'student3@millenniaire.test',
                'name' => 'FOBA Cedric',
                'gender' => 'M',
                'dob' => '2009-11-30',
                'matricule' => 'STU-000003',
            ],
            [
                'email' => 'student4@millenniaire.test',
                'name' => 'NKUEPON Edith',
                'gender' => 'F',
                'dob' => '2010-03-12',
                'matricule' => 'STU-000004',
            ],
            [
                'email' => 'student5@millenniaire.test',
                'name' => 'AYUKETAM David',
                'gender' => 'M',
                'dob' => '2010-07-20',
                'matricule' => 'STU-000005',
            ],
        ];

        foreach ($students_data as $data) {
            $student_user = User::firstOrCreate(
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

            Student::firstOrCreate(
                ['user_id' => $student_user->id],
                [
                    'matricule' => $data['matricule'],
                    'classe_id' => $class6e->id,
                    'enrollment_date' => now()->subMonths(6),
                    'financial_status' => 'current',
                    'is_active' => true,
                ]
            );

            echo "✅ {$data['name']}: {$data['email']} / Student@12345\n";
        }

        // Link students to parents
        $student1 = User::where('email', 'student1@millenniaire.test')->first();
        if ($student1 && $student1->student) {
            Guardian::firstOrCreate(
                ['user_id' => $parent1->id],
                [
                    'profession' => 'Ingénieur',
                    'is_active' => true,
                ]
            );
            $student1->student->guardians()->syncWithoutDetaching([$parent1->id]);
        }

        $student2 = User::where('email', 'student2@millenniaire.test')->first();
        if ($student2 && $student2->student) {
            $student2->student->guardians()->syncWithoutDetaching([$parent2->id]);
        }

        echo "\n========== ✅ CRÉATION TERMINÉE ==========\n\n";
    }
}

