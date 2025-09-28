<?php

// app/Http/Controllers/DemandeEpaveController.php
namespace App\Http\Controllers;

use App\Models\DemandeEpave;
use App\Models\OffreEpave;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DemandeEpaveController extends Controller
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
            // Voir toutes les demandes disponibles
            $query = DemandeEpave::with(['user', 'offres'])
                ->where('statut', 'en_attente');

            if ($request->filled('marque')) {
                $query->where('marque', $request->marque);
            }

            if ($request->filled('prix_max')) {
                $query->where('prix_souhaite', '<=', $request->prix_max);
            }

            $demandes = $query->latest()->paginate(10);
        } else {
            // Voir ses propres demandes
            $demandes = $user->demandesEpaves()->with('offres.casse')->latest()->paginate(10);
        }

        return view('demandes-epaves.index', compact('demandes'));
    }

    public function create()
    {
        return view('demandes-epaves.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'marque' => 'required|string|max:255',
            'modele' => 'required|string|max:255',
            'annee' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'numero_chassis' => 'required|string',
            'numero_plaque' => 'required|string',
            'couleur' => 'required|string',
            'carburant' => 'required|in:essence,diesel,hybride,electrique',
            'kilometrage' => 'required|integer|min:0',
            'etat' => 'required|in:bon,moyen,mauvais,epave',
            'prix_souhaite' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'photos.*' => 'nullable|image|max:2048',
            'telephone_contact' => 'required|string',
            'adresse' => 'required|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['statut'] = 'en_attente';

        // Upload photos
        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('demandes-epaves', 'public');
            }
            $validated['photos'] = $photos;
        }

        $demande = DemandeEpave::create($validated);

        // Notifier les casses dans la région
        $casses = User::where('role', 'casse')
            ->where('actif', true)
            ->get();

        foreach ($casses as $casse) {
            $this->notificationService->nouvelleDemande($casse, $demande);
        }

        return redirect()->route('demandes-epaves.show', $demande)
            ->with('success', 'Demande de vente créée avec succès.');
    }

    public function show(DemandeEpave $demandeEpave)
    {
        $demandeEpave->load(['user', 'offres.casse']);

        $peutFaireOffre = Auth::user()->isCasse() &&
            $demandeEpave->statut === 'en_attente' &&
            !$demandeEpave->offres()->where('casse_id', Auth::id())->exists();

        return view('demandes-epaves.show', compact('demandeEpave', 'peutFaireOffre'));
    }

    public function faireOffre(Request $request, DemandeEpave $demandeEpave)
    {
        $this->authorize('faireOffre', $demandeEpave);

        $request->validate([
            'prix_offert' => 'required|numeric|min:0',
            'message' => 'nullable|string'
        ]);

        // Vérifier qu'aucune offre n'existe déjà
        if ($demandeEpave->offres()->where('casse_id', Auth::id())->exists()) {
            return back()->with('error', 'Vous avez déjà fait une offre pour cette demande.');
        }

        $offre = OffreEpave::create([
            'demande_epave_id' => $demandeEpave->id,
            'casse_id' => Auth::id(),
            'prix_offert' => $request->prix_offert,
            'message' => $request->message,
            'statut' => 'en_attente'
        ]);

        // Notifier le propriétaire du véhicule
        $this->notificationService->nouvelleOffre($demandeEpave->user, $offre);

        return back()->with('success', 'Offre envoyée avec succès.');
    }

    public function accepterOffre(DemandeEpave $demandeEpave, OffreEpave $offre)
    {
        $this->authorize('accepterOffre', $demandeEpave);

        DB::transaction(function() use ($offre, $demandeEpave) {
            // Accepter l'offre
            $offre->update(['statut' => 'accepte']);

            // Refuser les autres offres liées à cette demande
            $demandeEpave->offres()
                ->where('id', '!=', $offre->id)
                ->update(['statut' => 'refuse']);
        });

        return back()->with('success', 'Offre acceptée avec succès.');
    }

    public function edit(DemandeEpave $demandeEpave)
    {
        $this->authorize('update', $demandeEpave);
        return view('demandes-epaves.edit', compact('demandeEpave'));
    }

    public function update(Request $request, DemandeEpave $demandeEpave)
    {
        $this->authorize('update', $demandeEpave);

        $validated = $request->validate([
            'marque' => 'required|string|max:255',
            'modele' => 'required|string|max:255',
            'annee' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'couleur' => 'required|string',
            'carburant' => 'required|in:essence,diesel,hybride,electrique',
            'kilometrage' => 'required|integer|min:0',
            'etat' => 'required|in:bon,moyen,mauvais,epave',
            'prix_souhaite' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'photos.*' => 'nullable|image|max:2048',
            'telephone_contact' => 'required|string',
            'adresse' => 'required|string',
        ]);

        // Upload nouvelles photos
        if ($request->hasFile('photos')) {
            // Supprimer les anciennes photos
            if ($demandeEpave->photos) {
                foreach ($demandeEpave->photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('demandes-epaves', 'public');
            }
            $validated['photos'] = $photos;
        }

        $demandeEpave->update($validated);

        return redirect()->route('demandes-epaves.show', $demandeEpave)
            ->with('success', 'Demande mise à jour avec succès.');
    }

    public function destroy(DemandeEpave $demandeEpave)
    {
        $this->authorize('delete', $demandeEpave);

        // Supprimer les photos
        if ($demandeEpave->photos) {
            foreach ($demandeEpave->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $demandeEpave->delete();

        return redirect()->route('demandes-epaves.index')
            ->with('success', 'Demande supprimée avec succès.');
    }
}
