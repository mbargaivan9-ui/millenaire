<?php

namespace App\Listeners;

use App\Events\ProfPrincipalAssigned;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class InvalidateProfPrincipalSessionCache
{
    public function handle(ProfPrincipalAssigned $event): void
    {
        // Invalider le cache utilisateur du professeur assigné
        Cache::forget("user.{$event->teacher->id}");
        Cache::forget("teacher.{$event->teacher->id}");
        
        // Invalider la session de l'utilisateur s'il est actuellement en ligne
        // Cela force le rechargement de ses données lors du prochain accès
        if ($event->teacher->is_online) {
            // Supprimer les données en cache qui pourraient être liées à ce professeur
            Cache::tags(['user:' . $event->teacher->id])->flush();
        }

        // Si un professeur précédent a perdu le statut
        if ($event->previousTeacher) {
            Cache::forget("user.{$event->previousTeacher->id}");
            Cache::forget("teacher.{$event->previousTeacher->id}");
            Cache::tags(['user:' . $event->previousTeacher->id])->flush();
        }
    }
}
