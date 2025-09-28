<?php


// app/Http/Controllers/SearchController.php
namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'all');

        $results = [];

        if ($query) {
            if ($type === 'all' || $type === 'pieces') {
                $results['pieces'] = Piece::where('disponible', true)
                    ->where('nom', 'like', "%{$query}%")
                    ->with(['vehicle.casse'])
                    ->take(10)
                    ->get();
            }

            if ($type === 'all' || $type === 'vehicles') {
                $results['vehicles'] = Vehicle::where('vendu', false)
                    ->where(function($q) use ($query) {
                        $q->where('marque', 'like', "%{$query}%")
                            ->orWhere('modele', 'like', "%{$query}%");
                    })
                    ->with('casse')
                    ->take(10)
                    ->get();
            }

            if ($type === 'all' || $type === 'casses') {
                $results['casses'] = User::where('role', 'casse')
                    ->where('actif', true)
                    ->where(function($q) use ($query) {
                        $q->where('nom_entreprise', 'like', "%{$query}%")
                            ->orWhere('ville', 'like', "%{$query}%");
                    })
                    ->take(10)
                    ->get();
            }
        }

        return view('search.results', compact('results', 'query', 'type'));
    }

    public function autocomplete(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'pieces');

        $results = [];

        if ($query) {
            if ($type === 'pieces') {
                $results = Piece::where('disponible', true)
                    ->where('nom', 'like', "%{$query}%")
                    ->select('nom')
                    ->distinct()
                    ->take(10)
                    ->pluck('nom');
            } elseif ($type === 'vehicles') {
                $marques = Vehicle::where('marque', 'like', "%{$query}%")
                    ->select('marque')
                    ->distinct()
                    ->take(5)
                    ->pluck('marque');

                $modeles = Vehicle::where('modele', 'like', "%{$query}%")
                    ->select('modele')
                    ->distinct()
                    ->take(5)
                    ->pluck('modele');

                $results = $marques->concat($modeles)->unique()->take(10);
            }
        }

        return response()->json($results);
    }
}
