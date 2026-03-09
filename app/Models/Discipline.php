<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discipline extends Model
{
    use HasFactory;

    protected $table = 'disciplines';
    protected $fillable = [
        'student_id',
        'recorded_by',
        'type',
        'reason',
        'description',
        'incident_date',
        'start_date',
        'end_date',
        'resolution',
        'status',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
