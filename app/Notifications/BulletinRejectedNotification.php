<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * BulletinRejectedNotification
 * 
 * Notifiée à l'enseignant quand un bulletin est rejeté par l'admin
 */
class BulletinRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $bulletinId,
        public readonly string $studentName,
        public readonly string $className,
        public readonly int $term,
        public readonly ?int $sequence = null,
        public readonly ?string $rejectionReason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Bulletin rejeté — {$this->studentName}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le bulletin scolaire suivant a été rejeté et doit être corrigé:")
            ->line("**Élève**: {$this->studentName}")
            ->line("**Classe**: {$this->className}")
            ->line("**Trimestre**: T{$this->term} / S{$this->sequence}");

        if ($this->rejectionReason) {
            $message->line("**Raison du rejet**:")
                ->line($this->rejectionReason);
        }

        $message->action('Corriger le bulletin', route('teacher.bulletin.index'))
            ->line('Veuillez corriger les erreurs identifiées et soumettre le bulletin à nouveau.')
            ->salutation('L\'administration');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'bulletin_rejected',
            'message'         => "Bulletin rejeté — {$this->studentName}",
            'bulletin_id'     => $this->bulletinId,
            'student_name'    => $this->studentName,
            'class_name'      => $this->className,
            'term'            => $this->term,
            'sequence'        => $this->sequence,
            'rejection_reason' => $this->rejectionReason,
            'action_url'      => route('teacher.bulletin.index'),
        ];
    }
}
