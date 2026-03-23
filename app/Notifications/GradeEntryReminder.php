<?php

namespace App\Notifications;

use App\Models\Classe;
use App\Models\Subject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradeEntryReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Classe  $class,
        public readonly Subject $subject,
        public readonly int     $term,
        public readonly int     $sequence,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Rappel: Saisie des notes — {$this->class->name} — {$this->subject->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Rappel: vous n'avez pas encore saisi les notes de **{$this->subject->name}** pour la classe **{$this->class->name}** (Trimestre {$this->term}, Séquence {$this->sequence}).")
            ->action('Saisir les notes maintenant', route('teacher.marks.index'))
            ->line('Merci de le faire dès que possible.')
            ->salutation('L\'équipe pédagogique de ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'grade_reminder',
            'message'    => "Rappel: Saisie des notes — {$this->subject->name} — {$this->class->name} (T{$this->term} S{$this->sequence})",
            'class_id'   => $this->class->id,
            'subject_id' => $this->subject->id,
            'term'       => $this->term,
            'sequence'   => $this->sequence,
            'action_url' => route('teacher.marks.index'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
