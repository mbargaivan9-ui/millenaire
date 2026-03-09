<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizQuestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'options'    => 'array',
        'points'     => 'integer',
        'sort_order' => 'integer',
    ];

    const TYPE_MCQ          = 'multiple_choice';
    const TYPE_TRUE_FALSE   = 'true_false';
    const TYPE_SHORT_ANSWER = 'short_answer';

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
