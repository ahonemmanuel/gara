<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'casse_id',
        'numero_commande',
        'statut',
        'total',
        'adresse_livraison',
        'methode_paiement',
        'date_commande'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'date_commande' => 'datetime'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function casse()
    {
        return $this->belongsTo(User::class, 'casse_id');
    }

    public function pieces()
    {
        return $this->belongsToMany(Piece::class, 'commande_piece')
            ->withPivot('quantite', 'prix_unitaire');
    }

    public function index()
    {
        $commandesEnAttente = Commande::where('status', 'pending')->count();

        return view('casse.*', compact('commandesEnAttente'));
    }


    public function lignes()
    {
        return $this->hasMany(LigneCommande::class, 'commande_id');
    }
}
