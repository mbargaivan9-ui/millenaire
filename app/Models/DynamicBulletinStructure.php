<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DynamicBulletinStructure extends Model
{
    use HasFactory;

    protected $table = 'bulletin_dynamic_structures';
    
    protected $fillable = [
        'classe_id',
        'source_file_path',
        'source_type',
        'structure',
        'metadata',
        'formula_config',
        'column_mapping',
        'status',
        'validation_notes',
        'created_by',
        'validated_by',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'structure' => 'array',
            'metadata' => 'array',
            'formula_config' => 'array',
            'column_mapping' => 'array',
            'validated_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // RELATIONS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Classe concernée par cette structure
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    /**
     * Utilisateur qui a créé cette structure
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur qui a validé cette structure
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Champs définissant la structure (matières, coefficients, etc)
     */
    public function fields(): HasMany
    {
        return $this->hasMany(BulletinStructureField::class);
    }

    /**
     * Historique des modifications
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(BulletinStructureRevision::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════════

    /**
     * Structures actives uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Structures en brouillon
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Structures validées
     */
    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    /**
     * Pour une classe donnée
     */
    public function scopeForClass($query, $classeId)
    {
        return $query->where('classe_id', $classeId);
    }

    // ═══════════════════════════════════════════════════════════════
    // MÉTHODES PUBLIQUES
    // ═══════════════════════════════════════════════════════════════

    /**
     * Extraire la liste des matières de la structure
     */
    public function getSubjects(): array
    {
        return $this->fields()
            ->where('field_type', 'subject')
            ->orderBy('display_order')
            ->pluck('field_label', 'field_name')
            ->toArray();
    }

    /**
     * Extraire tous les coefficients pour les matières
     */
    public function getCoefficients(): array
    {
        return $this->fields()
            ->whereIn('field_type', ['subject', 'coefficient'])
            ->select('field_name', 'coefficient')
            ->get()
            ->pluck('coefficient', 'field_name')
            ->toArray();
    }

    /**
     * Obtenir une formule de calcul pour un champ
     */
    public function getFormulaForField(string $fieldName): ?string
    {
        return $this->fields()
            ->where('field_name', $fieldName)
            ->first()
            ?->calculation_formula;
    }

    /**
     * Valider et activer cette structure
     */
    public function validate(User $validator, ?string $notes = null): bool
    {
        $this->status = 'validated';
        $this->validated_by = $validator->id;
        $this->validated_at = now();
        $this->validation_notes = $notes;

        return $this->save();
    }

    /**
     * Activer cette structure (la rendre opérationnelle)
     */
    public function activate(): bool
    {
        // Désactiver les autres structures actives pour cette classe
        self::where('classe_id', $this->classe_id)
            ->where('id', '!=', $this->id)
            ->where('status', 'active')
            ->update(['status' => 'archived']);

        // Activer cette structure
        $this->status = 'active';
        return $this->save();
    }

    /**
     * Archiver cette structure
     */
    public function archive(): bool
    {
        $this->status = 'archived';
        return $this->save();
    }

    /**
     * Enregistrer une révision (historique)
     */
    public function recordRevision(User $user, ?string $description = null, ?array $oldStructure = null): BulletinStructureRevision
    {
        return $this->revisions()->create([
            'old_structure' => $oldStructure,
            'new_structure' => $this->structure,
            'change_description' => $description,
            'modified_by' => $user->id,
        ]);
    }

    /**
     * Obtenir la structure complète avec tous les détails
     */
    public function getFullStructure(): array
    {
        return [
            'id' => $this->id,
            'classe_id' => $this->classe_id,
            'classe_name' => $this->classe->name,
            'status' => $this->status,
            'source_type' => $this->source_type,
            'created_at' => $this->created_at,
            'validated_at' => $this->validated_at,
            'structure' => $this->structure,
            'fields' => $this->fields()->orderBy('display_order')->get(),
            'metadata' => $this->metadata,
            'formula_config' => $this->formula_config,
            'column_mapping' => $this->column_mapping,
        ];
    }

    /**
     * Appliquer cette structure à un bulletin (générer les calculs)
     */
    public function applyToBulletinEntry(BulletinEntry $entry): void
    {
        // Récupérer tous les champs avec formule
        $fieldsWithFormulas = $this->fields()
            ->whereNotNull('calculation_formula')
            ->get();

        foreach ($fieldsWithFormulas as $field) {
            // Évaluer la formule et mettre à jour l'entrée
            $this->calculateFieldValue($entry, $field);
        }

        $entry->save();
    }

    /**
     * Calculer la valeur d'un champ selon sa formule
     */
    private function calculateFieldValue(BulletinEntry $entry, BulletinStructureField $field): void
    {
        if (!$field->calculation_formula) {
            return;
        }

        try {
            // Pour implémentation future : utiliser Math.js ou expression evaluator
            // Pour maintenant : supporter les formules simples
            $formula = $field->calculation_formula;
            
            // Exemple: "(n1+n2+n3)/3" → remplacer n1, n2, n3 par valeurs réelles
            // Cette logique sera améliorée dans la phase de calculs dynamiques
            
        } catch (\Exception $e) {
            \Log::warning("Formula error for field {$field->field_name}: {$e->getMessage()}");
        }
    }
}
