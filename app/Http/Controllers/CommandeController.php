<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CommandeItem;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isCasse()) {
            $query = Commande::where(function($q) use ($user) {
                $q->whereHas('items.piece', function($subq) use ($user) {
                    $subq->where('user_id', $user->id);
                })
                    ->orWhereHas('items.vehicule', function($subq) use ($user) {
                        $subq->where('user_id', $user->id);
                    });
            })->with(['user', 'items.piece', 'items.vehicule']);

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            $commandes = $query->latest()->paginate(10);
        } else {
            $query = $user->commandes()->with(['items.piece', 'items.vehicule']);
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }
            $commandes = $query->latest()->paginate(10);
        }

        return view('commandes.index', compact('commandes'));
    }

    public function create()
    {
        $panier = Auth::user()->panier()->with(['items.piece', 'items.vehicule'])->first();

        if (!$panier || $panier->items->isEmpty()) {
            return redirect()->route('panier.index')
                ->with('error', 'Votre panier est vide.');
        }

        // Vérifier la disponibilité de tous les items
        foreach ($panier->items as $item) {
            if ($item->estPiece()) {
                if (!$item->piece || !$item->piece->disponible || $item->piece->quantite < $item->quantite) {
                    return redirect()->route('panier.index')
                        ->with('error', "La pièce {$item->nom} n'est plus disponible en quantité suffisante.");
                }
            } elseif ($item->estVehicule()) {
                if (!$item->vehicule || !$item->vehicule->estDisponible() || $item->vehicule->quantite < $item->quantite) {
                    return redirect()->route('panier.index')
                        ->with('error', "Le véhicule {$item->nom} n'est plus disponible.");
                }
            }
        }

        return view('commandes.create', compact('panier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'adresse_livraison' => 'required|string',
            'telephone_livraison' => 'required|string',
            'mode_paiement' => 'required|in:carte_bancaire,paypal,virement,especes',
            'notes' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $panier = Auth::user()->panier()->with(['items.piece', 'items.vehicule'])->first();

        if (!$panier || $panier->items->isEmpty()) {
            return redirect()->route('panier.index')
                ->with('error', 'Votre panier est vide.');
        }

        // Vérifier la disponibilité de tous les items
        foreach ($panier->items as $item) {
            if ($item->estPiece()) {
                if (!$item->piece || !$item->piece->disponible || $item->piece->quantite < $item->quantite) {
                    return back()->with('error', "Stock insuffisant pour la pièce: {$item->nom}");
                }
            } elseif ($item->estVehicule()) {
                if (!$item->vehicule || !$item->vehicule->estDisponible() || $item->vehicule->quantite < $item->quantite) {
                    return back()->with('error', "Le véhicule {$item->nom} n'est plus disponible.");
                }
            }
        }

        $commande = null;

        DB::transaction(function() use ($request, $panier, &$commande) {
            $commande = Commande::create([
                'user_id' => Auth::id(),
                'numero_commande' => 'CMD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'statut' => 'en_attente',
                'total' => $panier->getTotal(),
                'adresse_livraison' => $request->adresse_livraison,
                'telephone_livraison' => $request->telephone_livraison,
                'mode_paiement' => $request->mode_paiement,
                'statut_paiement' => 'en_attente',
                'notes' => $request->notes,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            $vendeurs = collect();

            foreach ($panier->items as $item) {
                // Créer l'item de commande
                CommandeItem::create([
                    'commande_id' => $commande->id,
                    'piece_id' => $item->piece_id,
                    'vehicule_id' => $item->vehicule_id,
                    'quantite' => $item->quantite,
                    'prix_unitaire' => $item->prix
                ]);

                // Mettre à jour le stock et notifier le vendeur
                if ($item->estPiece()) {
                    $item->piece->decrement('quantite', $item->quantite);
                    if ($item->piece->quantite <= 0) {
                        $item->piece->update(['disponible' => false]);
                    }
                    $vendeurs->push($item->piece->user);
                } elseif ($item->estVehicule()) {
                    $item->vehicule->decrement('quantite', $item->quantite);
                    if ($item->vehicule->quantite <= 0) {
                        $item->vehicule->update(['statut' => 'vendu']);
                    }
                    $vendeurs->push($item->vehicule->vendeur);
                }
            }

            // Notifier tous les vendeurs uniques
            foreach ($vendeurs->unique('id') as $vendeur) {
                $this->notificationService->nouvelleCommande($vendeur, $commande);
            }

            // Vider le panier
            $panier->items()->delete();

            // Notification au client
            $this->notificationService->commandeCreee(Auth::user(), $commande);
        });

        return redirect()->route('commandes.show', $commande)
            ->with('success', 'Commande créée avec succès.');
    }

    public function show(Commande $commande)
    {
        $this->authorize('view', $commande);
        $commande->load(['user', 'items.piece', 'items.vehicule']);
        return view('commandes.show', compact('commande'));
    }

    public function editStatut(Commande $commande)
    {
        return view('commandes.edit', compact('commande'));
    }

    public function updateStatut(Request $request, Commande $commande)
    {
        $this->authorize('updateStatut', $commande);

        $request->validate([
            'statut' => 'required|in:en_attente,confirmee,en_preparation,expedie,livree,annulee',
            'commentaire' => 'nullable|string'
        ]);

        $ancienStatut = $commande->statut;
        $commande->update(['statut' => $request->statut]);

        $this->notificationService->statutCommandeChange($commande->user, $commande, $ancienStatut, $request->statut);

        return back()->with('success', 'Statut de la commande mis à jour.');
    }

    public function annuler(Commande $commande)
    {
        $this->authorize('annuler', $commande);

        if (!in_array($commande->statut, ['en_attente', 'confirmee'])) {
            return back()->with('error', 'Cette commande ne peut plus être annulée.');
        }

        $commande->load('items.piece', 'items.vehicule');

        // Vérifier que tous les items existent encore
        $itemsSupprimees = $commande->items->filter(function($item) {
            return ($item->estPiece() && is_null($item->piece)) ||
                ($item->estVehicule() && is_null($item->vehicule));
        });

        if ($itemsSupprimees->isNotEmpty()) {
            return back()->with('error',
                'Impossible d\'annuler cette commande car certains articles ne sont plus disponibles dans le catalogue. ' .
                'Veuillez contacter le service client pour obtenir de l\'aide.'
            );
        }

        DB::transaction(function() use ($commande) {
            $piecesRestaurees = 0;
            $vehiculesRestaurees = 0;

            foreach ($commande->items as $item) {
                if ($item->estPiece() && $item->piece) {
                    // Restaurer le stock de la pièce
                    $item->piece->increment('quantite', $item->quantite);
                    $item->piece->update(['disponible' => true]);
                    $piecesRestaurees++;
                } elseif ($item->estVehicule() && $item->vehicule) {
                    // Restaurer le stock du véhicule
                    $item->vehicule->increment('quantite', $item->quantite);
                    $item->vehicule->update(['statut' => 'disponible']);
                    $vehiculesRestaurees++;
                }
            }

            // Marquer la commande comme annulée
            $commande->update(['statut' => 'annulee']);

            // Notifier tous les vendeurs uniques
            $vendeurs = $commande->items->map(function($item) {
                if ($item->estPiece()) {
                    return $item->piece ? $item->piece->user : null;
                } elseif ($item->estVehicule()) {
                    return $item->vehicule ? $item->vehicule->vendeur : null;
                }
                return null;
            })->filter()->unique('id');

            foreach ($vendeurs as $vendeur) {
                $this->notificationService->commandeAnnulee($vendeur, $commande);
            }

            // Log pour debug
            \Log::info("Commande {$commande->numero_commande} annulée", [
                'pieces_restaurees' => $piecesRestaurees,
                'vehicules_restaures' => $vehiculesRestaurees,
                'total_items' => $commande->items->count()
            ]);
        });

        return back()->with('success', 'Commande annulée avec succès. Les stocks ont été restaurés.');
    }

    public function updateAdresse(Request $request, Commande $commande)
    {
        $this->authorize('update', $commande);

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $adresseGeo = "Lat: {$request->latitude}, Lon: {$request->longitude}";
        $commande->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'adresse_livraison' => $adresseGeo . "\n" . $commande->adresse_livraison
        ]);

        return back()->with('success', 'Adresse de livraison mise à jour avec votre géolocalisation.');
    }
}
