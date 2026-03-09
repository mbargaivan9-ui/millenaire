<?php

namespace App\Notifications;

use App\Models\Bulletin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BulletinPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Bulletin $bulletin,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->bulletin->student;
        return (new MailMessage)
            ->subject("Bulletin disponible — {$student->user->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le bulletin scolaire de **{$student->user->name}** pour le Trimestre {$this->bulletin->term} (Séquence {$this->bulletin->sequence}) est maintenant disponible.")
            ->line("Moyenne générale: **{$this->bulletin->moyenne}/20** — Rang: **{$this->bulletin->rang}**")
            ->action('Consulter le bulletin', route('parent.bulletin.show', $this->bulletin->id))
            ->line('Vous pouvez également télécharger le bulletin en PDF depuis votre espace parent.')
            ->salutation('L\'administration de ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        $student = $this->bulletin->student;
        return [
            'type'        => 'bulletin_published',
            'message'     => "Bulletin publié — {$student->user->name} — Moy. {$this->bulletin->moyenne}/20",
            'bulletin_id' => $this->bulletin->id,
            'student_id'  => $this->bulletin->student_id,
            'moyenne'     => $this->bulletin->moyenne,
            'rang'        => $this->bulletin->rang,
            'action_url'  => route('parent.bulletin.show', $this->bulletin->id),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
