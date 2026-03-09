<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * BulletinSubmittedNotification
 * 
 * Notifiée au Prof Principal quand un bulletin est soumis par un enseignant
 */
class BulletinSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $bulletinId,
        public readonly string $studentName,
        public readonly string $className,
        public readonly int $term,
        public readonly ?int $sequence = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Bulletin soumis pour validation — {$this->studentName}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Un bulletin scolaire a été soumis pour validation:")
            ->line("**Élève**: {$this->studentName}")
            ->line("**Classe**: {$this->className}")
            ->line("**Trimestre**: T{$this->term} / S{$this->sequence}")
            ->action('Valider le bulletin', route('admin.bulletins.validate'))
            ->line('Merci de vérifier et valider ce bulletin.')
            ->salutation('L\'administration');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'bulletin_submitted',
            'message'      => "Bulletin soumis — {$this->studentName}",
            'bulletin_id'  => $this->bulletinId,
            'student_name' => $this->studentName,
            'class_name'   => $this->className,
            'term'         => $this->term,
            'sequence'     => $this->sequence,
            'action_url'   => route('admin.bulletins.validate'),
        ];
    }
}
