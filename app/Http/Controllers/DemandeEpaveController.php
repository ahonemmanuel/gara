<?php

namespace App\Http\Controllers;

use App\Models\DemandeEpave;
use App\Models\User;
use App\Models\Marque;
use App\Models\Modele;
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

        // Mes annonces
        $mesDemandes = DemandeEpave::with(['vendeur'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'mes_demandes');

        // Annonces des autres utilisateurs
        $query = DemandeEpave::with(['vendeur'])
            ->where('user_id', '!=', $user->id)
            ->whereNotIn('statut', ['vendu', 'reserve']); // 👈 filtre ajouté

        if ($request->filled('marque')) {
            $query->where('marque', 'like', '%' . $request->marque . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('prix_max')) {
            $query->where('prix_souhaite', '<=', $request->prix_max);
        }

        if ($request->filled('etat')) {
            $query->where('etat', $request->etat);
        }

        $autresDemandes = $query->latest()->paginate(10);

        return view('demandes-epaves.index', compact('autresDemandes', 'mesDemandes'));
    }

    public function show(DemandeEpave $demandeEpave)
    {
        $demandeEpave->load(['vendeur']);

        $peutAjouterPanier = Auth::check() &&
            $demandeEpave->peutEtreAjouteAuPanier(Auth::id());

        $annancesSimilaires = DemandeEpave::with(['vendeur'])
            ->where('id', '!=', $demandeEpave->id)
            ->where('statut', 'disponible')
            ->where(function($q) use ($demandeEpave) {
                $q->where('marque', $demandeEpave->marque)
                    ->orWhere('modele', $demandeEpave->modele);
            })
            ->limit(4)
            ->get();

        return view('demandes-epaves.show', compact('demandeEpave', 'peutAjouterPanier', 'annancesSimilaires'));
    }

    public function create()
    {
        $user = Auth::user();

        if (!$this->utilisateurProfilComplet($user)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Veuillez compléter votre profil avant de créer une annonce.')
                ->with('required_fields', $this->getChampsManquants($user));
        }

        $marques = Marque::active()->orderBy('nom')->get();
        return view('demandes-epaves.create', compact('marques'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$this->utilisateurProfilComplet($user)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Veuillez compléter votre profil avant de créer une annonce.')
                ->with('required_fields', $this->getChampsManquants($user));
        }

        $messages = [
            'marque_id.required_without' => 'Veuillez sélectionner ou ajouter une marque.',
            'new_marque.required_without' => 'Veuillez sélectionner ou ajouter une marque.',
            'modele_id.required_without' => 'Veuillez sélectionner ou ajouter un modèle.',
            'new_modele.required_without' => 'Veuillez sélectionner ou ajouter un modèle.',
        ];

        $validated = $request->validate([
            'type' => 'required|in:vehicule,epave',
            'marque_id' => 'required_without:new_marque|nullable',
            'new_marque' => 'required_without:marque_id|nullable|string|max:255',
            'modele_id' => 'required_without:new_modele|nullable',
            'new_modele' => 'required_without:modele_id|nullable|string|max:255',
            'annee' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'numero_chassis' => 'required|string',
            'numero_plaque' => 'required|string',
            'couleur' => 'required|string',
            'carburant' => 'required|in:essence,diesel,hybride,electrique',
            'kilometrage' => 'required|integer|min:0',
            'etat' => 'required|in:bon,moyen,mauvais,epave',
            'prix_souhaite' => 'required|numeric|min:1',
            'quantite' => 'nullable|integer|min:1|max:1000',
            'description' => 'required|string',
            'photos.*' => 'nullable|image|max:2048',
        ], $messages);

        // Gestion marque
        if ($request->marque_id === 'new' && $request->filled('new_marque')) {
            $marque = Marque::firstOrCreate(
                ['nom' => trim($request->new_marque)],
                ['is_active' => true]
            );
            $validated['marque'] = $marque->nom;
        } else {
            $marque = Marque::find($request->marque_id);
            if ($marque) $validated['marque'] = $marque->nom;
        }

        // Gestion modèle
        if ($request->modele_id === 'new' && $request->filled('new_modele')) {
            $marqueId = isset($marque) ? $marque->id : $request->marque_id;
            $modele = Modele::firstOrCreate(
                ['marque_id' => $marqueId, 'nom' => trim($request->new_modele)],
                ['is_active' => true]
            );
            $validated['modele'] = $modele->nom;
        } else {
            $modele = Modele::find($request->modele_id);
            if ($modele) $validated['modele'] = $modele->nom;
        }

        unset($validated['marque_id'], $validated['new_marque'], $validated['modele_id'], $validated['new_modele']);

        $validated['user_id'] = Auth::id();
        $validated['telephone_contact'] = $user->telephone;
        $validated['adresse'] = $this->getAdresseComplete($user);

        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('demandes-epaves', 'public');
            }
            $validated['photos'] = $photos;
        }

        $validated['quantite'] = 1;

        $demande = DemandeEpave::create($validated);

        return redirect()->route('demandes-epaves.show', $demande)
            ->with('success', 'Annonce créée avec succès.');
    }

    public function edit(DemandeEpave $demandeEpave)
    {
        if ($demandeEpave->user_id !== Auth::id()) abort(403, 'Non autorisé.');

        $marques = Marque::active()->orderBy('nom')->get();
        $modeles = Modele::active()
            ->where('marque_id', optional(Marque::where('nom', $demandeEpave->marque)->first())->id)
            ->orderBy('nom')
            ->get();

        return view('demandes-epaves.edit', compact('demandeEpave', 'marques', 'modeles'));
    }

    public function update(Request $request, DemandeEpave $demandeEpave)
    {
        if ($demandeEpave->user_id !== Auth::id()) abort(403, 'Non autorisé.');

        $messages = [
            'marque_id.required_without' => 'Veuillez sélectionner ou ajouter une marque.',
            'new_marque.required_without' => 'Veuillez sélectionner ou ajouter une marque.',
            'modele_id.required_without' => 'Veuillez sélectionner ou ajouter un modèle.',
            'new_modele.required_without' => 'Veuillez sélectionner ou ajouter un modèle.',
        ];

        $validated = $request->validate([
            'type' => 'required|in:vehicule,epave',
            'marque_id' => 'required_without:new_marque|nullable',
            'new_marque' => 'required_without:marque_id|nullable|string|max:255',
            'modele_id' => 'required_without:new_modele|nullable',
            'new_modele' => 'required_without:modele_id|nullable|string|max:255',
            'annee' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'couleur' => 'required|string',
            'carburant' => 'required|in:essence,diesel,hybride,electrique',
            'kilometrage' => 'required|integer|min:0',
            'etat' => 'required|in:bon,moyen,mauvais,epave',
            'prix_souhaite' => 'required|numeric|min:1',
            'quantite' => 'nullable|integer|min:1|max:1000',
            'description' => 'required|string',
            'photos.*' => 'nullable|image|max:2048',
            'telephone_contact' => 'required|string',
            'adresse' => 'required|string',
        ], $messages);

        if ($request->marque_id === 'new' && $request->filled('new_marque')) {
            $marque = Marque::firstOrCreate(['nom' => trim($request->new_marque)], ['is_active' => true]);
            $validated['marque'] = $marque->nom;
        } else {
            $marque = Marque::find($request->marque_id);
            if ($marque) $validated['marque'] = $marque->nom;
        }

        if ($request->modele_id === 'new' && $request->filled('new_modele')) {
            $marqueId = isset($marque) ? $marque->id : $request->marque_id;
            $modele = Modele::firstOrCreate(['marque_id' => $marqueId, 'nom' => trim($request->new_modele)], ['is_active' => true]);
            $validated['modele'] = $modele->nom;
        } else {
            $modele = Modele::find($request->modele_id);
            if ($modele) $validated['modele'] = $modele->nom;
        }

        unset($validated['marque_id'], $validated['new_marque'], $validated['modele_id'], $validated['new_modele']);

        if ($request->hasFile('photos')) {
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
            ->with('success', 'Annonce mise à jour.');
    }

    public function destroy(DemandeEpave $demandeEpave)
    {
        if ($demandeEpave->user_id !== Auth::id()) abort(403, 'Non autorisé.');

        if ( $demandeEpave->statut === 'reserve') {
            return back()->with('error', 'Impossible de supprimer cette annonce.');
        }

        if ($demandeEpave->photos) {
            foreach ($demandeEpave->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $demandeEpave->delete();

        return redirect()->route('demandes-epaves.index')
            ->with('success', 'Annonce supprimée.');
    }

    public function getModelesByMarque($marqueId)
    {
        $modeles = Modele::active()->where('marque_id', $marqueId)->orderBy('nom')->get(['id', 'nom']);
        return response()->json($modeles);
    }

    private function utilisateurProfilComplet(User $user): bool
    {
        $champsRequis = ['name', 'email', 'telephone', 'ville'];

        foreach ($champsRequis as $champ) {
            if (empty($user->$champ)) return false;
        }

        if ($user->isCasse()) {
            foreach (['adresse'] as $champ) {
                if (empty($user->$champ)) return false;
            }
        }

        return true;
    }

    private function getChampsManquants(User $user): array
    {
        $champsManquants = [];
        $champsRequis = ['name' => 'Nom complet', 'email' => 'Email', 'telephone' => 'Téléphone', 'ville' => 'Ville'];

        if ($user->isCasse()) $champsRequis['adresse'] = 'Adresse complète';

        foreach ($champsRequis as $champ => $label) {
            if (empty($user->$champ)) $champsManquants[$champ] = $label;
        }

        return $champsManquants;
    }

    private function getAdresseComplete(User $user): string
    {
        $adresseComplete = [];
        if (!empty($user->adresse)) $adresseComplete[] = $user->adresse;
        if (!empty($user->ville)) $adresseComplete[] = $user->ville;
        if (!empty($user->code_postal)) $adresseComplete[] = $user->code_postal;

        return implode(', ', $adresseComplete);
    }

    public function debug()
    {
        $stats = [
            'total' => DemandeEpave::count(),
            'disponibles' => DemandeEpave::where('statut', 'disponible')->count(),
            'reserves' => DemandeEpave::where('statut', 'reserve')->count(),
            'vendues' => DemandeEpave::where('statut', 'vendu')->count(),
        ];

        $allDemandes = DemandeEpave::with('vendeur')->orderBy('created_at', 'desc')->get();
        $tableStructure = DB::select('DESCRIBE demandes_epaves');

        return view('demandes-epaves.debug', compact('stats', 'allDemandes', 'tableStructure'));
    }

    public function debugCreateTest()
    {
        $user = Auth::user();
        if (empty($user->telephone) || empty($user->ville)) {
            return back()->with('error', 'Complétez votre profil (téléphone et ville requis)');
        }

        $demande = DemandeEpave::create([
            'user_id' => $user->id,
            'type' => 'vehicule',
            'marque' => 'Toyota',
            'modele' => 'Corolla',
            'annee' => 2020,
            'numero_chassis' => 'TEST' . rand(1000, 9999),
            'numero_plaque' => 'AB-' . rand(100, 999) . '-CD',
            'couleur' => 'Blanc',
            'carburant' => 'essence',
            'kilometrage' => 50000,
            'etat' => 'bon',
            'prix_souhaite' => 5000000,
            'description' => 'Véhicule de test créé automatiquement',
            'telephone_contact' => $user->telephone,
            'adresse' => $user->ville,
            'statut' => 'disponible',
            'quantite' => 1,
        ]);

        return redirect()->route('demandes-epaves.debug')
            ->with('success', 'Annonce de test créée! ID: ' . $demande->id);
    }

    public function debugTruncate()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('demandes_epaves')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        return redirect()->route('demandes-epaves.debug')
            ->with('success', 'Toutes les annonces ont été supprimées');
    }
}
