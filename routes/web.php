<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DashboardController,
    PanierVehiculeController,
    PieceController,
    PanierController,
    CommandeController,
    DemandeEpaveController,
    SearchController,
    NotificationController,
    ProfileController,
    StockController,
    VehicleController,
    VenteEpaveController};
use App\Http\Controllers\auth\AdminController;
use App\Models\Commande;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ========================
// ROUTES PUBLIQUES
// ========================

// Page d'accueil
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Recherche publique
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

// Authentification
require __DIR__ . '/auth.php';

// Page d'attente d'approbation
Route::get('/auth/pending-approval', function() {
    return view('auth.pending-approval');
})->name('auth.pending-approval');

// ========================
// ROUTES AUTHENTIFIÉES
// ========================
Route::middleware(['auth', 'verified', 'approved'])->group(function () {

    Route::get('gestion/epaves', [VenteEpaveController::class, 'index'])->name('gestion.epaves.index');
    Route::get('gestion/epaves/{demande}', [VenteEpaveController::class, 'show'])->name('gestion.epaves.show');


    Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');


    Route::get('gestion/commandes', [CommandeController::class, 'index'])->name('gestion.commandes.index');
    Route::get('gestion/commandes/{commande}', [CommandeController::class, 'show'])->name('gestion.commandes.show');
    Route::get('gestion/commandes/{commande}/statut/form', [CommandeController::class, 'editStatut'])->name('gestion.commandes.edit-statut');
    Route::put('gestion/commandes/{commande}/statut', [CommandeController::class, 'updateStatut'])->name('gestion.commandes.update-statut');

    // ÉPAVES
    Route::get('gestion/epaves', [VenteEpaveController::class, 'index'])->name('gestion.epaves.index');
    Route::get('gestion/epaves/{demande}', [VenteEpaveController::class, 'show'])->name('gestion.epaves.show');



    // PANIER
    Route::prefix('panier')->name('panier.')->group(function () {
        Route::get('/', [PanierController::class, 'index'])->name('index');
        Route::post('/add/{piece}', [PanierController::class, 'add'])->name('add');
        Route::put('/items/{item}', [PanierController::class, 'update'])->name('update');
        Route::delete('/items/{item}', [PanierController::class, 'remove'])->name('remove');
        Route::delete('/clear', [PanierController::class, 'clear'])->name('clear');
    });

    // COMMANDES CLIENT
    Route::prefix('commandes')->name('commandes.')->group(function () {
        Route::get('/', [CommandeController::class, 'index'])->name('index');
        Route::get('/create', [CommandeController::class, 'create'])->name('create');
        Route::post('/', [CommandeController::class, 'store'])->name('store');
        Route::get('/{commande}', [CommandeController::class, 'show'])->name('show');
        Route::delete('/{commande}/annuler', [CommandeController::class, 'annuler'])->name('annuler');
        Route::put('/{commande}/update-adresse', [CommandeController::class, 'updateAdresse'])->name('update-adresse');
    });

    // Dashboard général
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ========================
    // PROFIL UTILISATEUR
    // ========================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/update', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ========================
    // NOTIFICATIONS
    // ========================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::put('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    });

    // ========================
    // PIÈCES DÉTACHÉES
    // ========================
    Route::resource('pieces', PieceController::class);

    // ========================
    // DEMANDES ÉPAVES / VÉHICULES
    // ========================
    Route::resource('demandes-epaves', DemandeEpaveController::class)
        ->parameters(['demandes-epaves' => 'demandeEpave'])
        ->names('demandes-epaves');

    // Toggle disponibilité
    Route::patch('demandes-epaves/{demandeEpave}/toggle-disponibilite',
        [DemandeEpaveController::class, 'toggleDisponibilite'])
        ->name('demandes-epaves.toggle-disponibilite');

    // ========================
    // PANIER VÉHICULES
    // ========================
    Route::post('panier-vehicule/add/{demandeEpave}',
        [PanierVehiculeController::class, 'add'])
        ->name('panier-vehicule.add');

    Route::delete('panier-vehicule/remove/{item}',
        [PanierVehiculeController::class, 'remove'])
        ->name('panier-vehicule.remove');

    // ========================
    // ROUTES CLIENT
    // ========================
    Route::middleware(['role:client'])->group(function () {


        Route::get('/mes-annonces', [StockController::class, 'index'])->name('client.annonces');

    });

    // ========================
    // ROUTES CASSE
    // ========================
    Route::middleware(['role:casse'])->prefix('gestion')->name('gestion.')->group(function () {

        // CRUD VÉHICULES
        Route::resource('vehicles', VehicleController::class);

       // Route::get('/stocks', [StockController::class, 'index'])->name('stocks');

        Route::get('/stocks', [StockController::class, 'index'])->name('stocks');


//        // COMMANDES DE LA CASSE
//        Route::get('/commandes', [CommandeController::class, 'index'])->name('commandes.index');
//        Route::get('/commandes/{commande}', [CommandeController::class, 'show'])->name('commandes.show');
//        Route::get('/commandes/{commande}/statut/form', [CommandeController::class, 'editStatut'])->name('commandes.edit-statut');
//        Route::put('/commandes/{commande}/statut', [CommandeController::class, 'updateStatut'])->name('commandes.update-statut');

        // ÉPAVES
//        Route::get('/epaves', [VenteEpaveController::class, 'index'])->name('epaves.index');
//        Route::get('/epaves/{demande}', [VenteEpaveController::class, 'show'])->name('epaves.show');
        // STOCKS
//        Route::get('/stocks', function () {
//            $user = Auth::user();
//
//            // Toutes les pièces de la casse (utilisateur connecté)
//            $pieces = $user->pieces()->get();
//
//            // Statistiques
//            $totalPieces = $pieces->count();
//            $totalStock = $pieces->sum('quantite');
//            $piecesDisponibles = $pieces->where('disponible', true)->count();
//
//            // Alertes de stock
//            $stockFaible = $pieces->where('quantite', '>', 0)->where('quantite', '<=', 3);
//            $stockVide = $pieces->where('quantite', 0);
//
//            return view('gestion.stocks', compact(
//                'pieces',
//                'totalPieces',
//                'totalStock',
//                'piecesDisponibles',
//                'stockFaible',
//                'stockVide'
//            ));
//        })->name('stocks');

        // ÉPAVES
//        Route::get('/epaves', [VenteEpaveController::class, 'index'])->name('epaves.index');
//        Route::get('/epaves/{demande}', [VenteEpaveController::class, 'show'])->name('epaves.show');
    });

    // ========================
    // ROUTES ADMIN
    // ========================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {

        // DASHBOARD ADMIN
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // GESTION DES UTILISATEURS
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::delete('/users/{id}', [AdminController::class, 'destroy'])->name('users.destroy');

        // STATISTIQUES
        Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');

        // PARAMÈTRES
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');

        // GESTION DES CASSES EN ATTENTE
        Route::get('/casses/pending', [AdminController::class, 'pendingCasses'])->name('casses.pending');
        Route::post('/casses/{id}/approve', [AdminController::class, 'approveCasse'])->name('casses.approve');
        Route::post('/casses/{id}/reject', [AdminController::class, 'rejectCasse'])->name('casses.reject');
    });

    // ========================
    // ROUTES DEBUG (à supprimer en production)
    // ========================
    if (config('app.debug')) {
        Route::prefix('debug')->name('demandes-epaves.debug.')->group(function () {
            Route::get('/demandes-epaves', [DemandeEpaveController::class, 'debug'])->name('index');
            Route::post('/demandes-epaves/create-test', [DemandeEpaveController::class, 'debugCreateTest'])->name('create-test');
            Route::post('/demandes-epaves/make-available', [DemandeEpaveController::class, 'debugMakeAvailable'])->name('make-available');
            Route::post('/demandes-epaves/truncate', [DemandeEpaveController::class, 'debugTruncate'])->name('truncate');
        });
    }
});

// ========================
// API ROUTES
// ========================
Route::middleware('auth')->group(function() {

    // MODÈLES PAR MARQUE (PIÈCES)
    Route::get('/api/marques/{marque}/modeles', [PieceController::class, 'getModeles'])
        ->name('api.marques.modeles');

    // MODÈLES PAR MARQUE (ÉPAVES/VÉHICULES)
    Route::get('/api/marques/{marque}/modeles-epave', [DemandeEpaveController::class, 'getModelesByMarque'])
        ->name('api.marques.modeles-epave');

    // AUTOCOMPLÉTION NOMS DE PIÈCES
    Route::get('/api/pieces/autocomplete-noms', [PieceController::class, 'autocompleteNomPieces'])
        ->name('api.pieces.autocomplete-noms');

    // AUTOCOMPLÉTION MARQUES
    Route::get('/api/marques/autocomplete', [PieceController::class, 'autocompleteMarques'])
        ->name('api.marques.autocomplete');
});

// ========================
// ROUTES LEGACY/COMPATIBILITÉ
// ========================

// Route publique Commandes Casse (éviter conflit noms)
Route::middleware(['auth', 'role:casse'])->get('/commandes-casse', function () {
    $commandes = Commande::whereHas('items.piece.vehicle', function ($query) {
        $query->where('casse_id', auth()->id());
    })->with(['client', 'items.piece'])->latest()->paginate(10);

    return view('casse.commandes.index', compact('commandes'));
})->name('casse.commandes.legacy');
