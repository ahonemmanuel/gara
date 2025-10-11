<?php

namespace App\Http\Controllers;

use App\Models\DemandeEpave;
use App\Models\PanierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanierVehiculeController extends Controller
{
    /**
     * Ajouter un véhicule au panier
     */
    public function add(Request $request, DemandeEpave $demandeEpave)
    {
        // Vérifier que le véhicule est disponible
        if (!$demandeEpave->estDisponible()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce véhicule n\'est plus disponible.'
                ]);
            }
            return back()->with('error', 'Ce véhicule n\'est plus disponible.');
        }

        // Vérifier que l'utilisateur n'est pas le propriétaire
        if ($demandeEpave->user_id === Auth::id()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas ajouter votre propre véhicule au panier.'
                ]);
            }
            return back()->with('error', 'Vous ne pouvez pas ajouter votre propre véhicule au panier.');
        }

        // Récupérer ou créer le panier
        $panier = Auth::user()->panier;
        if (!$panier) {
            $panier = Auth::user()->panier()->create();
        }

        // Vérifier si le véhicule est déjà dans le panier
        $existingItem = $panier->items()->where('vehicule_id', $demandeEpave->id)->first();

        if ($existingItem) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce véhicule est déjà dans votre panier.'
                ]);
            }
            return back()->with('info', 'Ce véhicule est déjà dans votre panier.');
        }

        // Vérifier la quantité disponible
        if ($demandeEpave->quantite <= 0) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock épuisé pour ce véhicule.'
                ]);
            }
            return back()->with('error', 'Stock épuisé pour ce véhicule.');
        }

        // Ajouter le véhicule au panier (quantité = 1 par défaut)
        $panier->items()->create([
            'vehicule_id' => $demandeEpave->id,
            'quantite' => 1,
            'prix_unitaire' => $demandeEpave->prix_souhaite
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Véhicule ajouté au panier avec succès.',
                'panier_count' => $panier->items()->count()
            ]);
        }

        return back()->with('success', 'Véhicule ajouté au panier avec succès.');
    }

    /**
     * Retirer un véhicule du panier
     */
    public function remove(PanierItem $item)
    {
        // Vérifier que l'item appartient bien au panier de l'utilisateur
        if ($item->panier->user_id !== Auth::id()) {
            abort(403, 'Action non autorisée.');
        }

        // Vérifier que c'est bien un véhicule
        if (!$item->estVehicule()) {
            return back()->with('error', 'Cet article n\'est pas un véhicule.');
        }

        $item->delete();

        return back()->with('success', 'Véhicule retiré du panier.');
    }

    /**
     * Mettre à jour la quantité d'un véhicule dans le panier
     * Note: Pour les véhicules, on permet de modifier la quantité si disponible
     */
    public function updateQuantite(Request $request, PanierItem $item)
    {
        // Vérifier que l'item appartient bien au panier de l'utilisateur
        if ($item->panier->user_id !== Auth::id()) {
            abort(403, 'Action non autorisée.');
        }

        // Vérifier que c'est bien un véhicule
        if (!$item->estVehicule()) {
            return back()->with('error', 'Cet article n\'est pas un véhicule.');
        }

        $request->validate([
            'quantite' => 'required|integer|min:1'
        ]);

        $vehicule = $item->vehicule;

        // Vérifier la disponibilité
        if (!$vehicule || !$vehicule->estDisponible()) {
            return back()->with('error', 'Ce véhicule n\'est plus disponible.');
        }

        // Vérifier le stock
        if ($request->quantite > $vehicule->quantite) {
            return back()->with('error', 'Quantité demandée supérieure au stock disponible.');
        }

        $item->update(['quantite' => $request->quantite]);

        return back()->with('success', 'Quantité mise à jour.');
    }
}
