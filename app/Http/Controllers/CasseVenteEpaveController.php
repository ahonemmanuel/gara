<?php
// app/Http/Controllers/CasseVenteEpaveController.php
namespace App\Http\Controllers;

use App\Models\VenteEpave;
use App\Models\Notification;
use Illuminate\Http\Request;

class CasseVenteEpaveController extends Controller
{

    public function evaluerForm(VenteEpave $venteEpave)
    {
        return view('casse.ventes-epaves.evaluer', compact('venteEpave'));
    }

    public function index()
    {
        $demandes = VenteEpave::with('client')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('casse.ventes-epaves.index', compact('demandes'));
    }

    public function show(VenteEpave $venteEpave)
    {
        return view('casse.ventes-epaves.show', compact('venteEpave'));
    }

    public function evaluer(Request $request, VenteEpave $venteEpave)
    {
        $request->validate([
            'notes_evaluation' => 'required|string',
            'prix_propose' => 'required|numeric|min:0',
            'statut' => 'required|in:acceptee,refusee'
        ]);

        $venteEpave->update([
            'notes_evaluation' => $request->notes_evaluation,
            'prix_propose' => $request->prix_propose,
            'statut' => $request->statut,
            'casse_id' => auth()->id()
        ]);

        // Notification au client
        Notification::create([
            'user_id' => $venteEpave->client_id,
            'type' => 'evaluation_epave',
            'title' => 'Évaluation de votre véhicule',
            'message' => 'Votre véhicule a été évalué. Prix proposé: ' . $request->prix_propose . ' €',
            'data' => ['vente_epave_id' => $venteEpave->id]
        ]);

        return redirect()->route('casse.ventes-epaves')->with('success', 'Évaluation enregistrée avec succès.');
    }

    public function updateStatut(Request $request, VenteEpave $venteEpave)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,en_cours,evaluee,acceptee,refusee'
        ]);

        $venteEpave->update(['statut' => $request->statut]);

        return redirect()->back()->with('success', 'Statut mis à jour.');
    }
}
