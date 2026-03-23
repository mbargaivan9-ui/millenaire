<?php

namespace App\Events\Bulletin;

use App\Models\Classe;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Déclenché quand l'admin publie les bulletins d'une classe/trimestre.
 * → Listener NotifyParentsOfPublishedBulletins
 */
class BulletinsPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Classe $classe,
        public readonly int $term,
        public readonly string $academicYear,
        public readonly Collection $bulletinIds,
    ) {}
}
