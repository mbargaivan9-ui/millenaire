<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BulletinStructureField extends Model
{
    use HasFactory;

    protected $table = 'bulletin_structure_fields';
    
    protected $fillable = [
        'bulletin_dynamic_structure_id',
        'field_name',
        'field_label',
        'field_type',
        'column_index',
        'display_order',
        'calculation_formula',
        'coefficient',
        'min_value',
        'max_value',
        'is_required',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'coefficient' => 'decimal:2',
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
            'is_required' => 'boolean',
            'is_visible' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Structure parente
     */
    public function structure(): BelongsTo
    {
        return $this->belongsTo(DynamicBulletinStructure::class, 'bulletin_dynamic_structure_id');
    }

    /**
     * Scope pour les matières uniquement
     */
    public function scopeSubjectsOnly($query)
    {
        return $query->where('field_type', 'subject');
    }

    /**
     * Scope pour les champs visibles
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope ordonnés par affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id');
    }
}
