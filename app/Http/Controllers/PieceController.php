<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PieceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isCasse()) {
            // Vue casse : afficher toutes ses pièces avec les véhicules
            $query = Piece::whereHas('vehicle', function($q) use($user) {
                $q->where('casse_id', $user->id);
            })->with(['vehicle']);

            if ($request->filled('search')) {
                $query->where('nom', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('etat')) {
                $query->where('etat', $request->etat);
            }

            $pieces = $query->latest()->paginate(12);
            $marques = Vehicle::where('casse_id', $user->id)->distinct()->pluck('marque');

            return view('pieces.index', compact('pieces', 'marques'));
        } else {
            // Vue client : marketplace
            $query = Piece::with(['vehicle.casse'])
                ->where('disponible', true);

            if ($request->filled('search')) {
                $query->where('nom', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('marque')) {
                $query->whereHas('vehicle', function($q) use($request) {
                    $q->where('marque', $request->marque);
                });
            }

            if ($request->filled('prix_max')) {
                $query->where('prix', '<=', $request->prix_max);
            }

            if ($request->filled('etat')) {
                $query->where('etat', $request->etat);
            }

            // Géolocalisation
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $lat = $request->latitude;
                $lng = $request->longitude;
                $radius = $request->radius ?? 50;

                $query->whereHas('vehicle.casse', function($q) use($lat, $lng, $radius) {
                    $q->whereRaw("
                        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) *
                        cos(radians(longitude) - radians(?)) + sin(radians(?)) *
                        sin(radians(latitude)))) <= ?
                    ", [$lat, $lng, $lat, $radius]);
                });
            }

            $pieces = $query->latest()->paginate(12);
            $marques = Vehicle::distinct()->pluck('marque');
            $modeles = Vehicle::distinct()->pluck('modele');
            $annees = Vehicle::distinct()->orderBy('annee', 'desc')->pluck('annee');

            return view('pieces.index', compact('pieces', 'marques', 'modeles', 'annees'));
        }
    }

    public function create(Request $request)
    {
        $this->authorize('create', Piece::class);

        // Récupérer les véhicules de la casse
        $vehicles = Auth::user()->vehicles;

        return view('pieces.create', compact('vehicles'));
    }

    private function generateUniqueNumeroChassis(): string
    {
        $lastVehicle = Vehicle::orderBy('id', 'desc')->first();

        if (!$lastVehicle) {
            return 'CH-00001';
        }

        $lastNumber = (int) str_replace('CH-', '', $lastVehicle->numero_chassis);
        $newNumber = $lastNumber + 1;

        return 'CH-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Piece::class);

        // Validation de base
        $rules = [
            'vehicle_option' => 'required|in:existing,new',
            'nom' => 'required|string|max:255',
            'description' => 'required|string',
            'prix' => 'required|numeric|min:0',
            'quantite' => 'required|integer|min:1',
            'etat_piece' => 'required|in:neuf,tres_bon,bon,moyen,usage',
            'photos.*' => 'nullable|image|max:2048',
            'reference_constructeur' => 'nullable|string',
            'compatible_avec' => 'nullable|string',
            'disponible' => 'boolean'
        ];


        // Validation conditionnelle selon l'option choisie
        if ($request->vehicle_option === 'existing') {
            $rules['vehicle_id'] = 'required|exists:vehicles,id';
        } else {
            $rules['marque'] = 'required|string|max:255';
            $rules['modele'] = 'required|string|max:255';
            $rules['annee'] = 'required|integer|min:1900|max:' . (date('Y') + 1);
            $rules['numero_plaque'] = 'required|string|unique:vehicles';
            $rules['couleur'] = 'required|string';
            $rules['carburant'] = 'required|in:essence,diesel,hybride,electrique';
            $rules['transmission'] = 'required|in:manuelle,automatique';
            $rules['kilometrage'] = 'required|integer|min:0';
            $rules['etat'] = 'required|in:bon,moyen,mauvais,epave';
            $rules['prix_epave'] = 'required|numeric|min:0';
            $rules['vehicle_description'] = 'nullable|string';
            $rules['photo_principale'] = 'nullable|image|max:2048';
        }

        $validated = $request->validate($rules);


        DB::beginTransaction();
        try {
            $vehicleId = null;

            if ($request->vehicle_option === 'existing') {
                // Vérifier que le véhicule appartient à la casse
                $vehicle = Vehicle::where('id', $validated['vehicle_id'])
                    ->where('casse_id', Auth::id())
                    ->firstOrFail();

                $vehicleId = $vehicle->id;
            } else {
                // Créer un nouveau véhicule
                $vehicleData = [
                    'casse_id' => Auth::id(),
                    'marque' => $validated['marque'],
                    'modele' => $validated['modele'],
                    'annee' => $validated['annee'],
                    'numero_chassis' => $this->generateUniqueNumeroChassis(),
                    'numero_plaque' => $validated['numero_plaque'],
                    'couleur' => $validated['couleur'],
                    'carburant' => $validated['carburant'],
                    'transmission' => $validated['transmission'],
                    'kilometrage' => $validated['kilometrage'],
                    'etat' => $validated['etat'],
                    'prix_epave' => $validated['prix_epave'],
                    'date_arrivee' => now()->toDateString(),
                    'description' => $validated['vehicle_description'] ?? null
                ];

                // Upload photo principale du véhicule
                if ($request->hasFile('photo_principale')) {
                    $vehicleData['photo_principale'] = $request->file('photo_principale')
                        ->store('vehicles', 'public');
                }

                $vehicle = Vehicle::create($vehicleData);
                $vehicleId = $vehicle->id;
            }

            // Créer la pièce
            $pieceData = [
                'vehicle_id' => $vehicleId,
                'nom' => $validated['nom'],
                'description' => $validated['description'],
                'prix' => $validated['prix'],
                'quantite' => $validated['quantite'],
                'etat' => $validated['etat_piece'],
                'reference_constructeur' => $validated['reference_constructeur'] ?? null,
                'compatible_avec' => $validated['compatible_avec'] ?? null,
                'disponible' => $validated['disponible'],
            ];

            // Upload photos de la pièce
            if ($request->hasFile('photos')) {
                $photos = [];
                foreach ($request->file('photos') as $photo) {
                    $photos[] = $photo->store('pieces', 'public');
                }
                $pieceData['photos'] = $photos;
            }

            $piece = Piece::create($pieceData);

            DB::commit();

            return redirect()->route('pieces.show', $piece)
                ->with('success', 'Pièce ajoutée avec succès' .
                    ($request->vehicle_option === 'new' ? ' (nouveau véhicule créé)' : '') . '.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    public function show(Piece $piece)
    {
        $piece->load(['vehicle.casse']);

        $piecesSimilaires = Piece::where('nom', 'like', '%' . $piece->nom . '%')
            ->where('id', '!=', $piece->id)
            ->where('disponible', true)
            ->with(['vehicle.casse'])
            ->limit(4)
            ->get();

        $autresPiecesVehicule = Piece::where('vehicle_id', $piece->vehicle_id)
            ->where('id', '!=', $piece->id)
            ->where('disponible', true)
            ->get();

        return view('pieces.show', compact('piece', 'piecesSimilaires', 'autresPiecesVehicule'));
    }

    public function edit(Piece $piece)
    {
        $this->authorize('update', $piece);

        // On ne permet pas de changer le véhicule en édition
        $vehicle = $piece->vehicle;

        return view('pieces.edit', compact('piece', 'vehicle'));
    }

    public function update(Request $request, Piece $piece)
    {
        $this->authorize('update', $piece);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'required|string',
            'prix' => 'required|numeric|min:0',
            'quantite' => 'required|integer|min:0',
            'etat' => 'required|in:neuf,tres_bon,bon,moyen,usage',
            'photos.*' => 'nullable|image|max:2048',
            'reference_constructeur' => 'nullable|string',
            'compatible_avec' => 'nullable|string',
            'disponible' => 'boolean'
        ]);

        // Upload nouvelles photos
        if ($request->hasFile('photos')) {
            // Supprimer les anciennes photos
            if ($piece->photos) {
                foreach ($piece->photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('pieces', 'public');
            }
            $validated['photos'] = $photos;
        }

        $validated['disponible'] = $request->has('disponible');

        $piece->update($validated);

        return redirect()->route('pieces.show', $piece)
            ->with('success', 'Pièce mise à jour avec succès.');
    }

    public function destroy(Piece $piece)
    {
        $this->authorize('delete', $piece);

        // Supprimer les photos
        if ($piece->photos) {
            foreach ($piece->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $piece->delete();

        return redirect()->route('pieces.index')
            ->with('success', 'Pièce supprimée avec succès.');
    }
}
