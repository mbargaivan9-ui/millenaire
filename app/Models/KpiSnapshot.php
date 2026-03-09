<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KpiSnapshot extends Model
{
    use HasFactory;

    protected $table = 'kpi_snapshots';

    protected $fillable = [
        'total_students',
        'total_teachers',
        'total_classes',
        'payment_rate',
        'completed_bulletins',
        'total_bulletins',
        'active_alerts',
        'academic_year',
        'snapshot_date',
    ];

    protected $casts = [
        'total_students' => 'integer',
        'total_teachers' => 'integer',
        'total_classes' => 'integer',
        'payment_rate' => 'float',
        'completed_bulletins' => 'integer',
        'total_bulletins' => 'integer',
        'active_alerts' => 'integer',
        'snapshot_date' => 'date',
    ];

    /**
     * Scope: Snapshots for academic year
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * Scope: Latest snapshot
     */
    public function scopeLatest($query)
    {
        return $query->latest('snapshot_date');
    }

    /**
     * Calculate bulletin completion percentage
     */
    public function getBulletinCompletionPercentage(): float
    {
        if ($this->total_bulletins === 0) {
            return 0;
        }
        return round(($this->completed_bulletins / $this->total_bulletins) * 100, 2);
    }
}
