<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandeItem extends Model
{
    protected $fillable = [
        'commande_id',
        'piece_id',
        'vehicule_id', // Nouveau champ
        'quantite',
        'prix_unitaire'
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
    ];

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function piece(): BelongsTo
    {
        return $this->belongsTo(Piece::class);
    }

    public function vehicule(): BelongsTo
    {
        return $this->belongsTo(DemandeEpave::class, 'vehicule_id');
    }

    /**
     * Obtenir le nom de l'item
     */
    public function getNomAttribute(): string
    {
        if ($this->piece_id && $this->piece) {
            return $this->piece->nom;
        }

        if ($this->vehicule_id && $this->vehicule) {
            return $this->vehicule->nom_complet;
        }

        return 'Article supprimé';
    }

    /**
     * Obtenir le sous-total de l'item
     */
    public function getSousTotalAttribute(): float
    {
        return $this->prix_unitaire * $this->quantite;
    }

    /**
     * Vérifier si c'est une pièce
     */
    public function estPiece(): bool
    {
        return !is_null($this->piece_id);
    }

    /**
     * Vérifier si c'est un véhicule
     */
    public function estVehicule(): bool
    {
        return !is_null($this->vehicule_id);
    }

    /**
     * Obtenir le type d'item
     */
    public function getTypeAttribute(): string
    {
        if ($this->estPiece()) {
            return 'piece';
        }

        if ($this->estVehicule()) {
            return 'vehicule';
        }

        return 'inconnu';
    }
}
