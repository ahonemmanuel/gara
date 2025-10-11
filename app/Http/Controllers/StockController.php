<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    /**
     * Afficher les stocks pour une casse
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isCasse()) {
            return $this->stocksCasse($user);
        } elseif ($user->isClient()) {
            return $this->stocksClient($user);
        }

        abort(403, 'Accès non autorisé');
    }

    /**
     * Stocks pour les casses (pièces + véhicules)
     */
    private function stocksCasse($user)
    {
        // ============ PIÈCES ============
        $pieces = $user->pieces()->with(['marque', 'modele'])->get();

        $statsPieces = [
            'total' => $pieces->count(),
            'stock_total' => $pieces->sum('quantite'),
            'disponibles' => $pieces->where('disponible', true)->count(),
            'stock_faible' => $pieces->where('quantite', '>', 0)->where('quantite', '<=', 3),
            'stock_vide' => $pieces->where('quantite', 0),
        ];

        // ============ VÉHICULES/ÉPAVES ============
        $vehicules = $user->demandesEpaves()->get();

        $statsVehicules = [
            'total' => $vehicules->count(),
            'disponibles' => $vehicules->where('statut', 'disponible')->count(),
            'reserves' => $vehicules->where('statut', 'reserve')->count(),
            'vendus' => $vehicules->where('statut', 'vendu')->count(),
            'stock_total' => $vehicules->sum('quantite'),
            'valeur_totale' => $vehicules->where('statut', 'disponible')->sum('prix_souhaite'),
        ];

        // Véhicules par type
        $parType = [
            'vehicules' => $vehicules->where('type', 'vehicule')->count(),
            'epaves' => $vehicules->where('type', 'epave')->count(),
        ];

        return view('gestion.stocks-casse', compact(
            'pieces',
            'statsPieces',
            'vehicules',
            'statsVehicules',
            'parType'
        ));
    }

    /**
     * Stocks pour les clients (uniquement leurs annonces de véhicules)
     */
    private function stocksClient($user)
    {
        $vehicules = $user->demandesEpaves()->get();

        $stats = [
            'total' => $vehicules->count(),
            'disponibles' => $vehicules->where('statut', 'disponible')->count(),
            'reserves' => $vehicules->where('statut', 'reserve')->count(),
            'vendus' => $vehicules->where('statut', 'vendu')->count(),
            'valeur_totale' => $vehicules->where('statut', 'disponible')->sum('prix_souhaite'),
        ];

        $parType = [
            'vehicules' => $vehicules->where('type', 'vehicule')->count(),
            'epaves' => $vehicules->where('type', 'epave')->count(),
        ];

        return view('gestion.stocks-client', compact('vehicules', 'stats', 'parType'));
    }

    /**
     * Vue détaillée d'un véhicule dans les stocks
     */
    public function showVehicule($id)
    {
        $vehicule = Auth::user()->demandesEpaves()->findOrFail($id);
        return view('gestion.vehicule-detail', compact('vehicule'));
    }
}
