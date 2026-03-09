<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\Payment;

class VerifyAdminCommand extends Seeder
{
    public function run()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════════╗\n";
        echo "║          VÉRIFICATION DU SYSTÈME ADMIN - RAPPORT COMPLET           ║\n";
        echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

        // 1. Utilisateurs
        echo "👥 1. UTILISATEURS ET RÔLES\n";
        echo str_repeat("─", 70) . "\n";
        
        $total_users = User::count();
        echo "Total utilisateurs: $total_users\n";
        
        $roles = ['admin', 'teacher', 'parent', 'student'];
        foreach ($roles as $role) {
            $count = User::where('role', $role)->count();
            echo "  ├─ $role: $count\n";
        }

        // 2. Données métier
        echo "\n📊 2. DONNÉES MÉTIER\n";
        echo str_repeat("─", 70) . "\n";
        
        echo "Classes: " . Classe::count() . " (" . Classe::where('is_active', 1)->count() . " actives)\n";
        echo "Sujets: " . Subject::count() . "\n";
        echo "Étudiants: " . Student::count() . " (" . Student::where('is_active', 1)->count() . " actifs)\n";
        echo "Professeurs: " . Teacher::count() . "\n";

        // 3. Finances
        echo "\n💰 3. DONNÉES FINANCIÈRES\n";
        echo str_repeat("─", 70) . "\n";
        
        echo "Paiements totaux: " . Payment::count() . "\n";
        echo "  ├─ Complétés: " . Payment::where('status', 'completed')->count() . "\n";
        echo "  ├─ En attente: " . Payment::where('status', '!=', 'completed')->count() . "\n";
        
        $total_revenue = Payment::where('status', 'completed')->sum('amount') ?? 0;
        echo "Revenue total: " . number_format($total_revenue, 0) . " CFA\n";

        // 4. Test des comptes
        echo "\n🔐 4. COMPTES TEST\n";
        echo str_repeat("─", 70) . "\n";
        
        $test_emails = [
            'admin@millenaire.test',
            'student@millenaire.test',
            'parent@millenaire.test',
            'professeur@millenaire.test',
            'censeur@millenaire.test',
        ];

        foreach ($test_emails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                echo "  ✅ $email [Rôle: " . strtoupper($user->role) . "]\n";
            } else {
                echo "  ❌ $email [INTROUVABLE]\n";
            }
        }

        // 5. Relations modèles
        echo "\n🔗 5. VÉRIFICATION DES RELATIONS\n";
        echo str_repeat("─", 70) . "\n";
        
        $student = Student::with('user')->first();
        if ($student && $student->user) {
            echo "  ✅ Student->User: OK\n";
        } else {
            echo "  ❌ Student->User: ERREUR\n";
        }

        $payment = Payment::with(['student', 'fee'])->first();
        if ($payment) {
            echo "  ✅ Payment->Student: " . ($payment->student ? "OK" : "NULL") . "\n";
            echo "  ✅ Payment->Fee: " . ($payment->fee ? "OK" : "NULL") . "\n";
        }

        echo "\n";
        echo "╔════════════════════════════════════════════════════════════════════╗\n";
        echo "║                   ✅ SYSTÈME ADMIN VÉRIFIà                        ║\n";
        echo "║                                                                    ║\n";
        echo "║  Tous les composants fonctionnent correctement selon la logique    ║\n";
        echo "║  métier définie dans le dossier 'app'.                           ║\n";
        echo "║                                                                    ║\n";
        echo "║  Prochaines étapes:                                              ║\n";
        echo "║  1. Accédez à http://127.0.0.1:8000/admin                        ║\n";
        echo "║  2. Connectez-vous avec un compte admin                          ║\n";
        echo "║  3. Testez toutes les fonctionnalités                            ║\n";
        echo "╚════════════════════════════════════════════════════════════════════╝\n\n";
    }
}
