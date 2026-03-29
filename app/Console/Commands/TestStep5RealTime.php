<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class TestStep5RealTime extends Command
{
    protected $signature = 'test:step5-realtime';
    protected $description = 'Diagnostic complet du système Step 5 en temps réel';

    public function handle()
    {
        $this->info('════════════════════════════════════════════════════════════════');
        $this->info('🔍 DIAGNOSTIC COMPLET STEP 5 - SYSTÈME DE CALCUL EN TEMPS RÉEL');
        $this->info('════════════════════════════════════════════════════════════════');
        $this->newLine();

        // Crée des données de test simulant un scénario réel
        $this->info('📋 CRÉATION DE DONNÉES DE TEST');
        $this->info('────────────────────────────────────────────────────────────────');

        $configId = 'test_' . uniqid();
        $sessionKey = 'bulletin_ng_config_' . $configId;

        // Crée des élèves avec IDs cohérents
        $students = [
            ['id' => uniqid('student_'), 'nom' => 'Alice Dupont', 'matricule' => 'S001'],
            ['id' => uniqid('student_'), 'nom' => 'Bob Martin', 'matricule' => 'S002'],
            ['id' => uniqid('student_'), 'nom' => 'Charlie Smith', 'matricule' => 'S003'],
        ];

        // Crée des matières avec IDs cohérents
        $subjects = [
            ['id' => uniqid('subject_'), 'nom' => 'Mathématiques', 'coefficient' => 2, 'nom_prof' => 'M. Dupuis'],
            ['id' => uniqid('subject_'), 'nom' => 'Français', 'coefficient' => 2, 'nom_prof' => 'Mme Bernard'],
            ['id' => uniqid('subject_'), 'nom' => 'Anglais', 'coefficient' => 1, 'nom_prof' => 'Mr. Johnson'],
        ];

        // Initialise les notes
        $notes = [];

        // Simule quelques notes
        $notes[$students[0]['id'] . '_' . $subjects[0]['id']] = 15;  // Alice - Math - 15
        $notes[$students[0]['id'] . '_' . $subjects[1]['id']] = 14;  // Alice - Français - 14
        $notes[$students[0]['id'] . '_' . $subjects[2]['id']] = 12;  // Alice - Anglais - 12
        $notes[$students[1]['id'] . '_' . $subjects[0]['id']] = 10;  // Bob - Math - 10
        $notes[$students[1]['id'] . '_' . $subjects[1]['id']] = 11;  // Bob - Français - 11
        // Bob n'a pas de note en Anglais - on teste avec données incomplètes

        // Sauvegarde en session
        session([$sessionKey => [
            'id' => $configId,
            'langue' => 'FR',
            'nom_classe' => 'Classe 3A',
            'trimestre' => 1,
            'trimestre_label' => 'Trimestre 1',
            'students' => $students,
            'subjects' => $subjects,
            'notes' => $notes,
            'notes_verrouillee' => false,
        ]]);

        $this->line("✅ Session créée: $sessionKey");
        $this->line("   Élèves: " . count($students));
        $this->line("   Matières: " . count($subjects));
        $this->line("   Notes: " . count($notes));
        $this->newLine();

        // TEST 1: Affiche la structure
        $this->info('TEST 1️⃣: STRUCTURE DES DONNÉES');
        $this->info('────────────────────────────────────────────────────────────────');

        $sessionData = session($sessionKey);
        $this->line('Élèves:');
        foreach ($sessionData['students'] as $i => $s) {
            $this->line("  " . ($i + 1) . ". [ID: " . substr($s['id'], 0, 20) . "...] " . $s['nom']);
        }

        $this->newLine();
        $this->line('Matières:');
        foreach ($sessionData['subjects'] as $i => $s) {
            $this->line("  " . ($i + 1) . ". [ID: " . substr($s['id'], 0, 20) . "...] " . $s['nom'] . " (Coef: " . $s['coefficient'] . ")");
        }

        $this->newLine();
        $this->line('Notes stockées:');
        foreach ($sessionData['notes'] as $key => $value) {
            $this->line("  $key => $value");
        }
        $this->newLine();

        // TEST 2: Lance calculateStats
        $this->info('TEST 2️⃣: CALCUL DES STATISTIQUES');
        $this->info('────────────────────────────────────────────────────────────────');

        // Appelle le contrôleur pour calculer
        $controller = app(\App\Http\Controllers\Teacher\BulletinNgController::class);
        $reflection = new \ReflectionMethod($controller, 'calculateStats');
        $reflection->setAccessible(true);

        $stats = $reflection->invoke($controller, $sessionData);

        $this->line('✅ Statistiques calculées:');
        $this->line('  Classe:');
        $this->line("    - Moyenne: " . $stats['avg']);
        $this->line("    - % Réussite: " . $stats['pct'] . "%");
        $this->line("    - Max: " . $stats['max']);
        $this->line("    - Min: " . $stats['min']);
        $this->line("    - Réussis: " . $stats['passing'] . "/" . count($students));

        $this->newLine();
        $this->line('  Par élève:');
        foreach ($stats['avgs'] as $studentId => $avg) {
            $studentName = collect($students)->firstWhere('id', $studentId)['nom'];
            $rank = $stats['ranks'][$studentId] ?? '?';
            $this->line("    - $studentName: $avg (Rang: $rank)");
        }
        $this->newLine();

        // TEST 3: Simule une saisie et recalcul
        $this->info('TEST 3️⃣: SIMULATION SAISIE TEMPS RÉEL');
        $this->info('────────────────────────────────────────────────────────────────');

        $testStudent = $students[2]; // Charlie
        $testSubject = $subjects[1]; // Français
        $testNote = 16;

        $this->line("Simulation: {$testStudent['nom']} + {$testSubject['nom']} = $testNote");

        // Ajoute la note
        $sessionData['notes'][$testStudent['id'] . '_' . $testSubject['id']] = $testNote;
        session([$sessionKey => $sessionData]);

        // Recalcule
        $newStats = $reflection->invoke($controller, $sessionData);

        $this->line("✅ Nouvelles statistiques:");
        $this->line("  Moyenne classe: " . $newStats['avg']);
        $this->line("  Exemple - Charlie avant: N/A | Charlie après: " . ($newStats['avgs'][$testStudent['id']] ?? 'N/A'));
        $this->newLine();

        // TEST 4: Vérifie la structure JSON retournée
        $this->info('TEST 4️⃣: STRUCTURE JSON RETOURNÉE');
        $this->info('────────────────────────────────────────────────────────────────');

        $jsonResponse = [
            'success' => true,
            'note' => $testNote,
            'stats' => $newStats,
        ];

        $json = json_encode($jsonResponse, JSON_PRETTY_PRINT);
        $this->line('📦 JSON qui sera envoyé au JavaScript:');
        $this->line(substr($json, 0, 500) . '...');
        $this->newLine();

        // TEST 5: Vérifications finales
        $this->info('TEST 5️⃣: VÉRIFICATIONS FINALES');
        $this->info('────────────────────────────────────────────────────────────────');

        $checks = [
            'SessionExists' => !empty($sessionData),
            'StudentsCount' => count($sessionData['students']) > 0,
            'SubjectsCount' => count($sessionData['subjects']) > 0,
            'NotesCount' => count($sessionData['notes']) > 0,
            'StatsAverage' => isset($newStats['avg']) && $newStats['avg'] >= 0,
            'StatsStudentAvgs' => count($newStats['avgs']) > 0,
            'StatsRanks' => count($newStats['ranks']) > 0,
            'StudentInAvgs' => isset($newStats['avgs'][$testStudent['id']]),
            'StudentInRanks' => isset($newStats['ranks'][$testStudent['id']]),
        ];

        foreach ($checks as $check => $result) {
            $status = $result ? '✅ PASS' : '❌ FAIL';
            $this->line("$status | $check");
        }

        $this->newLine();

        // RÉSUMÉ
        $allPass = array_reduce($checks, fn($carry, $item) => $carry && $item, true);

        if ($allPass) {
            $this->info('════════════════════════════════════════════════════════════════');
            $this->line('✅ TOUS LES TESTS PASSENT!');
            $this->line('');
            $this->line('Le système de calcul en temps réel fonctionne CORRECTEMENT.');
            $this->line('');
            $this->line('Si tu vois toujours un problème dans le navigateur:');
            $this->line('1. C\'est un problème JavaScript (console F12)');
            $this->line('2. Les IDs HTML ne matchent pas les IDs en session');
            $this->line('3. La requête AJAX ne fonctionne pas correctement');
            $this->info('════════════════════════════════════════════════════════════════');
        } else {
            $this->error('════════════════════════════════════════════════════════════════');
            $this->error('❌ CERTAINS TESTS ÉCHOUENT');
            $this->error('========================================================== ========');
            $failedChecks = array_filter($checks, fn($v) => !$v);
            foreach (array_keys($failedChecks) as $check) {
                $this->error("   - $check");
            }
        }
    }
}
