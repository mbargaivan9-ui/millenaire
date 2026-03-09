<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AppointmentRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isFr   = $notifiable->preferred_language !== 'en';
        $parent = $this->appointment->parent;
        $student= $this->appointment->student?->user;
        $date   = $this->appointment->scheduled_at?->format('d/m/Y à H:i');

        return (new MailMessage)
            ->subject($isFr ? '📅 Nouvelle demande de rendez-vous' : '📅 New appointment request')
            ->greeting($isFr ? "Bonjour {$notifiable->name}," : "Hello {$notifiable->name},")
            ->line($isFr
                ? "Le parent de {$student?->name} souhaite vous rencontrer."
                : "The parent of {$student?->name} would like to meet with you.")
            ->line(($isFr ? 'Date souhaitée: ' : 'Requested date: ') . $date)
            ->when($this->appointment->notes, fn($m) => $m->line(($isFr ? 'Objet: ' : 'Regarding: ') . $this->appointment->notes))
            ->action($isFr ? 'Voir la demande' : 'View request', route('teacher.appointments.index'))
            ->salutation($isFr ? 'Cordialement,' : 'Best regards,');
    }

    public function toArray(object $notifiable): array
    {
        $isFr   = $notifiable->preferred_language !== 'en';
        $student= $this->appointment->student?->user;

        return [
            'title'   => $isFr ? 'Nouvelle demande de RDV' : 'New appointment request',
            'message' => $isFr
                ? "Le parent de {$student?->name} souhaite vous rencontrer."
                : "Parent of {$student?->name} requests a meeting.",
            'icon'       => 'calendar',
            'url'        => route('teacher.appointments.index'),
            'appointment_id' => $this->appointment->id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
