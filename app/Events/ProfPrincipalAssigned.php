<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfPrincipalAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $teacher,
        public int $classId,
        public ?User $previousTeacher = null
    ) {
    }
}
