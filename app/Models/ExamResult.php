<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    protected $table = 'exam_results';
    
    protected $fillable = [
        'establishment_setting_id',
        'exam_name',
        'subject',
        'success_percentage',
        'academic_year',
        'order',
    ];

    protected $casts = [
        'success_percentage' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the establishment setting
     */
    public function establishmentSetting(): BelongsTo
    {
        return $this->belongsTo(EstablishmentSetting::class);
    }
}
