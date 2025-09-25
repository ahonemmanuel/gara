<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    use HasFactory;

    protected $fillable = [
        'casse_id',
        'marque',
        'modele',
        'annee',
        'immatriculation',
        'type_vehicule',
        'date_arrivee',
        'etat',
        'photos',
        'description'
    ];

    protected $casts = [
        'photos' => 'array',
        'date_arrivee' => 'date'
    ];

    public function casse()
    {
        return $this->belongsTo(User::class, 'casse_id');
    }

    public function pieces()
    {
        return $this->hasMany(Piece::class);
    }
}
