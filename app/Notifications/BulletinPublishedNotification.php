<?php

namespace App\Notifications\Bulletin;

use App\Models\Guardian;
use App\Models\ParentBulletinAccess;
use App\Models\SmartBulletin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * BulletinPublishedNotification
 *
 * Envoyée aux parents quand les bulletins sont publiés.
 * Channels : mail + database
 */
class BulletinPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly SmartBulletin $bulletin,
        private readonly Guardian $guardian,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $student  = $this->bulletin->student;
        $classe   = $this->bulletin->classe;
        $term     = $this->bulletin->term;
        $year     = $this->bulletin->academic_year;

        // Récupérer ou créer le token d'accès parent
        $access = ParentBulletinAccess::firstOrCreate(
            ['guardian_id' => $this->guardian->id, 'student_id' => $student->id],
            ['created_by' => $this->bulletin->published_by ?? 1]
        );

        $accessUrl = route('parent.bulletins.token', $access->access_token);

        return (new MailMessage)
            ->subject("📋 Bulletin Scolaire disponible – {$student->first_name} {$student->last_name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le bulletin du **Trimestre {$term}** ({$year}) de **{$student->first_name} {$student->last_name}** est maintenant disponible.")
            ->when($this->bulletin->student_average, fn($m) =>
                $m->line("**Moyenne générale : {$this->bulletin->student_average}/20** – {$this->bulletin->appreciation}")
            )
            ->action('Consulter le bulletin', $accessUrl)
            ->line("Ce lien est personnel et sécurisé. Merci de ne pas le partager.")
            ->salutation("L'équipe pédagogique de " . (config('app.school_name', 'l\'établissement')));
    }

    public function toDatabase($notifiable): array
    {
        $student = $this->bulletin->student;
        return [
            'type'         => 'bulletin_published',
            'bulletin_id'  => $this->bulletin->id,
            'student_name' => "{$student->first_name} {$student->last_name}",
            'term'         => $this->bulletin->term,
            'average'      => $this->bulletin->student_average,
            'appreciation' => $this->bulletin->appreciation,
            'message'      => "Le bulletin du Trimestre {$this->bulletin->term} de {$student->first_name} est disponible.",
        ];
    }
}
