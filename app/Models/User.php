<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    // Dans app/Models/User.php, ajoutez ces méthodes :

    public function paniers()
    {
        return $this->hasMany(Panier::class, 'client_id');
    }

    public function favoris()
    {
        return $this->hasMany(Favoris::class, 'client_id');
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'client_id');
    }

    public function venteEpaves()
    {
        return $this->hasMany(VenteEpave::class, 'client_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function recherchesSauvegardees()
    {
        return $this->hasMany(RechercheSauvegardee::class, 'client_id');
    }

    public function getNombrePiecesPanier()
    {
        return $this->paniers()->sum('quantite');
    }

    public function getTotalPanier()
    {
        return $this->paniers()->with('piece')->get()->sum(function ($panier) {
            return $panier->quantite * $panier->piece->prix;
        });
    }

    public function getUnreadNotificationsCount()
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function getDistanceFrom($latitude, $longitude)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        $earthRadius = 6371; // km

        $latFrom = deg2rad($latitude);
        $lonFrom = deg2rad($longitude);
        $latTo = deg2rad($this->latitude);
        $lonTo = deg2rad($this->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    // Méthodes helper pour vérifier les rôles
    public function isClient(): bool
    {
        return $this->role === UserRole::CLIENT;
    }

    public function isCasse(): bool
    {
        return $this->role === UserRole::CASSE;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }


// app/Models/User.php (ajouter ces relations)
    public function vehicules()
    {
        return $this->hasMany(Vehicule::class, 'casse_id');
    }

    public function pieces()
    {
        return $this->hasMany(Piece::class, 'casse_id');
    }

}
