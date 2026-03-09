<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $templates = [
            [
                'title'    => 'Déploiement terminé',
                'message'  => 'Millenaire Connect v2.0 a été déployé avec succès sur le serveur.',
                'type'     => Notification::TYPE_SUCCESS,
                'category' => Notification::CAT_SYSTEM,
                'icon'     => 'check-circle',
            ],
            [
                'title'    => 'Nouvelle note disponible',
                'message'  => 'Votre note en Mathématiques est disponible : 16/20.',
                'type'     => Notification::TYPE_INFO,
                'category' => Notification::CAT_GRADE,
                'icon'     => 'book-open',
            ],
            [
                'title'    => 'Paiement reçu',
                'message'  => 'Le paiement de 75 000 XAF pour la scolarité du 2ème trimestre a été confirmé.',
                'type'     => Notification::TYPE_SUCCESS,
                'category' => Notification::CAT_PAYMENT,
                'icon'     => 'credit-card',
            ],
            [
                'title'    => 'Absence enregistrée',
                'message'  => 'Une absence a été enregistrée en SVT le ' . now()->subDays(2)->format('d/m/Y') . '.',
                'type'     => Notification::TYPE_WARNING,
                'category' => Notification::CAT_ABSENCE,
                'icon'     => 'user-x',
            ],
            [
                'title'    => 'Alerte de sécurité',
                'message'  => 'Nouvelle connexion détectée depuis Douala (197.159.x.x). Si ce n\'est pas vous, changez votre mot de passe.',
                'type'     => Notification::TYPE_DANGER,
                'category' => Notification::CAT_SECURITY,
                'icon'     => 'shield-check',
                'action_url' => '/profile/security',
            ],
            [
                'title'    => 'Nouvelle annonce',
                'message'  => 'Réunion parents-professeurs le 15 Mars 2026 à 9h00 en salle polyvalente.',
                'type'     => Notification::TYPE_INFO,
                'category' => Notification::CAT_ANNOUNCE,
                'icon'     => 'megaphone',
            ],
            [
                'title'    => 'Bulletin disponible',
                'message'  => 'Le bulletin du 1er trimestre de l\'année 2025-2026 est maintenant disponible.',
                'type'     => Notification::TYPE_PRIMARY,
                'category' => Notification::CAT_GRADE,
                'icon'     => 'file-text',
            ],
            [
                'title'    => 'Seuil de stockage atteint',
                'message'  => 'L\'espace de stockage des médias a atteint 82% de la limite allouée.',
                'type'     => Notification::TYPE_WARNING,
                'category' => Notification::CAT_SYSTEM,
                'icon'     => 'hard-drive',
            ],
        ];

        foreach ($users as $user) {
            // Give each user 3-6 random notifications
            $count = rand(3, 6);
            $selected = collect($templates)->shuffle()->take($count);

            foreach ($selected as $i => $tpl) {
                Notification::create([
                    'user_id'    => $user->id,
                    'title'      => $tpl['title'],
                    'message'    => $tpl['message'],
                    'type'       => $tpl['type'],
                    'category'   => $tpl['category'],
                    'icon'       => $tpl['icon'],
                    'action_url' => $tpl['action_url'] ?? null,
                    'is_read'    => $i > 1, // first 2 unread
                    'read_at'    => $i > 1 ? now()->subHours(rand(1, 48)) : null,
                    'created_at' => now()->subHours(rand(1, 72)),
                    'updated_at' => now()->subHours(rand(0, 24)),
                ]);
            }
        }

        $this->command->info('Notifications seeded for ' . $users->count() . ' users.');
    }
}
