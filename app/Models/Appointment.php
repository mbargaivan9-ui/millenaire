<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_DONE      = 'done';

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
                     ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
                     ->orderBy('scheduled_at');
    }
}
