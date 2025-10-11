<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Piece;
use App\Models\Commande;
use App\Models\DemandeEpave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isCasse()) {
            return $this->casseDashboard();
        } elseif ($user->isClient()) {
            return $this->clientDashboard();
        } elseif ($user->isAdmin()) {
            return $this->adminDashboard();
        }
    }

    private function casseDashboard()
    {
        $user = Auth::user();

        // 📊 Stats séparées pour la casse
        $stats = [
            // Pièces détachées
            'pieces_total' => Piece::where('user_id', $user->id)->count(),
            'pieces_disponibles' => Piece::where('user_id', $user->id)
                ->where('disponible', true)
                ->where('quantite', '>', 0)
                ->count(),
            'pieces_stock_faible' => Piece::where('user_id', $user->id)
                ->whereBetween('quantite', [1, 3])
                ->count(),
            'pieces_rupture' => Piece::where('user_id', $user->id)
                ->where('quantite', 0)
                ->count(),

            // Véhicules (type = vehicule)
            'vehicules_total' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'vehicule')
                ->count(),
            'vehicules_disponibles' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'vehicule')
                ->where('statut', 'disponible')
                ->count(),
            'vehicules_vendus' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'vehicule')
                ->where('statut', 'vendu')
                ->count(),

            // Épaves (type = epave)
            'epaves_total' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'epave')
                ->count(),
            'epaves_disponibles' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'epave')
                ->where('statut', 'disponible')
                ->count(),
            'epaves_vendues' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'epave')
                ->where('statut', 'vendu')
                ->count(),

            // Commandes
            'commandes_mois' => Commande::whereHas('items.piece', function($query) use($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', now()->month)->count(),

            'commandes_en_cours' => Commande::whereHas('items.piece', function($query) use($user) {
                $query->where('user_id', $user->id);
            })->whereIn('statut', ['en_attente', 'confirmee', 'en_preparation', 'expedie'])->count(),

            'commandes_total' => Commande::whereHas('items.piece', function($query) use($user) {
                $query->where('user_id', $user->id);
            })->count(),

            // Chiffre d'affaires
            'chiffre_affaires_mois' => Commande::whereHas('items.piece', function($query) use($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', now()->month)->sum('total'),

            'chiffre_affaires_total' => Commande::whereHas('items.piece', function($query) use($user) {
                $query->where('user_id', $user->id);
            })->whereIn('statut', ['livree', 'terminee'])->sum('total'),
        ];

        // 📋 Pièces récentes
        $recentPieces = Piece::where('user_id', $user->id)
            ->with(['marque', 'modele', 'nomPiece'])
            ->latest()
            ->take(5)
            ->get();

        // 🚗 Véhicules récents
        $recentVehicules = DemandeEpave::where('user_id', $user->id)
            ->where('type', 'vehicule')
            ->latest()
            ->take(5)
            ->get();

        // 💥 Épaves récentes
        $recentEpaves = DemandeEpave::where('user_id', $user->id)
            ->where('type', 'epave')
            ->latest()
            ->take(5)
            ->get();

        // 📦 Commandes récentes
        $recentCommandes = Commande::whereHas('items.piece', function($query) use($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['user', 'items.piece'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.casse', compact(
            'stats',
            'recentPieces',
            'recentVehicules',
            'recentEpaves',
            'recentCommandes'
        ));
    }

    private function clientDashboard()
    {
        $user = Auth::user();

        $stats = [
            // Commandes
            'commandes_total' => $user->commandes()->count(),
            'commandes_en_cours' => $user->commandes()
                ->whereIn('statut', ['en_attente', 'confirmee', 'en_preparation', 'expedie'])
                ->count(),

            // Panier
            'panier_items' => optional($user->panier)->items()->count() ?? 0,

            // Favoris
            'favoris' => $user->favoris()->count(),

            // Véhicules (mes annonces de véhicules)
            'mes_vehicules_total' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'vehicule')
                ->count(),
            'mes_vehicules_disponibles' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'vehicule')
                ->where('statut', 'disponible')
                ->count(),
            'mes_vehicules_vendus' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'vehicule')
                ->where('statut', 'vendu')
                ->count(),

            // Épaves (mes annonces d'épaves)
            'mes_epaves_total' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'epave')
                ->count(),
            'mes_epaves_disponibles' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'epave')
                ->where('statut', 'disponible')
                ->count(),
            'mes_epaves_vendues' => DemandeEpave::where('user_id', $user->id)
                ->where('type', 'epave')
                ->where('statut', 'vendu')
                ->count(),
        ];

        // Commandes récentes
        $recentCommandes = $user->commandes()
            ->with(['items.piece'])
            ->latest()
            ->take(5)
            ->get();

        // Notifications non lues
        $notifications = $user->notifications()
            ->where('lu', false)
            ->latest()
            ->take(5)
            ->get();

        // Pièces populaires
        $piecesPopulaires = Piece::where('disponible', true)
            ->withCount('commandeItems')
            ->orderBy('commande_items_count', 'desc')
            ->take(6)
            ->get();

        return view('dashboard.client', compact(
            'stats',
            'recentCommandes',
            'notifications',
            'piecesPopulaires'
        ));
    }

    private function adminDashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_casses' => User::where('role', 'casse')->count(),
            'total_clients' => User::where('role', 'client')->count(),
            'total_vehicles' => DemandeEpave::where('type', 'vehicule')->count(),
            'total_epaves' => DemandeEpave::where('type', 'epave')->count(),
            'total_pieces' => Piece::count(),
            'total_commandes' => Commande::count(),
            'chiffre_affaires' => Commande::where('statut_paiement', 'paye')->sum('total')
        ];

        $commandesParMois = Commande::select(
            DB::raw('MONTH(created_at) as mois'),
            DB::raw('COUNT(*) as total')
        )->whereYear('created_at', now()->year)
            ->groupBy('mois')
            ->get();

        $recentUsers = User::latest()->take(5)->get();
        $recentCommandes = Commande::with(['user', 'items.piece'])->latest()->take(5)->get();

        $commandes = DB::table('commandes')
            ->selectRaw('MONTH(created_at) as mois, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        // Transformer les données pour Chart.js
        $labels_chart = [];
        $data = [];
        foreach ($commandes as $commande) {
            $labels_chart[] = date("F", mktime(0, 0, 0, $commande->mois, 1));
            $data[] = $commande->total;
        }

        $clientsInscrits = DB::table('users')
            ->selectRaw('MONTH(created_at) as mois, COUNT(*) as total')
            ->where('role', 'client')
            ->whereYear('created_at', now()->year)
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        $dataClients = [];
        foreach ($clientsInscrits as $cli) {
            $dataClients[] = $cli->total;
        }

        return view('dashboard.admin', compact(
            'stats',
            'commandesParMois',
            'recentUsers',
            'recentCommandes',
            'data',
            'labels_chart',
            'dataClients'
        ));
    }
}
