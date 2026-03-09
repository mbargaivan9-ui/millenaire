<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Console\Command;

class FixMissingTeacherRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:missing-teacher-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the 403 error by creating missing Teacher records for users with teacher role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('================== FIX TEACHER RECORDS ==================');
        $this->newLine();

        // Find users with 'teacher' role who don't have a Teacher record
        $usersWithoutTeacher = User::where('role', 'teacher')
            ->doesntHave('teacher')
            ->get();

        if ($usersWithoutTeacher->isEmpty()) {
            $this->info('✓ Aucun utilisateur teacher sans enregistrement trouvé!');
            return;
        }

        $this->comment("Utilisateurs avec rôle 'teacher' sans enregistrement Teacher: " . $usersWithoutTeacher->count());
        $this->newLine();

        foreach ($usersWithoutTeacher as $user) {
            $this->line("Création d'un enregistrement Teacher pour: {$user->name} ({$user->email})");

            try {
                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'is_active' => true,
                    'is_prof_principal' => false,
                    'years_experience' => 0,
                ]);

                $this->info("✓ Enregistrement créé avec succès (ID: {$teacher->id})");
                $this->newLine();
            } catch (\Exception $e) {
                $this->error("✗ Erreur lors de la création: {$e->getMessage()}");
                $this->newLine();
            }
        }

        $this->line('================== RÉSUMÉ ==================');
        $this->info('Tous les utilisateurs teachers ont maintenant un enregistrement correspondant!');
        $this->info('Essayez de vous reconnecter à l\'interface enseignant.');
    }
}
