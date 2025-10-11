<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Panier extends Model
{
    protected $fillable = ['user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PanierItem::class);
    }

    /**
     * Calculer le total du panier (pièces + véhicules)
     */
    public function getTotal()
    {
        return $this->items->sum(function($item) {
            return $item->quantite * $item->prix;
        });
    }

    /**
     * Obtenir les items de type pièce
     */
    public function getPiecesItems()
    {
        return $this->items->filter(fn($item) => $item->estPiece());
    }

    /**
     * Obtenir les items de type véhicule
     */
    public function getVehiculesItems()
    {
        return $this->items->filter(fn($item) => $item->estVehicule());
    }

    /**
     * Obtenir le nombre total d'items
     */
    public function getNombreItems(): int
    {
        return $this->items->count();
    }

    /**
     * Vérifier si le panier contient un véhicule spécifique
     */
    public function contientVehicule(int $vehiculeId): bool
    {
        return $this->items()
            ->where('vehicule_id', $vehiculeId)
            ->exists();
    }

    /**
     * Vérifier si le panier contient une pièce spécifique
     */
    public function contientPiece(int $pieceId): bool
    {
        return $this->items()
            ->where('piece_id', $pieceId)
            ->exists();
    }
}
