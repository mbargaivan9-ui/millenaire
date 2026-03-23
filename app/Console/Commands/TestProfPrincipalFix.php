<?php

namespace App\Console\Commands;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestProfPrincipalFix extends Command
{
    protected $signature = 'test:prof-principal-fix';
    protected $description = 'Test that prof principal assignment and Bulletin NG visibility works correctly';

    public function handle()
    {
        $this->info("\n╔════════════════════════════════════════════════════════════════╗");
        $this->info("║  TEST PROF PRINCIPAL - BULLETIN NG VISIBILITY FIX             ║");
        $this->info("╚════════════════════════════════════════════════════════════════╝\n");

        try {
            // 1. Get a teacher and a class
            $this->info("STEP 1: Récupération des données...");

            $teacher = Teacher::with('user')
                ->where('is_active', true)
                ->first();

            if (!$teacher) {
                $this->error("❌ Aucun professeur actif trouvé");
                return 1;
            }

            $this->line("✅ Professeur: {$teacher->user->name}");

            $class = DB::table('classes')->where('is_active', true)->first();
            if (!$class) {
                $this->error("❌ Aucune classe trouvée");
                return 1;
            }

            $this->line("✅ Classe: {$class->name}\n");

            // 2. Assign as prof principal
            $this->info("STEP 2: Assignation du professeur comme prof principal...");

            $oldTeacher = Teacher::where('is_prof_principal', true)
                ->where('head_class_id', $class->id)
                ->first();

            if ($oldTeacher) {
                $oldTeacher->update(['is_prof_principal' => false, 'head_class_id' => null]);
                $this->line("• Ancien prof principal retiré");
            }

            $teacher->update(['is_prof_principal' => true, 'head_class_id' => $class->id]);
            DB::table('classes')->where('id', $class->id)->update(['head_teacher_id' => $teacher->id]);

            $this->line("✅ Assignation complétée\n");

            // 3. Check isProfPrincipal 
            $this->info("STEP 3: Vérification de isProfPrincipal()...");

            $user = $teacher->user->fresh();
            $isProfPrincipal = $user->isProfPrincipal();

            $this->line("isProfPrincipal(): " . ($isProfPrincipal ? "✅ TRUE" : "❌ FALSE"));

            // 4. Check database
            $this->info("\nSTEP 4: Vérification des données en BD...");

            $teacherCheck = Teacher::find($teacher->id);
            $this->line("is_prof_principal: " . ($teacherCheck->is_prof_principal ? "✅ TRUE" : "❌ FALSE"));
            $this->line("head_class_id: " . ($teacherCheck->head_class_id ? "✅ {$teacherCheck->head_class_id}" : "❌ NULL"));

            // 5. Check sidebar condition
            $this->info("\nSTEP 5: Vérification pour le sidebar...");

            if ($user->isProfPrincipal()) {
                $this->line("✅ Condition @if(auth()->user()?->isProfPrincipal()): TRUE");
                $this->line("✅ La section 'Professor Principal' s'affichera");
                $this->line("✅ Le lien 'Bulletin NG' s'affichera dans la section");
            } else {
                $this->line("❌ Condition @if(auth()->user()?->isProfPrincipal()): FALSE");
            }

            // Summary
            if ($isProfPrincipal && $teacherCheck->is_prof_principal) {
                $this->info("\n╔═══════════════════════════════════════════════════════════════╗");
                $this->info("║  ✅ TOUS LES TESTS PASSÉS - LA SOLUTION FONCTIONNE            ║");
                $this->info("╚═══════════════════════════════════════════════════════════════╝");
                $this->info("\n📝 RÉSUMÉ:");
                $this->line("  • Professeur {$user->name} est prof principal ✅");
                $this->line("  • isProfPrincipal() retourne TRUE ✅");
                $this->line("  • Bulletin NG sera affiché dans le sidebar ✅");
                $this->line("  • Les données sont correctes en BD ✅");
                return 0;
            } else {
                $this->error("\n❌ PROBLÈME: Les tests n'ont pas passé");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("\n❌ ERREUR: {$e->getMessage()}");
            $this->error("Stack: {$e->getTraceAsString()}");
            return 1;
        }
    }
}
