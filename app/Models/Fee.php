<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fee extends Model
{
    use HasFactory;

    protected $table = 'fees';
    protected $fillable = [
        'name',
        'description',
        'amount',
        'frequency',
        'due_date',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'float',
        'is_active' => 'boolean',
        'due_date' => 'date',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
