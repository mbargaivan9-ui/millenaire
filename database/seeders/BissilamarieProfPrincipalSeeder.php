<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Classe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BissilamarieProfPrincipalSeeder extends Seeder
{
    /**
     * Create or update Bissilamarie account as Prof Principal with grid access
     */
    public function run(): void
    {
        echo "\n========== CRÉATION/CORRECTION COMPTE PROF PRINCIPAL BISSILAMARIE ==========\n\n";

        // Create or update the prof principal account
        $user = User::updateOrCreate(
            ['email' => 'bissilamarie@gmail.com'],
            [
                'name' => 'Bissila Marie',
                'password' => Hash::make('Prof@12345'),
                'role' => 'prof_principal',  // ✅ CRITICAL: Must be prof_principal for grid access
                'is_active' => true,
                'gender' => 'F',
                'date_of_birth' => '1985-06-15',
                'address' => 'Douala - Centre',
                'city' => 'Douala',
                'country' => 'Cameroun',
                'phoneNumber' => '+237671234567',
            ]
        );

        // Get the first available class for assignment
        $classe = Classe::first();
        if (!$classe) {
            echo "⚠️  Aucune classe trouvée. Vérifiez que ClassSeeder a été exécuté en premier.\n";
            return;
        }

        // Create or update teacher relation with prof_principal configuration
        $teacher = Teacher::updateOrCreate(
            ['user_id' => $user->id],
            [
                'qualification' => 'Baccalauréat + Diplôme d\'Enseignement',
                'years_experience' => 8,
                'is_active' => true,
                'is_prof_principal' => true,  // ✅ CRITICAL: Mark as prof principal
                'head_class_id' => $classe->id,  // ✅ CRITICAL: Assign head class
                'matricule' => 'PROF-PRINCIPAL-' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
            ]
        );
        echo "✅ Relation Teacher créée avec is_prof_principal = true et head_class_id = {$classe->id}\n";

        // Assign prof principal to the class
        $classe->update(['prof_principal_id' => $teacher->id]);
        echo "✅ Classe '{$classe->name}' assignée à Bissila Marie comme Professeur Principal\n";

        echo "\n========== ✅ COMPTE PROF PRINCIPAL CONFIGURÉ ==========\n\n";
        echo "📋 DÉTAILS DU COMPTE:\n";
        echo "====================================\n";
        echo "✅ Email: bissilamarie@gmail.com\n";
        echo "✅ Mot de passe: Prof@12345\n";
        echo "✅ Rôle User: prof_principal\n";
        echo "✅ Teacher.is_prof_principal: " . ($teacher->is_prof_principal ? 'OUI' : 'NON') . "\n";
        echo "✅ Teacher.head_class_id: {$teacher->head_class_id} ({$classe->name})\n";
        echo "✅ Actif: Oui\n";
        echo "====================================\n\n";

        echo "🎯 ACCÈS DISPONIBLE:\n";
        echo "====================================\n";
        echo "✅ Sidebar: Bulletins Classe (Section Or) - VISIBLE\n";
        echo "✅ Grille Template (Vert) - ACCESSIBLE\n";
        echo "✅ Route: /teacher/bulletin/template-grid\n";
        echo "✅ Classe assignée: {$classe->name}\n";
        echo "====================================\n\n";

        echo "🔍 VÉRIFICATIONS:\n";
        echo "1. isProfPrincipal() = " . ($user->isProfPrincipal() ? 'true ✅' : 'false ❌') . "\n";
        echo "2. Rôle dans BD = '{$user->role}' ✅\n";
        echo "3. teacher.is_prof_principal = " . ($teacher->is_prof_principal ? 'true ✅' : 'false ❌') . "\n";
        echo "4. teacher.head_class_id = {$teacher->head_class_id} ✅\n";
        echo "5. Classe.prof_principal_id = {$classe->prof_principal_id} vs Teacher.id = {$teacher->id} " . ($classe->prof_principal_id === $teacher->id ? '✅' : '❌') . "\n";
        echo "\n";
    }
}
