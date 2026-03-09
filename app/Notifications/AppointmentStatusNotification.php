<?php

/**
 * AppointmentStatusNotification — Notification de confirmation/refus RDV
 *
 * Envoyée au parent lorsque l'enseignant confirme ou refuse un rendez-vous.
 *
 * @package App\Notifications
 */

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class AppointmentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Appointment $appointment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isFr    = $notifiable->preferred_language === 'fr';
        $appt    = $this->appointment;
        $teacher = $appt->teacher?->user?->name ?? 'L\'enseignant';
        $student = $appt->student?->user?->name ?? 'votre enfant';
        $date    = $appt->scheduled_at?->locale($isFr ? 'fr' : 'en')->isoFormat('dddd D MMMM YYYY [à] H:mm');

        $isConfirmed = $appt->status === 'confirmed';

        $subject = $isFr
            ? ($isConfirmed ? "✅ Rendez-vous confirmé avec {$teacher}" : "❌ Rendez-vous annulé par {$teacher}")
            : ($isConfirmed ? "✅ Appointment confirmed with {$teacher}" : "❌ Appointment cancelled by {$teacher}");

        $line1 = $isFr
            ? ($isConfirmed ? "Votre rendez-vous pour {$student} le {$date} a été **confirmé** par {$teacher}." : "Votre rendez-vous pour {$student} a été **annulé** par {$teacher}.")
            : ($isConfirmed ? "Your appointment for {$student} on {$date} has been **confirmed** by {$teacher}." : "Your appointment for {$student} has been **cancelled** by {$teacher}.");

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting($isFr ? "Bonjour {$notifiable->first_name}," : "Hello {$notifiable->first_name},")
            ->line($line1);

        if ($appt->teacher_note) {
            $mail->line($isFr ? "**Note de l'enseignant :** {$appt->teacher_note}" : "**Teacher's note:** {$appt->teacher_note}");
        }

        if ($isConfirmed) {
            $mail->action(
                $isFr ? 'Voir mes rendez-vous' : 'View my appointments',
                url('/parent/appointments')
            );
        } else {
            $mail->action(
                $isFr ? 'Prendre un nouveau rendez-vous' : 'Book a new appointment',
                url('/parent/appointments/create')
            );
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $appt    = $this->appointment;
        $isFr    = $notifiable->preferred_language === 'fr';
        $teacher = $appt->teacher?->user?->name ?? '—';
        $isConf  = $appt->status === 'confirmed';

        return [
            'type'    => 'appointment_status',
            'title'   => $isFr ? ($isConf ? "RDV confirmé ✅" : "RDV annulé ❌") : ($isConf ? "Appointment confirmed ✅" : "Appointment cancelled ❌"),
            'message' => $isFr
                ? "Votre rendez-vous avec {$teacher} est " . ($isConf ? "confirmé." : "annulé.")
                : "Your appointment with {$teacher} is " . ($isConf ? "confirmed." : "cancelled."),
            'appointment_id' => $appt->id,
            'status'         => $appt->status,
            'scheduled_at'   => $appt->scheduled_at?->toIso8601String(),
            'teacher_name'   => $teacher,
            'teacher_note'   => $appt->teacher_note,
        ];
    }
}
