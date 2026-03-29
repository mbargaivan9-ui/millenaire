<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BulletinNgNote — Note d'un élève pour une matière
 */
class BulletinNgNote extends Model
{
    protected $table    = 'bulletin_ng_notes';
    protected $fillable = [
        'config_id', 'session_id', 'ng_student_id', 'ng_subject_id',
        'sequence_number', 'note', 'saisie_par', 'saisie_at',
    ];
    protected $casts = [
        'note'      => 'float',
        'saisie_at' => 'datetime',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(BulletinNgConfig::class, 'config_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(BulletinNgSession::class, 'session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(BulletinNgStudent::class, 'ng_student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(BulletinNgSubject::class, 'ng_subject_id');
    }

    public function saiseur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saisie_par');
    }
}
