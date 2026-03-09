<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportCard extends Model
{
    use HasFactory;

    protected $table = 'report_cards';
    protected $fillable = [
        'student_id',
        'class_id',
        'term',
        'sequence',
        'term_average',
        'rank',
        'appreciation',
        'behavior_comment',
        'generated_by',
        'generated_at',
        'is_validated',
        'validated_by',
        'validated_at',
        'pdf_path',
    ];

    protected $casts = [
        'term_average' => 'float',
        'rank' => 'integer',
        'is_validated' => 'boolean',
        'generated_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }
}
