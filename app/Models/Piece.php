<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piece extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicule_id',
        'casse_id',
        'nom',
        'reference',
        'categorie',
        'prix',
        'quantite',
        'etat',
        'description',
        'photos'
    ];

    protected $casts = [
        'photos' => 'array',
        'prix' => 'decimal:2'
    ];

    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class);
    }

    public function casse()
    {
        return $this->belongsTo(User::class, 'casse_id');
    }

    public function commandes()
    {
        return $this->belongsToMany(Commande::class, 'commande_piece')
            ->withPivot('quantite', 'prix_unitaire');
    }
}
