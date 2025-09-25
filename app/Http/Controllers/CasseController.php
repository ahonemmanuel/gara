<?php
// app/Http/Controllers/CasseController.php
namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Notification;
use App\Models\Piece;
use App\Models\User;
use App\Models\Vehicule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CasseController extends Controller
{
    public function dashboard()
    {
        $casseId = auth()->id();

        $stats = [
            'vehicules' => Vehicule::where('casse_id', $casseId)->count(),
            'pieces' => Piece::where('casse_id', $casseId)->count(),
            'commandes' => Commande::where('casse_id', $casseId)->count(),
            'revenus' => Commande::where('casse_id', $casseId)
                ->where('statut', 'livree')
                ->sum('total')
        ];

        $commandesRecentes = Commande::with('client')
            ->where('casse_id', $casseId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $commandesEnAttente = Commande::where('casse_id', $casseId)
            ->where('statut', 'en_attente')
            ->count();

        $demandesEpaves = Commande::where('casse_id', $casseId)
            ->where('statut', 'en_attente')
            ->count();


        $notificationsNonLues = Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();


        return view('casse.dashboard', compact('stats', 'commandesRecentes','commandesEnAttente','demandesEpaves','notificationsNonLues'));
    }

    public function vehicules()
    {
        $vehicules = Vehicule::where('casse_id', auth()->id())->get();
        return view('casse.vehicules.index', compact('vehicules'));
    }

    public function createVehicule()
    {
        return view('casse.vehicules.create');
    }

    public function storeVehicule(Request $request)
    {
        $request->validate([
            'marque' => 'required|string|max:255',
            'modele' => 'required|string|max:255',
            'annee' => 'required|integer|min:1900|max:' . date('Y'),
            'immatriculation' => 'required|string|unique:vehicules',
            'type_vehicule' => 'required|string',
            'date_arrivee' => 'required|date',
            'etat' => 'required|in:excellent,bon,moyen,mauvais',
            'description' => 'nullable|string'
        ]);

        Vehicule::create([
            'casse_id' => auth()->id(),
            ...$request->all()
        ]);

        return redirect()->route('casse.vehicules')->with('success', 'Véhicule ajouté avec succès.');
    }

    public function stock()
    {
        $pieces = Piece::with('vehicule')
            ->where('casse_id', auth()->id())
            ->get();
        return view('casse.stock.index', compact('pieces'));
    }

    public function createPiece()
    {
        $vehicules = Vehicule::where('casse_id', auth()->id())->get();
        return view('casse.stock.create', compact('vehicules'));
    }

    public function storePiece(Request $request)
    {
        $request->validate([
            'vehicule_id' => 'required|exists:vehicules,id',
            'nom' => 'required|string|max:255',
            'reference' => 'required|string|unique:pieces',
            'categorie' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
            'quantite' => 'required|integer|min:0',
            'etat' => 'required|in:neuf,occasion,reconditionne',
            'description' => 'nullable|string'
        ]);

        Piece::create([
            'casse_id' => auth()->id(),
            ...$request->all()
        ]);

        return redirect()->route('casse.stock')->with('success', 'Pièce ajoutée avec succès.');
    }

    public function editPiece(Piece $piece)
    {
        if ($piece->casse_id !== auth()->id()) {
            abort(403);
        }

        $vehicules = Vehicule::where('casse_id', auth()->id())->get();
        return view('casse.stock.edit', compact('piece', 'vehicules'));
    }

    public function updatePiece(Request $request, Piece $piece)
    {
        if ($piece->casse_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'vehicule_id' => 'required|exists:vehicules,id',
            'nom' => 'required|string|max:255',
            'reference' => 'required|string|unique:pieces,reference,' . $piece->id,
            'categorie' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0',
            'quantite' => 'required|integer|min:0',
            'etat' => 'required|in:neuf,occasion,reconditionne',
            'description' => 'nullable|string'
        ]);

        $piece->update($request->all());

        return redirect()->route('casse.stock')->with('success', 'Pièce modifiée avec succès.');
    }

    public function destroyPiece(Piece $piece)
    {
        if ($piece->casse_id !== auth()->id()) {
            abort(403);
        }

        $piece->delete();
        return redirect()->route('casse.stock')->with('success', 'Pièce supprimée avec succès.');
    }

    public function commandes()
    {
        $commandes = Commande::with(['client', 'pieces'])
            ->where('casse_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('casse.commandes.index', compact('commandes'));
    }

    public function updateCommandeStatut(Request $request, Commande $commande)
    {
        if ($commande->casse_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'statut' => 'required|in:en_attente,confirmee,preparation,expediee,livree,annulee'
        ]);

        $commande->update(['statut' => $request->statut]);

        return redirect()->back()->with('success', 'Statut de la commande mis à jour.');
    }

    public function profile()
    {
        $casse = auth()->user();
        return view('casse.profile', compact('casse'));
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
            'code_postal' => 'nullable|string|max:10',
            'siret' => 'nullable|string|max:14',
            'description' => 'nullable|string'
        ]);

        $user->update($request->all());

        return redirect()->back()->with('success', 'Profil mis à jour avec succès.');
    }
}
