<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanierItem extends Model
{
    protected $fillable = [
        'panier_id',
        'piece_id',
        'vehicule_id', // Nouveau champ
        'quantite',
        'prix_unitaire'
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
    ];

    public function panier(): BelongsTo
    {
        return $this->belongsTo(Panier::class);
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
     * Obtenir le nom de l'item (pièce ou véhicule)
     */
    public function getNomAttribute(): string
    {
        if ($this->piece_id) {
            return $this->piece->nom;
        }

        if ($this->vehicule_id) {
            return $this->vehicule->nom_complet;
        }

        return 'Article inconnu';
    }

    /**
     * Obtenir le prix unitaire de l'item
     */
    public function getPrixAttribute(): float
    {
        if ($this->prix_unitaire) {
            return $this->prix_unitaire;
        }

        if ($this->piece_id && $this->piece) {
            return $this->piece->prix;
        }

        if ($this->vehicule_id && $this->vehicule) {
            return $this->vehicule->prix_souhaite;
        }

        return 0;
    }

    /**
     * Obtenir le sous-total de l'item
     */
    public function getSousTotalAttribute(): float
    {
        return $this->prix * $this->quantite;
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

    /**
     * Obtenir la première photo de l'item
     */
    public function getPhotoAttribute(): ?string
    {
        if ($this->piece_id && $this->piece) {
            return $this->piece->photos[0] ?? null;
        }

        if ($this->vehicule_id && $this->vehicule) {
            return $this->vehicule->premiere_photo;
        }

        return null;
    }

    /**
     * Obtenir le vendeur de l'item
     */
    public function getVendeurAttribute(): ?User
    {
        if ($this->piece_id && $this->piece) {
            return $this->piece->user;
        }

        if ($this->vehicule_id && $this->vehicule) {
            return $this->vehicule->vendeur;
        }

        return null;
    }
}
