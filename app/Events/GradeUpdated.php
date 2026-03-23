<?php
// ═══════════════════════════════════════════════════════════
// app/Events/Bulletin/GradeUpdated.php
// Déclenché à chaque sauvegarde d'une note → recalcul auto
// ═══════════════════════════════════════════════════════════

namespace App\Events\Bulletin;

use App\Models\SmartBulletin;
use App\Models\SmartBulletinGrade;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GradeUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly SmartBulletinGrade $grade,
        public readonly SmartBulletin $bulletin,
    ) {}
}
