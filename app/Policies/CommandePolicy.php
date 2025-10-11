<?php

namespace App\Policies;

use App\Models\Commande;
use App\Models\User;

class CommandePolicy
{
    /**
     * Déterminer si l'utilisateur peut voir la commande
     */
    public function view(User $user, Commande $commande): bool
    {
        // Le client propriétaire peut voir sa commande
        if ($user->id === $commande->user_id) {
            return true;
        }

        // Une casse peut voir la commande si elle contient ses articles
        if ($user->isCasse()) {
            return $commande->items->contains(function($item) use ($user) {
                if ($item->estPiece() && $item->piece) {
                    return $item->piece->user_id === $user->id;
                }
                if ($item->estVehicule() && $item->vehicule) {
                    return $item->vehicule->user_id === $user->id;
                }
                return false;
            });
        }

        return false;
    }

    /**
     * Déterminer si l'utilisateur peut modifier la commande
     */
    public function update(User $user, Commande $commande): bool
    {
        // Seul le client propriétaire peut modifier
        return $user->id === $commande->user_id &&
            in_array($commande->statut, ['en_attente']);
    }

    /**
     * Déterminer si l'utilisateur peut annuler la commande
     */
    public function annuler(User $user, Commande $commande): bool
    {
        // Seul le client propriétaire peut annuler
        // Et seulement si la commande est en attente ou confirmée
        return $user->id === $commande->user_id &&
            in_array($commande->statut, ['en_attente', 'confirmee']);
    }

    /**
     * Déterminer si l'utilisateur (casse) peut modifier le statut
     */
    public function updateStatut(User $user, Commande $commande): bool
    {
        // Seule une casse concernée par la commande peut modifier le statut
        if (!$user->isCasse()) {
            return false;
        }

        return $commande->items->contains(function($item) use ($user) {
            if ($item->estPiece() && $item->piece) {
                return $item->piece->user_id === $user->id;
            }
            if ($item->estVehicule() && $item->vehicule) {
                return $item->vehicule->user_id === $user->id;
            }
            return false;
        });
    }

    /**
     * Déterminer si l'utilisateur peut supprimer la commande
     */
    public function delete(User $user, Commande $commande): bool
    {
        // Seuls les admins peuvent supprimer définitivement
        return $user->isAdmin();
    }
}
