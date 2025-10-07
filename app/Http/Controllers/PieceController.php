<?php
// app/Http/Controllers/PieceController.php

namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\Marque;
use App\Models\Modele;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PieceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isCasse()) {
            // Vue casse : toutes les pièces de l'utilisateur connecté
            $query = Piece::with(['marque', 'modele'])->where('user_id', $user->id);

            if ($request->filled('search')) {
                $query->where('nom', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('etat')) {
                $query->where('etat', $request->etat);
            }

            if ($request->filled('marque_id')) {
                $query->where('marque_id', $request->marque_id);
            }

            if ($request->filled('ville')) {
                $query->where('ville', $request->ville);
            }

            $pieces = $query->latest()->paginate(12);

            // Marques utilisées par l'utilisateur
            $marques = Marque::active()
                ->whereHas('pieces', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->withCount('pieces')
                ->orderBy('nom')
                ->get();

            // Villes des pièces de l'utilisateur
            $villes = Piece::where('user_id', $user->id)
                ->whereNotNull('ville')
                ->distinct()
                ->pluck('ville')
                ->filter()
                ->sort()
                ->values();

            return view('pieces.index', compact('pieces', 'marques', 'villes'));
        } else {
            // Vue client : marketplace
            $query = Piece::with(['marque', 'modele', 'user'])
                ->where('disponible', true);

            if ($request->filled('search')) {
                $query->where('nom', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('marque_id')) {
                $query->where('marque_id', $request->marque_id);
            }

            if ($request->filled('etat')) {
                $query->where('etat', $request->etat);
            }

            if ($request->filled('ville')) {
                $query->where('ville', $request->ville);
            }

            $pieces = $query->latest()->paginate(12);

            // Marques disponibles
            $marques = Marque::active()
                ->whereHas('pieces', function($q) {
                    $q->where('disponible', true);
                })
                ->withCount('pieces')
                ->orderBy('nom')
                ->get();

            // Villes disponibles
            $villes = Piece::where('disponible', true)
                ->whereNotNull('ville')
                ->distinct()
                ->pluck('ville')
                ->filter()
                ->sort()
                ->values();

            return view('pieces.index', compact('pieces', 'marques', 'villes'));
        }
    }

    public function create()
    {
        $this->authorize('create', Piece::class);

        $marques = Marque::active()->orderBy('nom')->get();

        return view('pieces.create', compact('marques'));
    }

    public function store(Request $request)
    {
        $messages = [
            'nom.required' => 'Le nom de la pièce est obligatoire.',
            'marque_id.required' => 'La marque de la pièce est obligatoire.',
            'marque_id.exists' => 'La marque sélectionnée n\'existe pas.',
            'modele_id.required' => 'Le modèle de la pièce est obligatoire.',
            'modele_id.exists' => 'Le modèle sélectionné n\'existe pas.',
            'new_marque.required_without' => 'Veuillez sélectionner ou ajouter une marque.',
            'new_modele.required_without' => 'Veuillez sélectionner ou ajouter un modèle.',
            'description.required' => 'La description est obligatoire.',
            'prix.required' => 'Le prix de la pièce est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre valide.',
            'prix.min' => 'Le prix doit être au moins de 500 FCFA.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité doit être au moins de 1.',
            'etat.required' => "L'état de la pièce est obligatoire.",
            'photos.*.image' => 'Chaque fichier doit être une image valide.',
            'photos.*.max' => 'Chaque image ne doit pas dépasser 2 Mo.',
            'reference_constructeur.required' => 'La référence constructeur est obligatoire.',
            'compatible_avec.required' => 'Le champ "compatible avec" est obligatoire.',
        ];

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'marque_id' => 'required_without:new_marque|nullable|exists:marques,id',
            'new_marque' => 'required_without:marque_id|nullable|string|max:255',
            'modele_id' => 'required_without:new_modele|nullable|exists:modeles,id',
            'new_modele' => 'required_without:modele_id|nullable|string|max:255',
            'description' => 'required|string',
            'prix' => 'required|numeric|min:500',
            'quantite' => 'required|integer|min:1',
            'etat' => 'required|in:neuf,tres_bon,bon,moyen,usage',
            'photos.*' => 'nullable|image|max:2048',
            'reference_constructeur' => 'required|string|max:255',
            'compatible_avec' => 'required|string',
            'disponible' => 'nullable',
        ], $messages);

        // Gestion de la nouvelle marque
        if ($request->filled('new_marque')) {
            $marque = Marque::firstOrCreate(
                ['nom' => trim($request->new_marque)],
                ['is_active' => true]
            );
            $validated['marque_id'] = $marque->id;
        }

        // Gestion du nouveau modèle
        if ($request->filled('new_modele')) {
            $modele = Modele::firstOrCreate(
                [
                    'marque_id' => $validated['marque_id'],
                    'nom' => trim($request->new_modele)
                ],
                ['is_active' => true]
            );
            $validated['modele_id'] = $modele->id;
        }

        // Supprimer les champs temporaires
        unset($validated['new_marque'], $validated['new_modele']);

        // Récupérer automatiquement la ville de l'utilisateur connecté
        $validated['ville'] = Auth::user()->ville;

        // Gestion de la checkbox "disponible"
        $validated['disponible'] = $request->has('disponible');

        // Gestion des photos
        if ($request->hasFile('photos')) {
            $validated['photos'] = array_map(
                fn($file) => $file->store('pieces', 'public'),
                $request->file('photos')
            );
        }

        // Attribution de l'utilisateur connecté
        $validated['user_id'] = Auth::id();
        $validated['disponible'] = true ;

        // Création de la pièce
        Piece::create($validated);

        return redirect()->route('pieces.index')
            ->with('success', 'La pièce a été ajoutée avec succès.');
    }

    public function show(Piece $piece)
    {
        $piece->load(['marque', 'modele', 'user']);

        // Vérifier que la pièce appartient à l'utilisateur si nécessaire
        if (Auth::user()->isCasse() && $piece->user_id !== Auth::id()) {
            abort(403);
        }

        $piecesSimilaires = Piece::with(['marque', 'modele'])
            ->where('nom', 'like', '%' . $piece->nom . '%')
            ->where('id', '!=', $piece->id)
            ->where('disponible', true)
            ->limit(4)
            ->get();

        return view('pieces.show', compact('piece', 'piecesSimilaires'));
    }

    public function edit(Piece $piece)
    {
        $this->authorize('update', $piece);

        $marques = Marque::active()->orderBy('nom')->get();
        $modeles = Modele::active()->where('marque_id', $piece->marque_id)->orderBy('nom')->get();

        return view('pieces.edit', compact('piece', 'marques', 'modeles'));
    }

    public function update(Request $request, Piece $piece)
    {
        $this->authorize('update', $piece);

        $messages = [
            'nom.required' => 'Le nom de la pièce est obligatoire.',
            'marque_id.required' => 'La marque de la pièce est obligatoire.',
            'marque_id.exists' => 'La marque sélectionnée n\'existe pas.',
            'modele_id.required' => 'Le modèle de la pièce est obligatoire.',
            'modele_id.exists' => 'Le modèle sélectionné n\'existe pas.',
            'new_marque.required_without' => 'Veuillez sélectionner ou ajouter une marque.',
            'new_modele.required_without' => 'Veuillez sélectionner ou ajouter un modèle.',
            'description.required' => 'La description est obligatoire.',
            'prix.required' => 'Le prix de la pièce est obligatoire.',
            'prix.numeric' => 'Le prix doit être un nombre valide.',
            'prix.min' => 'Le prix doit être au moins de 500 FCFA.',
            'quantite.required' => 'La quantité est obligatoire.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité doit être au moins de 1.',
            'etat.required' => "L'état de la pièce est obligatoire.",
            'reference_constructeur.required' => 'La référence constructeur est obligatoire.',
            'compatible_avec.required' => 'Le champ "compatible avec" est obligatoire.',
            'photos.*.image' => 'Chaque fichier doit être une image valide.',
            'photos.*.max' => 'Chaque image ne doit pas dépasser 2 Mo.',
        ];

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'marque_id' => 'required_without:new_marque|nullable|exists:marques,id',
            'new_marque' => 'required_without:marque_id|nullable|string|max:255',
            'modele_id' => 'required_without:new_modele|nullable|exists:modeles,id',
            'new_modele' => 'required_without:modele_id|nullable|string|max:255',
            'description' => 'required|string',
            'prix' => 'required|numeric|min:500',
            'quantite' => 'required|integer|min:1',
            'etat' => 'required|in:neuf,tres_bon,bon,moyen,usage',
            'photos.*' => 'nullable|image|max:2048',
            'reference_constructeur' => 'required|string|max:255',
            'compatible_avec' => 'required|string',
            'disponible' => 'nullable',
        ], $messages);

        // Gestion de la nouvelle marque
        if ($request->filled('new_marque')) {
            $marque = Marque::firstOrCreate(
                ['nom' => trim($request->new_marque)],
                ['is_active' => true]
            );
            $validated['marque_id'] = $marque->id;
        }

        // Gestion du nouveau modèle
        if ($request->filled('new_modele')) {
            $modele = Modele::firstOrCreate(
                [
                    'marque_id' => $validated['marque_id'],
                    'nom' => trim($request->new_modele)
                ],
                ['is_active' => true]
            );
            $validated['modele_id'] = $modele->id;
        }

        // Supprimer les champs temporaires
        unset($validated['new_marque'], $validated['new_modele']);

        // Mettre à jour la ville depuis l'utilisateur actuel
        $validated['ville'] = Auth::user()->ville;

        // Gestion des photos
        if ($request->hasFile('photos')) {
            if ($piece->photos) {
                foreach ($piece->photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            $validated['photos'] = array_map(
                fn($file) => $file->store('pieces', 'public'),
                $request->file('photos')
            );
        }

        // Checkbox disponible
        $validated['disponible'] = $request->has('disponible');

        $piece->update($validated);

        return redirect()->route('pieces.show', $piece)
            ->with('success', 'La pièce a été mise à jour avec succès.');
    }

    public function destroy(Piece $piece)
    {
        $this->authorize('delete', $piece);

        if ($piece->photos) {
            foreach ($piece->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $piece->delete();

        return redirect()->route('pieces.index')
            ->with('success', 'Pièce supprimée avec succès.');
    }

    // API pour récupérer les modèles d'une marque
    public function getModeles($marqueId)
    {
        $modeles = Modele::active()
            ->where('marque_id', $marqueId)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        return response()->json($modeles);
    }
}
