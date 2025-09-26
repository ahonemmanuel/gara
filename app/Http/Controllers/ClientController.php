<?php
// app/Http/Controllers/ClientController.php
namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Favoris;
use App\Models\Notification;
use App\Models\Panier;
use App\Models\Piece;
use App\Models\RechercheSauvegardee;
use App\Models\User;
use App\Models\VenteEpave;
use App\Models\Vehicule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Statistiques du dashboard
        $stats = [
            'commandes_total' => $user->commandes()->count(),
            'commandes_en_cours' => $user->commandes()
                ->whereIn('statut', ['en_attente', 'confirmee', 'preparation', 'expediee'])
                ->count(),
            'panier_items' => $user->paniers()->sum('quantite'),
            'favoris_count' => $user->favoris()->count(),
            'notifications_non_lues' => $user->notifications()->whereNull('read_at')->count(),
            'ventes_epaves' => $user->venteEpaves()->count()
        ];

        // Commandes récentes (5 dernières)
        $commandesRecentes = $user->commandes()
            ->with(['casse', 'pieces'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Notifications récentes (5 dernières)
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pièces récemment consultées (simulation - à implémenter avec historique)
        $piecesPopulaires = Piece::with(['vehicule', 'casse'])
            ->where('disponible', true)
            ->where('quantite', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Casses à proximité (si géolocalisation disponible)
        $cassesProches = [];
        if ($user->latitude && $user->longitude) {
            $cassesProches = User::where('role', 'casse')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->map(function ($casse) use ($user) {
                    $distance = $this->calculateDistance(
                        $user->latitude,
                        $user->longitude,
                        $casse->latitude,
                        $casse->longitude
                    );
                    $casse->distance = round($distance, 1);
                    return $casse;
                })
                ->where('distance', '<=', 50) // Dans un rayon de 50km
                ->sortBy('distance')
                ->take(3);
        }

        return view('client.dashboard', compact(
            'stats',
            'commandesRecentes',
            'notifications',
            'piecesPopulaires',
            'cassesProches'
        ));
    }

    public function recherchePieces(Request $request)
    {
        $query = Piece::with(['vehicule', 'casse'])
            ->where('quantite', '>', 0);

        // Filtres
        if ($request->filled('marque')) {
            $query->whereHas('vehicule', function($q) use ($request) {
                $q->where('marque', 'like', '%' . $request->marque . '%');
            });
        }

        if ($request->filled('modele')) {
            $query->whereHas('vehicule', function($q) use ($request) {
                $q->where('modele', 'like', '%' . $request->modele . '%');
            });
        }

        if ($request->filled('annee_min')) {
            $query->whereHas('vehicule', function($q) use ($request) {
                $q->where('annee', '>=', $request->annee_min);
            });
        }

        if ($request->filled('annee_max')) {
            $query->whereHas('vehicule', function($q) use ($request) {
                $q->where('annee', '<=', $request->annee_max);
            });
        }

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        if ($request->filled('prix_min')) {
            $query->where('prix', '>=', $request->prix_min);
        }

        if ($request->filled('prix_max')) {
            $query->where('prix', '<=', $request->prix_max);
        }

        if ($request->filled('etat')) {
            $query->where('etat', $request->etat);
        }

        // Tri
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        $pieces = $query->paginate(12);

        // Données pour les filtres
        $marques = Piece::join('vehicules', 'pieces.vehicule_id', '=', 'vehicules.id')
            ->distinct()->pluck('vehicules.marque')->filter();

        $categories = Piece::distinct()->pluck('categorie')->filter();

        return view('client.recherche-pieces', compact('pieces', 'marques', 'categories'));
    }

    public function panier()
    {
        $panierItems = Auth::user()->paniers()->with('piece.vehicule', 'piece.casse')->get();
        $total = $panierItems->sum(function($item) {
            return $item->quantite * $item->piece->prix;
        });

        return view('client.panier', compact('panierItems', 'total'));
    }

    public function ajouterAuPanier(Request $request, Piece $piece)
    {
        $quantite = $request->quantite ?? 1;

        // Vérifier le stock
        if ($piece->quantite < $quantite) {
            return response()->json(['success' => false, 'message' => 'Stock insuffisant']);
        }

        // Vérifier si l'article est déjà dans le panier
        $panier = Panier::where('client_id', Auth::id())
            ->where('piece_id', $piece->id)
            ->first();

        if ($panier) {
            $panier->increment('quantite', $quantite);
        } else {
            Panier::create([
                'client_id' => Auth::id(),
                'piece_id' => $piece->id,
                'quantite' => $quantite
            ]);
        }

        $panierCount = Auth::user()->getNombrePiecesPanier();

        return response()->json([
            'success' => true,
            'panier_count' => $panierCount
        ]);
    }

    public function retirerDuPanier(Panier $panier)
    {
        if ($panier->client_id !== Auth::id()) {
            abort(403);
        }

        $panier->delete();

        return response()->json(['success' => true]);
    }

    public function updateQuantitePanier(Request $request, Panier $panier)
    {
        if ($panier->client_id !== Auth::id()) {
            abort(403);
        }

        $quantite = $request->quantite;

        if ($quantite <= 0) {
            $panier->delete();
        } else {
            // Vérifier le stock
            if ($panier->piece->quantite < $quantite) {
                return response()->json(['success' => false, 'message' => 'Stock insuffisant']);
            }

            $panier->update(['quantite' => $quantite]);
        }

        return response()->json(['success' => true]);
    }

    public function passerCommande(Request $request)
    {
        $user = Auth::user();
        $panierItems = $user->paniers()->with('piece.casse')->get();

        if ($panierItems->isEmpty()) {
            return redirect()->back()->with('error', 'Votre panier est vide');
        }

        // Vérifier le stock
        foreach ($panierItems as $item) {
            if ($item->piece->quantite < $item->quantite) {
                return redirect()->back()->with('error', "Stock insuffisant pour {$item->piece->nom}");
            }
        }

        // Grouper les items par casse_id
        $groupedByCasse = $panierItems->groupBy(fn($item) => $item->piece->casse->id);

        $commandesCreees = [];

        foreach ($groupedByCasse as $casseId => $items) {
            // Calculer le total pour cette casse
            $totalCasse = $items->sum(fn($item) => $item->quantite * $item->piece->prix);

            // Créer la commande pour cette casse
            $commande = Commande::create([
                'date_commande' => now(),
                'client_id' => $user->id,
                'casse_id' => $casseId,
                'numero_commande' => 'CMD' . time() . '-' . $casseId,
                'total' => $totalCasse,
                'statut' => 'en_attente',
                'adresse_livraison' => $request->adresse_livraison,
                'methode_paiement' => $request->methode_paiement
            ]);

            // Créer les lignes de commande
            foreach ($items as $item) {
                $commande->pieces()->attach($item->piece_id, [
                    'quantite' => $item->quantite,
                    'prix_unitaire' => $item->piece->prix
                ]);

                // Décrémenter le stock
                $item->piece->decrement('quantite', $item->quantite);
            }

            $commandesCreees[] = $commande;

            // ✅ Notification client
            Notification::create([
                'user_id' => $user->id,
                'type' => 'commande',
                'title' => 'Commande confirmée',
                'message' => "Votre commande #{$commande->numero_commande} a été confirmée",
                'data' => ['commande_id' => $commande->id]
            ]);

            // ✅ Notification pour la casse (vendeur)
            $casseUser = $commande->casse; // relation belongsTo(User::class, 'casse_id')
            if ($casseUser) {
                Notification::create([
                    'user_id' => $casseUser->id,
                    'type' => 'commande',
                    'title' => 'Nouvelle commande reçue',
                    'message' => "Vous avez reçu une nouvelle commande #{$commande->numero_commande} de {$user->name}",
                    'data' => ['commande_id' => $commande->id]
                ]);
            }
        }

        // Supprimer tous les items du panier après la commande
        $user->paniers()->delete();

        return redirect()->route('client.commandes')
            ->with('success', count($commandesCreees) . ' commande(s) passée(s) avec succès');
    }

    public function venteEpaves()
    {
        $ventes = Auth::user()->venteEpaves()->latest()->get();
        return view('client.vente-epaves', compact('ventes'));
    }

    public function creerVenteEpave(Request $request)
    {
        $request->validate([
            'marque' => 'required|string|max:255',
            'modele' => 'required|string|max:255',
            'annee' => 'required|integer',
            'immatriculation' => 'required|string|max:20',
            'description' => 'required|string',
            'etat' => 'required|in:bon,etat,mauvais,epave',
            'photos' => 'nullable|array',
            'prix_souhaite' => 'required|numeric|min:0'
        ]);

        $vente = VenteEpave::create([
            'client_id' => Auth::id(),
            'marque' => $request->marque,
            'modele' => $request->modele,
            'annee' => $request->annee,
            'immatriculation' => $request->immatriculation,
            'description' => $request->description,
            'etat' => $request->etat,
            'photos' => $request->photos ?? [],
            'prix_souhaite' => $request->prix_souhaite,
            'statut' => 'en_attente'
        ]);

        return redirect()->route('client.vente-epaves.detail', $vente)
            ->with('success', 'Votre demande de vente a été soumise');
    }





    public function commandes()
    {
        $commandes = auth()->user()->commandes()
            ->with(['casse', 'pieces'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('client.commandes', compact('commandes'));
    }



    public function favoris()
    {
        $favoris = auth()->user()->favoris()
            ->with(['piece.vehicule', 'piece.casse'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('client.favoris', compact('favoris'));
    }

    public function ajouterAuxFavoris(Piece $piece)
    {
        $existe = Favoris::where('client_id', auth()->id())
            ->where('piece_id', $piece->id)
            ->exists();

        if (!$existe) {
            Favoris::create([
                'client_id' => auth()->id(),
                'piece_id' => $piece->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pièce ajoutée aux favoris',
                'action' => 'added'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pièce déjà dans les favoris'
        ]);
    }

    public function retirerDesFavoris(Piece $piece)
    {
        Favoris::where('client_id', auth()->id())
            ->where('piece_id', $piece->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pièce retirée des favoris',
            'action' => 'removed'
        ]);
    }

    public function sauvegarderRecherche(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'criteres' => 'required|array',
            'notifications_activees' => 'boolean'
        ]);

        RechercheSauvegardee::create([
            'client_id' => auth()->id(),
            'nom' => $request->nom,
            'criteres' => $request->criteres,
            'notifications_activees' => $request->boolean('notifications_activees')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recherche sauvegardée avec succès'
        ]);
    }

    public function recherchesSauvegardees()
    {
        $recherches = auth()->user()->recherchesSauvegardees()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.recherches-sauvegardees', compact('recherches'));
    }

    public function supprimerRechercheSauvegardee(RechercheSauvegardee $recherche)
    {
        if ($recherche->client_id !== auth()->id()) {
            abort(403);
        }

        $recherche->delete();

        return redirect()->back()->with('success', 'Recherche supprimée');
    }

    public function notifications()
    {
        $notifications = auth()->user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('client.notifications', compact('notifications'));
    }

    public function marquerNotificationLue(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function marquerToutesNotificationsLues()
    {
        auth()->user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function cassesProches(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $rayon = $request->get('rayon', 50); // 50km par défaut

        $casses = User::where('role', 'casse')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($casse) use ($latitude, $longitude) {
                $distance = $casse->getDistanceFrom($latitude, $longitude);
                $casse->distance = $distance;
                return $casse;
            })
            ->filter(function ($casse) use ($rayon) {
                return $casse->distance && $casse->distance <= $rayon;
            })
            ->sortBy('distance');

        return view('client.casses-proches', compact('casses', 'latitude', 'longitude', 'rayon'));
    }

    public function detailPiece(Piece $piece)
    {
        $piece->load(['vehicule', 'casse']);

        $piecesSimilaires = Piece::where('id', '!=', $piece->id)
            ->where('categorie', $piece->categorie)
            ->whereHas('vehicule', function ($q) use ($piece) {
                $q->where('marque', $piece->vehicule->marque);
            })
            ->where('disponible', true)
            ->where('quantite', '>', 0)
            ->limit(4)
            ->get();

        $estDansFavoris = auth()->check()
            ? Favoris::where('client_id', auth()->id())
                ->where('piece_id', $piece->id)
                ->exists()
            : false;

        return view('client.detail-piece', compact('piece', 'piecesSimilaires', 'estDansFavoris'));
    }

    public function profile()
    {
        $user = auth()->user();
        return view('client.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:10'
        ]);

        $user->update($request->all());

        return redirect()->back()->with('success', 'Profil mis à jour avec succès');
    }

    public function detailCommande(Commande $commande)
    {
        if ($commande->client_id !== auth()->id()) {
            abort(403);
        }

        $commande->load(['casse', 'pieces']);

        return view('client.detail-commande', compact('commande'));
    }
}
