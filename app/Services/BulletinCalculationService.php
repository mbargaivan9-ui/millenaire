<?php

namespace App\Services;

use App\Models\BulletinNgConfig;
use App\Models\BulletinNgNote;
use App\Models\BulletinNgSession;
use App\Models\BulletinNgStudent;
use App\Models\BulletinNgTrimestre;
use Illuminate\Support\Collection;

/**
 * BulletinCalculationService — Calcul des moyennes et classements
 * 
 * Service responsable de tous les calculs du système de bulletin:
 * - Moyennes de séquences
 * - Moyennes de trimestres  
 * - Notes finales (Trimestre 3)
 * - Classements par classe et trimestre
 */
class BulletinCalculationService
{
    /**
     * Calculer la moyenne d'une séquence pour un étudiant
     * 
     * @param int $studentId
     * @param int $sequenceNumber (1-6)
     * @param int $configId
     * @return float
     */
    public function calculateSequenceAverage(
        int $studentId,
        int $sequenceNumber,
        int $configId
    ): float {
        // ✅ FIX: Add session_id filter to prevent data mixing when multiple sessions exist
        // Get current session for this sequence
        $session = BulletinNgSession::where('config_id', $configId)
            ->where('sequence_number', $sequenceNumber)
            ->latest()
            ->first();
        
        // Récupérer tous les notes de cette séquence pour cet étudiant
        $query = BulletinNgNote::where('ng_student_id', $studentId)
            ->where('config_id', $configId)
            ->where('sequence_number', $sequenceNumber)
            ->with('subject');
        
        // ✅ FIX: Filter by session if it exists
        if ($session) {
            $query = $query->where('session_id', $session->id);
        }
        
        $notes = $query->get();

        if ($notes->isEmpty()) {
            return 0;
        }

        $totalPts = 0;
        $totalCoef = 0;

        foreach ($notes as $note) {
            if ($note->note !== null && $note->subject) {
                $totalPts += $note->note * $note->subject->coefficient;
                $totalCoef += $note->subject->coefficient;
            }
        }

        return $totalCoef > 0 ? round($totalPts / $totalCoef, 2) : 0;
    }

    /**
     * Calculer la moyenne d'un trimestre (1 ou 2)
     * 
     * Trimestre 1 = (Seq1 + Seq2) / 2
     * Trimestre 2 = (Seq3 + Seq4) / 2
     * 
     * @param int $studentId
     * @param int $trimesterNumber (1-2)
     * @param int $configId
     * @return float
     */
    public function calculateTrimesterAverage(
        int $studentId,
        int $trimesterNumber,
        int $configId
    ): float {
        if ($trimesterNumber < 1 || $trimesterNumber > 3) {
            throw new \InvalidArgumentException("Trimestre doit être entre 1 et 3");
        }

        if ($trimesterNumber === 3) {
            // Pour trimestre 3, utiliser calculateFinalGrade()
            return $this->calculateFinalGrade($studentId, $configId);
        }

        // Déterminer les séquences pour ce trimestre
        $seqStart = ($trimesterNumber - 1) * 2 + 1;
        $seqEnd = $seqStart + 1;

        // Calculer moyennes des séquences
        $seq1Avg = $this->calculateSequenceAverage($studentId, $seqStart, $configId);
        $seq2Avg = $this->calculateSequenceAverage($studentId, $seqEnd, $configId);

        // Moyenne du trimestre
        $trimesterAvg = ($seq1Avg + $seq2Avg) / 2;

        return round($trimesterAvg, 2);
    }

    /**
     * Calculer la note finale (Trimestre 3)
     * 
     * Trimestre 3 = (Trim1*0.3 + Trim2*0.3 + Seq5*0.2 + Seq6*0.2)
     * C'est la note définitive de l'année scolaire
     * 
     * @param int $studentId
     * @param int $configId
     * @return float
     */
    public function calculateFinalGrade(
        int $studentId,
        int $configId
    ): float {
        // Récupérer moyennes trimestres 1 & 2
        $trim1Avg = $this->calculateTrimesterAverage($studentId, 1, $configId);
        $trim2Avg = $this->calculateTrimesterAverage($studentId, 2, $configId);

        // Récupérer moyennes séquences 5 & 6
        $seq5Avg = $this->calculateSequenceAverage($studentId, 5, $configId);
        $seq6Avg = $this->calculateSequenceAverage($studentId, 6, $configId);

        // Calcul composé
        $finalGrade = (
            $trim1Avg * 0.3 +
            $trim2Avg * 0.3 +
            $seq5Avg * 0.2 +
            $seq6Avg * 0.2
        );

        return round($finalGrade, 2);
    }

    /**
     * Calculer le classement des élèves pour un trimestre
     * 
     * Retourne une collection avec les rangs
     * Format: [ng_student_id => rang]
     * 
     * @param int $configId
     * @param int $trimesterNumber (1-3)
     * @return Collection
     */
    public function calculateClassRanking(
        int $configId,
        int $trimesterNumber
    ): Collection {
        $config = BulletinNgConfig::find($configId);
        if (! $config) {
            return collect();
        }

        // Récupérer tous les étudiants actifs
        $students = BulletinNgStudent::where('config_id', $configId)
            ->where('is_active', true)
            ->get();

        // Calculer moyennes
        $avgs = [];
        foreach ($students as $student) {
            $avg = $this->calculateTrimesterAverage($student->id, $trimesterNumber, $configId);
            $avgs[$student->id] = $avg;
        }

        // Trier par moyenne décroissante
        arsort($avgs);

        // Créer les rangs
        $rankings = collect();
        $rank = 1;
        foreach ($avgs as $studentId => $avg) {
            $rankings[$studentId] = $rank;
            $rank++;
        }

        return $rankings;
    }

    /**
     * Mettre à jour les records de trimestres en BD
     * 
     * Appelle calculateTrimesterAverage() et stocker dans bulletin_ng_trimestres
     * Calcule aussi le rang
     * 
     * @param int $studentId
     * @param int $configId
     * @param int $trimesterNumber (1-3)
     * @return bool
     */
    public function updateTrimesterRecord(
        int $studentId,
        int $configId,
        int $trimesterNumber
    ): bool {
        $moyenne = $this->calculateTrimesterAverage($studentId, $trimesterNumber, $configId);

        // Calculer classement
        $rankings = $this->calculateClassRanking($configId, $trimesterNumber);
        $rang = $rankings[$studentId] ?? null;

        // Compter effectif
        $effectif = BulletinNgStudent::where('config_id', $configId)
            ->where('is_active', true)
            ->count();

        // Upsert dans bulletin_ng_trimestres
        BulletinNgTrimestre::updateOrCreate(
            [
                'config_id' => $configId,
                'ng_student_id' => $studentId,
                'trimestre_number' => $trimesterNumber,
            ],
            [
                'moyenne' => $moyenne,
                'rang_classe' => $rang,
                'effectif_total' => $effectif,
            ]
        );

        return true;
    }

    /**
     * Recalculer tous les trimestres pour une config (batch)
     * 
     * Utile après import ou modification massive
     * 
     * @param int $configId
     * @return int Nombre de records updated
     */
    public function recalculateAllTrimestres(int $configId): int
    {
        $students = BulletinNgStudent::where('config_id', $configId)
            ->where('is_active', true)
            ->pluck('id');

        $count = 0;
        foreach ($students as $studentId) {
            // Trimestres 1, 2, 3
            for ($tri = 1; $tri <= 3; $tri++) {
                $this->updateTrimesterRecord($studentId, $configId, $tri);
                $count++;
            }
        }

        return $count;
    }
}
