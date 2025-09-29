<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- Corrige ici


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    public function getTotalPanier()
    {
        return $this->panierItems()->with('piece')->get()->sum(function ($item) {
            return $item->quantite * $item->piece->prix;
        });
    }



    public function getNombrePiecesPanier()
    {
        return $this->panierItems()->sum('quantite');
    }

    // Dans app/Models/User.php, ajoutez ces méthodes :

    public function paniers()
    {
        return $this->hasMany(Panier::class, 'client_id');
    }

    public function favoris()
    {
        return $this->hasMany(Favoris::class, 'user_id');
    }


    public function venteEpaves()
    {
        return $this->hasMany(VenteEpave::class, 'client_id');
    }


    public function recherchesSauvegardees()
    {
        return $this->hasMany(RechercheSauvegardee::class, 'client_id');
    }





    public function panierItems()
    {
        return $this->hasManyThrough(
            PanierItem::class,
            Panier::class,
            'user_id',   // clé étrangère sur panier
            'panier_id',   // clé étrangère sur panier_items
            'id',          // clé primaire user
            'id'           // clé primaire panier
        );
    }




    public function panier()
    {
        return $this->hasOne(Panier::class, 'user_id');
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




// app/Models/User.php (ajouter ces relations)
    public function vehicules()
    {
        return $this->hasMany(Vehicle::class, 'casse_id');
    }

    public function pieces()
    {
        return $this->hasMany(Piece::class, 'vehicle_id');
    }



    // ... existing code ...

    // Nouvelles relations
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'casse_id');
    }


    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Méthodes utilitaires
    public function isCasse(): bool
    {
        return $this->role === UserRole::CASSE;
    }

    public function isClient(): bool
    {
        return $this->role === UserRole::CLIENT;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    // Créer le panier automatiquement pour les clients
    protected static function booted()
    {
        static::created(function ($user) {
            if ($user->isClient()) {
                $user->panier()->create();
            }
        });
    }


    // app/Models/User.php

    public function demandesEpaves()
    {
        return $this->hasMany(DemandeEpave::class, 'user_id'); // ou 'client_id' selon ta colonne
    }


    public function demandes_epaves()
    {
        return $this->hasMany(DemandeEpave::class);
    }


}
