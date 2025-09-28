<?php

// app/Http/Controllers/CommandeController.php
namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CommandeItem;
use App\Models\Notification;
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
            // Commandes reçues par la casse
            $query = Commande::whereHas('items.piece.vehicle', function($query) use($user) {
                $query->where('casse_id', $user->id);
            })->with(['user', 'items.piece.vehicle']);

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            $commandes = $query->latest()->paginate(10);
        } else {
            // Commandes passées par le client
            $query = $user->commandes()->with(['items.piece.vehicle.casse']);

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            $commandes = $query->latest()->paginate(10);
        }

        return view('commandes.index', compact('commandes'));
    }

    public function create()
    {
        $panier = Auth::user()->panier()->with(['items.piece.vehicle.casse'])->first();

        if (!$panier || $panier->items->isEmpty()) {
            return redirect()->route('panier.index')
                ->with('error', 'Votre panier est vide.');
        }

        // Vérifier la disponibilité des pièces
        foreach ($panier->items as $item) {
            if (!$item->piece->disponible || $item->piece->quantite < $item->quantite) {
                return redirect()->route('panier.index')
                    ->with('error', "La pièce {$item->piece->nom} n'est plus disponible en quantité suffisante.");
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
            'notes' => 'nullable|string'
        ]);

        $panier = Auth::user()->panier()->with(['items.piece.vehicle.casse'])->first();

        if (!$panier || $panier->items->isEmpty()) {
            return redirect()->route('panier.index')
                ->with('error', 'Votre panier est vide.');
        }

        // Vérifier la disponibilité des pièces
        foreach ($panier->items as $item) {
            if (!$item->piece->disponible || $item->piece->quantite < $item->quantite) {
                return back()->with('error',
                    "Stock insuffisant pour la pièce: {$item->piece->nom}");
            }
        }

        $commande = null;

        DB::transaction(function() use ($request, $panier, &$commande) {
            // Créer la commande
            $commande = Commande::create([
                'user_id' => Auth::id(),
                'numero_commande' => 'CMD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'statut' => 'en_attente',
                'total' => $panier->getTotal(),
                'adresse_livraison' => $request->adresse_livraison,
                'telephone_livraison' => $request->telephone_livraison,
                'mode_paiement' => $request->mode_paiement,
                'statut_paiement' => 'en_attente',
                'notes' => $request->notes
            ]);

            // Créer les items de commande et mettre à jour le stock
            foreach ($panier->items as $item) {
                CommandeItem::create([
                    'commande_id' => $commande->id,
                    'piece_id' => $item->piece_id,
                    'quantite' => $item->quantite,
                    'prix_unitaire' => $item->piece->prix
                ]);

                // Mettre à jour le stock
                $item->piece->decrement('quantite', $item->quantite);

                // Marquer comme indisponible si stock = 0
                if ($item->piece->quantite <= 0) {
                    $item->piece->update(['disponible' => false]);
                }

                // Notifier la casse
                $this->notificationService->nouvelleCommande($item->piece->vehicle->casse, $commande);
            }

            // Vider le panier
            $panier->items()->delete();

            // Créer notification pour le client
            $this->notificationService->commandeCreee(Auth::user(), $commande);
        });

        return redirect()->route('commandes.show', $commande)
            ->with('success', 'Commande créée avec succès.');
    }

    public function show(Commande $commande)
    {
        $this->authorize('view', $commande);
        $commande->load(['user', 'items.piece.vehicle.casse']);
        return view('commandes.show', compact('commande'));
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

        // Créer notification pour le client
        $this->notificationService->statutCommandeChange($commande->user, $commande, $ancienStatut, $request->statut);

        return back()->with('success', 'Statut de la commande mis à jour.');
    }

    public function annuler(Commande $commande)
    {
        $this->authorize('annuler', $commande);

        if (!in_array($commande->statut, ['en_attente', 'confirmee'])) {
            return back()->with('error', 'Cette commande ne peut plus être annulée.');
        }

        DB::transaction(function() use ($commande) {
            // Remettre les quantités en stock
            foreach ($commande->items as $item) {
                $item->piece->increment('quantite', $item->quantite);
                $item->piece->update(['disponible' => true]);
            }

            $commande->update(['statut' => 'annulee']);

            // Notifier les casses concernées
            $casses = $commande->items->map(function($item) {
                return $item->piece->vehicle->casse;
            })->unique();

            foreach ($casses as $casse) {
                $this->notificationService->commandeAnnulee($casse, $commande);
            }
        });

        return back()->with('success', 'Commande annulée avec succès.');
    }
}
