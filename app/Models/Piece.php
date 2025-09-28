<?php


// app/Models/Piece.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Piece extends Model
{
    protected $fillable = [
        'vehicle_id',
        'nom',
        'description',
        'prix',
        'quantite',
        'etat',
        'photos',
        'reference_constructeur',
        'compatible_avec',
        'disponible'
    ];

    protected $casts = [
        'photos' => 'array',
        'compatible_avec' => 'array',
        'disponible' => 'boolean'
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function panierItems(): HasMany
    {
        return $this->hasMany(PanierItem::class);
    }

    public function commandeItems(): HasMany
    {
        return $this->hasMany(CommandeItem::class);
    }

    // User.php
    public function pieces()
    {
        return $this->hasManyThrough(
            Piece::class,     // Le modèle final
            Vehicle::class,   // Le modèle intermédiaire
            'casse_id',       // Clé étrangère sur Vehicle qui pointe sur User (casse_id)
            'vehicle_id',     // Clé étrangère sur Piece qui pointe sur Vehicle
            'id',             // Clé locale du User
            'id'              // Clé locale du Vehicle
        );
    }

}
