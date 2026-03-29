<?php

namespace App\Events;

use App\Models\BulletinNgNote;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * BulletinNoteWasSaved — Event déclenché quand une note est sauvée
 * 
 * Cet event est émis chaque fois qu'une note est créée ou modifiée.
 * Les listeners peuvent réagir en recalculant les moyennes, classements, etc.
 */
class BulletinNoteWasSaved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BulletinNgNote $note,
        public User $savedBy
    ) {
    }
}
