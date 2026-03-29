<?php

namespace App\Listeners;

use App\Events\BulletinNoteWasSaved;
use App\Services\BulletinCalculationService;
use Illuminate\Support\Facades\Log;

/**
 * RecalculateBulletinStats — Listener pour BulletinNoteWasSaved
 * 
 * Déclenché chaque fois qu'une note est sauvée.
 * Recalcule automatiquement:
 * 1. Moyenne de l'étudiant pour cette séquence
 * 2. Moyenne du trimestre concerné
 * 3. Classement de l'étudiant
 * 4. Met à jour bulletin_ng_trimestres
 */
class RecalculateBulletinStats
{
    public function __construct(private BulletinCalculationService $calculationService)
    {
    }

    public function handle(BulletinNoteWasSaved $event): void
    {
        $note = $event->note;

        try {
            // Déterminer le trimestre à partir du numéro de séquence
            $trimesterNumber = match (true) {
                $note->sequence_number <= 2 => 1,
                $note->sequence_number <= 4 => 2,
                default => 3,
            };

            // Recalculer et sauvegarder les stats du trimestre
            $this->calculationService->updateTrimesterRecord(
                $note->ng_student_id,
                $note->config_id,
                $trimesterNumber
            );

            Log::info('Bulletin stats recalculated', [
                'note_id' => $note->id,
                'student_id' => $note->ng_student_id,
                'trimester' => $trimesterNumber,
                'sequence' => $note->sequence_number,
            ]);
        } catch (\Exception $e) {
            Log::error('Error recalculating bulletin stats', [
                'note_id' => $note->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
