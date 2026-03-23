<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Créer une nouvelle instance de notification.
     *
     * @param User $user L'utilisateur créé
     * @param string $plainPassword Le mot de passe en clair
     */
    public function __construct(
        public readonly User $user,
        public readonly string $plainPassword,
    ) {}

    /**
     * Canaux de notification.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Contenu du mail.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name');
        $loginUrl = route('login');
        
        return (new MailMessage)
            ->subject("Accès à votre compte {$appName} — Données d'accès")
            ->greeting("Bonjour {$this->user->name},")
            ->line("Un compte a été créé pour vous sur la plateforme **{$appName}**.")
            ->line("Voici vos identifiants de connexion:")
            ->line("")
            ->line("📧 **Email:** `{$this->user->email}`")
            ->line("🔐 **Mot de passe:** `{$this->plainPassword}`")
            ->line("")
            ->line("⚠️ **Important:** À votre première connexion, vous serez invité à modifier ce mot de passe pour votre sécurité.")
            ->line("")
            ->action('Se connecter', $loginUrl)
            ->line("")
            ->line("**Conseils de sécurité:**")
            ->line("- Gardez vos identifiants confidentiels")
            ->line("- Changez votre mot de passe dès votre première connexion")
            ->line("- Utilisez un mot de passe fort et unique")
            ->line("")
            ->salutation("Cordialement,\n{$appName} - Administration");
    }

    /**
     * Données pour stockage en base de données.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'user_created',
            'message'  => "Compte créé — Veuillez modifier votre mot de passe",
            'user_id'  => $this->user->id,
            'role'     => $this->user->role,
        ];
    }
}
