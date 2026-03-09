<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BulletinStructureRevision extends Model
{
    use HasFactory;

    protected $table = 'bulletin_structure_revisions';
    
    protected $fillable = [
        'bulletin_dynamic_structure_id',
        'old_structure',
        'new_structure',
        'change_description',
        'modified_by',
        'modified_at',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'old_structure' => 'array',
            'new_structure' => 'array',
            'modified_at' => 'datetime',
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
     * Utilisateur qui a effectué la modification
     */
    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
