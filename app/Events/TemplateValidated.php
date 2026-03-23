<?php

namespace App\Events\Bulletin;

use App\Models\SmartBulletinTemplate;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Déclenché quand le prof principal valide le template.
 * → Listener CreateStudentBulletins
 * → Listener NotifyTeachersGradeEntryOpen
 */
class TemplateValidated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly SmartBulletinTemplate $template,
    ) {}
}
