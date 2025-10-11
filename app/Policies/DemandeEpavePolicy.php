<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DemandeEpave;

class DemandeEpavePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DemandeEpave $demande): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Tout le monde peut créer une annonce
        return true;
    }

    public function update(User $user, DemandeEpave $demande): bool
    {
        return $user->id === $demande->user_id;
    }

    public function delete(User $user, DemandeEpave $demande): bool
    {
        return $user->id === $demande->user_id;
    }

    public function ajouterAuPanier(User $user, DemandeEpave $demande): bool
    {
        // L'utilisateur ne peut pas ajouter sa propre annonce au panier
        return $demande->estDisponible() && $user->id !== $demande->user_id;
    }

    public function toggleDisponibilite(User $user, DemandeEpave $demande): bool
    {
        return $user->id === $demande->user_id;
    }
}
