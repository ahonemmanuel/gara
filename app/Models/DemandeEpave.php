<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeEpave extends Model
{
    protected $table = 'demandes_epaves';

    protected $fillable = [
        'user_id',
        'type',
        'marque',
        'modele',
        'annee',
        'numero_chassis',
        'numero_plaque',
        'couleur',
        'carburant',
        'kilometrage',
        'etat',
        'prix_souhaite',
        'description',
        'photos',
        'telephone_contact',
        'adresse',
        'statut',
        'quantite',
    ];

    protected $casts = [
        'photos' => 'array',
        'prix_souhaite' => 'decimal:2',
    ];

    protected $attributes = [
        'quantite' => 1,
        'statut' => 'disponible',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relation vers le vendeur (alias pour clarté)
    public function vendeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getPhotosUrlsAttribute()
    {
        if (!$this->photos) return [];

        return collect($this->photos)->map(function($photo) {
            return asset('storage/' . $photo);
        })->toArray();
    }

    public function getStatutBadgeClassAttribute()
    {
        return match($this->statut) {
            'disponible' => 'bg-success',
            'vendu' => 'bg-danger',
            'reserve' => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    public function getTypeBadgeClassAttribute()
    {
        return match($this->type) {
            'vehicule' => 'bg-primary',
            'epave' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getTypeLibelleAttribute()
    {
        return match($this->type) {
            'vehicule' => 'Véhicule',
            'epave' => 'Épave',
            default => 'Non défini'
        };
    }

    // Vérifier si le véhicule est disponible (basé uniquement sur le statut)
    public function estDisponible(): bool
    {
        return $this->statut === 'disponible' && $this->quantite > 0;
    }

    // Vérifier si l'utilisateur peut ajouter au panier
    public function peutEtreAjouteAuPanier(int $userId): bool
    {
        return $this->estDisponible() && $this->user_id !== $userId;
    }

    // Obtenir le nom complet du véhicule
    public function getNomCompletAttribute(): string
    {
        return "{$this->marque} {$this->modele} ({$this->annee})";
    }

    // Obtenir la première photo
    public function getPremierePhotoAttribute(): ?string
    {
        if (!$this->photos || empty($this->photos)) {
            return null;
        }
        return asset('storage/' . $this->photos[0]);
    }
}
