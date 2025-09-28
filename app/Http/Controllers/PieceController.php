<?php

// app/Http/Controllers/PieceController.php
namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PieceController extends Controller
{
    public function index(Request $request)
    {
        $query = Piece::with(['vehicle.casse'])
            ->where('disponible', true);

        // Filtres de recherche
        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('marque')) {
            $query->whereHas('vehicle', function($q) use($request) {
                $q->where('marque', $request->marque);
            });
        }

        if ($request->filled('modele')) {
            $query->whereHas('vehicle', function($q) use($request) {
                $q->where('modele', $request->modele);
            });
        }

        if ($request->filled('annee')) {
            $query->whereHas('vehicle', function($q) use($request) {
                $q->where('annee', $request->annee);
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

        // Pour les filtres
        $marques = Vehicle::distinct()->pluck('marque');
        $modeles = Vehicle::distinct()->pluck('modele');
        $annees = Vehicle::distinct()->orderBy('annee', 'desc')->pluck('annee');

        return view('pieces.index', compact('pieces', 'marques', 'modeles', 'annees'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Piece::class);

        $vehicles = Auth::user()->vehicles;
        $selectedVehicle = null;

        if ($request->filled('vehicle_id')) {
            $selectedVehicle = Vehicle::where('id', $request->vehicle_id)
                ->where('casse_id', Auth::id())
                ->first();
        }

        return view('pieces.create', compact('vehicles', 'selectedVehicle'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Piece::class);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'nom' => 'required|string|max:255',
            'description' => 'required|string',
            'prix' => 'required|numeric|min:0',
            'quantite' => 'required|integer|min:1',
            'etat' => 'required|in:neuf,tres_bon,bon,moyen,usage',
            'photos.*' => 'nullable|image|max:2048',
            'reference_constructeur' => 'nullable|string',
            'compatible_avec' => 'nullable|string',
        ]);

        // Vérifier que le véhicule appartient à la casse connectée
        $vehicle = Vehicle::where('id', $validated['vehicle_id'])
            ->where('casse_id', Auth::id())
            ->firstOrFail();

        // Upload photos
        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('pieces', 'public');
            }
            $validated['photos'] = $photos;
        }

        $piece = Piece::create($validated);

        return redirect()->route('pieces.show', $piece)
            ->with('success', 'Pièce ajoutée avec succès.');
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
        $vehicles = Auth::user()->vehicles;
        return view('pieces.edit', compact('piece', 'vehicles'));
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

        return redirect()->route('vehicles.show', $piece->vehicle)
            ->with('success', 'Pièce supprimée avec succès.');
    }
}
