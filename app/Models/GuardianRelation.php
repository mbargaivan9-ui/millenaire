<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alias pour Guardian — pivot student_guardian
 */
class GuardianRelation extends Model
{
    protected $table = 'student_guardian';

    protected $fillable = ['student_id', 'guardian_id', 'relationship', 'is_primary'];

    protected $casts = ['is_primary' => 'boolean'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }
}
