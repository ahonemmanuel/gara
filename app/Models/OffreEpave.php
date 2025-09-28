<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffreEpave extends Model
{
    protected $table = 'offres_epaves'; // <-- IMPORTANT

    protected $fillable = [
        'demande_epave_id',
        'casse_id',
        'prix_offert',
        'message',
        'statut'
    ];

    public function demandeEpave(): BelongsTo
    {
        return $this->belongsTo(DemandeEpave::class);
    }

    public function casse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'casse_id');
    }

    public function getStatutBadgeClassAttribute()
    {
        return match($this->statut) {
            'en_attente' => 'bg-warning',
            'accepte' => 'bg-success',
            'refuse' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getFormattedPrixAttribute()
    {
        return number_format($this->prix_offert, 0, ',', ' ') . '€';
    }
}
