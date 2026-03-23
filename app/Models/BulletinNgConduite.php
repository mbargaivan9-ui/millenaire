<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BulletinNgConduite — Conduite & comportement d'un élève
 */
class BulletinNgConduite extends Model
{
    protected $table    = 'bulletin_ng_conduites';
    protected $fillable = [
        'config_id', 'ng_student_id',
        'tableau_honneur', 'encouragement', 'felicitations', 'blame_travail', 'avert_travail',
        'absences_totales', 'absences_nj', 'exclusion', 'avert_conduite', 'blame_conduite',
    ];
    protected $casts = [
        'tableau_honneur'  => 'boolean',
        'encouragement'    => 'boolean',
        'felicitations'    => 'boolean',
        'blame_travail'    => 'boolean',
        'exclusion'        => 'boolean',
        'absences_totales' => 'integer',
        'absences_nj'      => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(BulletinNgStudent::class, 'ng_student_id');
    }

    public function config(): BelongsTo
    {
        return $this->belongsTo(BulletinNgConfig::class, 'config_id');
    }
}
