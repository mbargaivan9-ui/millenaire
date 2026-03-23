<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FixLoginIssues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix login issues by correcting user roles and creating test accounts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line("\n");
        $this->info('══════════════════════════════════════════════════════════════');
        $this->info('           CORRECTION DES PROBLÈMES DE CONNEXION');
        $this->info('══════════════════════════════════════════════════════════════');

        try {
            // Step 1: Update existing users with wrong roles
            $this->info("\n✓ Étape 1: Correction des rôles existants...");
            
            DB::table('users')->where('role', 'professeur')->update(['role' => 'teacher']);
            DB::table('users')->where('role', 'prof_principal')->update(['role' => 'teacher']);
            DB::table('users')->where('role', 'censeur')->update(['role' => 'admin']);
            DB::table('users')->where('role', 'intendant')->update(['role' => 'admin']);
            DB::table('users')->where('role', 'secretaire')->update(['role' => 'admin']);
            DB::table('users')->where('role', 'surveillant')->update(['role' => 'admin']);
            
            $this->line('  ✅ Rôles corrigés');

            // Step 2: Delete test accounts with old roles and recreate them
            $this->info("\n✓ Étape 2: Création des comptes de test...");
            
            $testEmails = [
                'admin@millenaire.test',
                'teacher@millenaire.test',
                'parent@millenaire.test',
                'student@millenaire.test',
            ];
            
            foreach ($testEmails as $email) {
                User::where('email', $email)->delete();
            }

            // Create Admin Account
            $admin = User::create([
                'name' => 'Administrateur',
                'email' => 'admin@millenaire.test',
                'password' => Hash::make('admin@123'),
                'role' => 'admin',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1990-01-15',
            ]);
            $this->line("  ✅ Admin: admin@millenaire.test / admin@123");

            // Create Teacher Account
            $teacher = User::create([
                'name' => 'Professeur Dupont',
                'email' => 'teacher@millenaire.test',
                'password' => Hash::make('teacher@123'),
                'role' => 'teacher',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1985-06-20',
            ]);
            
            Teacher::create([
                'user_id' => $teacher->id,
                'qualification' => 'Mathématiques',
                'is_active' => true,
            ]);
            $this->line("  ✅ Enseignant: teacher@millenaire.test / teacher@123");

            // Create Parent Account
            $parent = User::create([
                'name' => 'Parent Bernard',
                'email' => 'parent@millenaire.test',
                'password' => Hash::make('parent@123'),
                'role' => 'parent',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '1960-05-12',
            ]);
            
            Guardian::create([
                'user_id' => $parent->id,
                'profession' => 'Ingénieur',
            ]);
            $this->line("  ✅ Parent: parent@millenaire.test / parent@123");

            // Create Student Account
            $student = User::create([
                'name' => 'Élève Dupont',
                'email' => 'student@millenaire.test',
                'password' => Hash::make('student@123'),
                'role' => 'student',
                'is_active' => true,
                'gender' => 'M',
                'date_of_birth' => '2008-09-15',
            ]);
            
            Student::create([
                'user_id' => $student->id,
                'matricule' => 'STU-' . str_pad($student->id, 6, '0', STR_PAD_LEFT),
            ]);
            $this->line("  ✅ Élève: student@millenaire.test / student@123");

            // Step 3: Report
            $this->info("\n✓ Étape 3: Rapport de statut...");
            
            $adminCount = User::where('role', 'admin')->count();
            $teacherCount = User::where('role', 'teacher')->count();
            $parentCount = User::where('role', 'parent')->count();
            $studentCount = User::where('role', 'student')->count();
            $totalCount = User::count();

            $this->line("\n  📊 Statut des utilisateurs:");
            $this->line("  ├─ Total: $totalCount");
            $this->line("  ├─ Admins: $adminCount");
            $this->line("  ├─ Enseignants: $teacherCount");
            $this->line("  ├─ Parents: $parentCount");
            $this->line("  └─ Élèves: $studentCount");

            $this->info("\n✓ Étape 4: Vérification de la BD...");
            
            // Verify database connections are working
            \DB::connection()->getPdo();
            $this->line("  ✅ Connexion à la BD: OK");

            $this->info("\n══════════════════════════════════════════════════════════════");
            $this->info("✅ CORRECTION RÉUSSIE!");
            $this->info("══════════════════════════════════════════════════════════════\n");

            return 0;

        } catch (\Exception $e) {
            $this->error("\n❌ ERREUR: " . $e->getMessage());
            return 1;
        }
    }
}
