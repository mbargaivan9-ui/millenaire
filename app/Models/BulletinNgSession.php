<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BulletinNgSession — Session de saisie de bulletins
 * 
 * Représente une période de saisie pour une trimestre/séquence spécifique.
 * État workflow: brouillon → saisie_ouverte → saisie_fermee → conduite → genere
 * 
 * @property int                  $id
 * @property int                  $config_id
 * @property string               $statut
 * @property bool                 $visibilite_enseignants
 * @property \DateTime|null       $date_publication
 * @property bool                 $notes_verrouillee
 * @property \DateTime|null       $notes_verrouillee_at
 * @property int                  $trimestre_number
 * @property int                  $sequence_number
 * @property string|null          $description
 */
class BulletinNgSession extends Model
{
    protected $table = 'bulletin_ng_sessions';

    protected $fillable = [
        'config_id',
        'statut',
        'visibilite_enseignants',
        'date_publication',
        'notes_verrouillee',
        'notes_verrouillee_at',
        'trimestre_number',
        'sequence_number',
        'description',
    ];

    protected $casts = [
        'visibilite_enseignants' => 'boolean',
        'notes_verrouillee'      => 'boolean',
        'date_publication'       => 'datetime',
        'notes_verrouillee_at'   => 'datetime',
    ];

    // Statuts constants
    const STATUS_DRAFT         = 'brouillon';
    const STATUS_ENTRY_OPEN    = 'saisie_ouverte';
    const STATUS_ENTRY_CLOSED  = 'saisie_fermee';
    const STATUS_CONDUCT       = 'conduite';
    const STATUS_GENERATED     = 'genere';

    /**
     * Relation vers BulletinNgConfig
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(BulletinNgConfig::class, 'config_id');
    }

    /**
     * Relation vers BulletinNgNote
     */
    public function notes(): HasMany
    {
        return $this->hasMany(BulletinNgNote::class, 'session_id');
    }

    /**
     * Vérifier si session est en saisie ouverte
     */
    public function isEntryOpen(): bool
    {
        return $this->statut === self::STATUS_ENTRY_OPEN
            && ! $this->notes_verrouillee
            && $this->visibilite_enseignants;
    }

    /**
     * Vérifier si session est verrouillée
     */
    public function isLocked(): bool
    {
        return $this->notes_verrouillee || $this->statut === self::STATUS_GENERATED;
    }

    /**
     * Publier session aux enseignants
     */
    public function publishToTeachers(): bool
    {
        return $this->update([
            'visibilite_enseignants' => true,
            'date_publication'       => now(),
            'statut'                 => self::STATUS_ENTRY_OPEN,
        ]);
    }

    /**
     * Fermer la saisie
     */
    public function closeSaisie(): bool
    {
        return $this->update([
            'notes_verrouillee'    => true,
            'notes_verrouillee_at' => now(),
            'statut'               => self::STATUS_ENTRY_CLOSED,
        ]);
    }

    /**
     * Marquer comme généré
     */
    public function markAsGenerated(): bool
    {
        return $this->update([
            'statut' => self::STATUS_GENERATED,
        ]);
    }

    /**
     * Obtenir description trimestre/séquence
     */
    public function getSessionLabel(): string
    {
        return "Trimestre {$this->trimestre_number} - Séquence {$this->sequence_number}";
    }
}
