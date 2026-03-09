<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAvailability extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active'   => 'boolean',
        'day_of_week' => 'integer', // 0=Sunday, 1=Monday, ..., 6=Saturday
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, int $day)
    {
        return $query->where('day_of_week', $day);
    }

    public static function dayName(int $day, string $locale = 'fr'): string
    {
        $fr = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        $en = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        return ($locale === 'fr' ? $fr : $en)[$day] ?? '?';
    }
}
