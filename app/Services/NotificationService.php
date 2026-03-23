<?php

/**
 * NotificationService
 *
 * Service centralisé pour toutes les notifications de la plateforme.
 * Supporte: Push (Laravel Reverb), Email, SMS (futur)
 *
 * Phase 8 — Notifications Temps Réel
 *
 * @package App\Services
 */

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\EstablishmentSetting;
use App\Notifications\AbsenceRecordedNotification;
use App\Notifications\PaymentConfirmedNotification;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Notifier un parent qu'une absence a été enregistrée pour son enfant.
     */
    public function sendAbsenceNotification(Student $student, array $absenceData): void
    {
        $settings = EstablishmentSetting::getInstance();

        if (!($settings->notify_absence_parent ?? true)) {
            return;
        }

        // Trouver les tuteurs/parents de l'élève
        $guardians = $student->guardians ?? collect();

        foreach ($guardians as $guardian) {
            $user = $guardian->user ?? null;
            if (!$user) continue;

            try {
                $user->notify(new AbsenceRecordedNotification($student, $absenceData));

                // Broadcast temps réel via Reverb
                broadcast(new \App\Events\StudentAbsenceRecorded([
                    'student_id'   => $student->id,
                    'student_name' => $student->user?->name,
                    'date'         => $absenceData['date'] ?? now()->format('Y-m-d'),
                    'subject'      => $absenceData['subject_name'] ?? null,
                    'justified'    => $absenceData['justified'] ?? false,
                ]))->toPrivate("guardian.{$user->id}");

            } catch (\Throwable $e) {
                \Log::error("[NotificationService] Absence notification failed for user {$user->id}: " . $e->getMessage());
            }
        }
    }


    /**
     * Notifier la confirmation d'un paiement Mobile Money.
     */
    public function sendPaymentConfirmation(User $payer, array $paymentData): void
    {
        $settings = EstablishmentSetting::getInstance();

        if (!($settings->notify_payment_success ?? true)) {
            return;
        }

        try {
            $payer->notify(new PaymentConfirmedNotification($paymentData));
        } catch (\Throwable $e) {
            \Log::error("[NotificationService] Payment notification failed for user {$payer->id}: " . $e->getMessage());
        }
    }

    /**
     * Notifier les enseignants en retard de saisie (relance Prof Principal).
     */
    public function sendGradeEntryRelance(\App\Models\Classe $class, int $term, int $sequence): int
    {
        $gradeService = app(GradeCalculationService::class);
        $completion   = $gradeService->getCompletionBySubject($class, $term, $sequence);

        $notified = 0;

        foreach ($completion as $subjectId => $data) {
            if ($data['pct'] >= 100) continue; // Déjà complet

            // Trouver l'enseignant de cette matière dans cette classe
            $assignment = \App\Models\ClassSubjectTeacher::where([
                'class_id'   => $class->id,
                'subject_id' => $subjectId,
            ])->with('teacher.user')->first();

            if (!$assignment?->teacher?->user) continue;

            try {
                $assignment->teacher->user->notify(
                    new \App\Notifications\GradeEntryRelanceNotification(
                        $class, $data['name'], $data['pct'], $term, $sequence
                    )
                );
                $notified++;
            } catch (\Throwable $e) {
                \Log::warning("[NotificationService] Relance failed for teacher: " . $e->getMessage());
            }
        }

        return $notified;
    }
}
