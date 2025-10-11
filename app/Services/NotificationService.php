<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\User;
use App\Models\Notification;

class NotificationService
{
    /**
     * Notifier lors de l'annulation d'une commande
     */
    public function commandeAnnulee(User $vendeur, Commande $commande): void
    {
        // Compter les articles du vendeur dans la commande
        $articlesVendeur = $commande->items->filter(function($item) use ($vendeur) {
            if ($item->estPiece() && $item->piece) {
                return $item->piece->user_id === $vendeur->id;
            }
            if ($item->estVehicule() && $item->vehicule) {
                return $item->vehicule->user_id === $vendeur->id;
            }
            return false;
        });

        $pieces = $articlesVendeur->filter(fn($item) => $item->estPiece())->count();
        $vehicules = $articlesVendeur->filter(fn($item) => $item->estVehicule())->count();

        $message = "La commande {$commande->numero_commande} a été annulée par le client. ";

        if ($pieces > 0) {
            $message .= "{$pieces} pièce(s) ";
        }
        if ($vehicules > 0) {
            $message .= "{$vehicules} véhicule(s) ";
        }

        $message .= "ont été remises en stock.";

        Notification::create([
            'user_id' => $vendeur->id,
            'titre' => 'Commande annulée',
            'message' => $message,
            'type' => 'commande',
            'url' => route('gestion.commandes.show', $commande),
            'lu' => false
        ]);
    }

    /**
     * Notifier lors de la création d'une nouvelle commande
     */
    public function nouvelleCommande(User $vendeur, Commande $commande): void
    {
        $articlesVendeur = $commande->items->filter(function($item) use ($vendeur) {
            if ($item->estPiece() && $item->piece) {
                return $item->piece->user_id === $vendeur->id;
            }
            if ($item->estVehicule() && $item->vehicule) {
                return $item->vehicule->user_id === $vendeur->id;
            }
            return false;
        });

        $pieces = $articlesVendeur->filter(fn($item) => $item->estPiece())->count();
        $vehicules = $articlesVendeur->filter(fn($item) => $item->estVehicule())->count();

        $message = "Nouvelle commande {$commande->numero_commande} : ";

        $details = [];
        if ($pieces > 0) {
            $details[] = "{$pieces} pièce(s)";
        }
        if ($vehicules > 0) {
            $details[] = "{$vehicules} véhicule(s)";
        }

        $message .= implode(' et ', $details);

        Notification::create([
            'user_id' => $vendeur->id,
            'titre' => 'Nouvelle commande',
            'message' => $message,
            'type' => 'commande',
            'url' => route('gestion.commandes.show', $commande),
            'lu' => false
        ]);
    }

    /**
     * Notifier le client lors de la création de sa commande
     */
    public function commandeCreee(User $client, Commande $commande): void
    {
        $pieces = $commande->items->filter(fn($item) => $item->estPiece())->count();
        $vehicules = $commande->items->filter(fn($item) => $item->estVehicule())->count();

        $message = "Votre commande {$commande->numero_commande} a été créée avec succès. ";

        $details = [];
        if ($pieces > 0) {
            $details[] = "{$pieces} pièce(s)";
        }
        if ($vehicules > 0) {
            $details[] = "{$vehicules} véhicule(s)";
        }

        $message .= "Contenu : " . implode(' et ', $details);

        Notification::create([
            'user_id' => $client->id,
            'titre' => 'Commande créée',
            'message' => $message,
            'type' => 'commande',
            'url' => route('commandes.show', $commande),
            'lu' => false
        ]);
    }

    /**
     * Notifier lors du changement de statut
     */
    public function statutCommandeChange(User $client, Commande $commande, string $ancienStatut, string $nouveauStatut): void
    {
        $statutLibelle = [
            'en_attente' => 'En attente',
            'confirmee' => 'Confirmée',
            'en_preparation' => 'En préparation',
            'expedie' => 'Expédiée',
            'livree' => 'Livrée',
            'annulee' => 'Annulée'
        ];

        $message = "Votre commande {$commande->numero_commande} est passée de '{$statutLibelle[$ancienStatut]}' à '{$statutLibelle[$nouveauStatut]}'.";

        Notification::create([
            'user_id' => $client->id,
            'titre' => 'Statut de commande mis à jour',
            'message' => $message,
            'type' => 'commande',
            'url' => route('commandes.show', $commande),
            'lu' => false
        ]);
    }
}
