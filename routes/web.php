<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    PieceController,
    PanierController,
    CommandeController,
    DemandeEpaveController,
    SearchController,
    NotificationController,
    ProfileController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Routes publiques
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');

// Routes d'authentification
require __DIR__.'/auth.php';

// Routes protégées
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil utilisateur
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::put('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    });

    // Pièces détachées - accessible à tous
    Route::resource('pieces', PieceController::class);

    // Demandes d'épaves - accessible à tous
    Route::resource('demandes-epaves', DemandeEpaveController::class)
        ->parameters(['demandes-epaves' => 'demandeEpave'])
        ->names('demandes-epaves');

    Route::post('/demandes_epaves/{demandeEpave}/offre', [DemandeEpaveController::class, 'faireOffre'])
        ->name('demandes-epaves.faire-offre');

    Route::delete('/demandes_epaves/{demandeEpave}/offre/{offre}', [DemandeEpaveController::class, 'retirerOffre'])
        ->name('demandes-epaves.retirer-offre');

    Route::post('/demandes-epaves/{demandeEpave}/accepter-offre/{offre}', [DemandeEpaveController::class, 'accepterOffre'])
        ->name('demandes-epaves.accepter-offre');

    // Routes spécifiques aux clients
    Route::middleware(['role:client'])->group(function () {

        // Panier
        Route::prefix('panier')->name('panier.')->group(function () {
            Route::get('/', [PanierController::class, 'index'])->name('index');
            Route::post('/add/{piece}', [PanierController::class, 'add'])->name('add');
            Route::put('/items/{item}', [PanierController::class, 'update'])->name('update');
            Route::delete('/items/{item}', [PanierController::class, 'remove'])->name('remove');
            Route::delete('/clear', [PanierController::class, 'clear'])->name('clear');
        });

        // Commandes
        Route::resource('commandes', CommandeController::class)->except(['edit', 'update', 'destroy']);
        Route::delete('/commandes/{commande}/annuler', [CommandeController::class, 'annuler'])
            ->name('commandes.annuler');
    });

    // Routes spécifiques aux casses
    Route::middleware(['role:casse'])->group(function () {

        // Gestion des stocks et commandes
        Route::prefix('gestion')->name('gestion.')->group(function () {
            Route::get('/stocks', function () {
                $vehicles = auth()->user()->vehicles()->with('pieces')->get();
                $totalPieces = auth()->user()->pieces()->count();
                $totalStock = auth()->user()->pieces()->sum('quantite');
                $piecesDisponibles = auth()->user()->pieces()->where('disponible', true)->count();

                return view('gestion.stocks', compact('vehicles', 'totalPieces', 'totalStock', 'piecesDisponibles'));
            })->name('stocks');

            Route::get('/commandes', function () {
                $commandes = \App\Models\Commande::whereHas('items.piece.vehicle', function($query) {
                    $query->where('casse_id', auth()->id());
                })->with(['user', 'items.piece'])->latest()->paginate(10);

                return view('gestion.commandes', compact('commandes'));
            })->name('commandes');
        });

        // Mise à jour statut commande
        Route::put('/commandes/{commande}/statut', [CommandeController::class, 'updateStatut'])
            ->name('commandes.update-statut');
    });

    // Routes administrateur
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', function () {
            $users = \App\Models\User::with(['vehicles', 'commandes'])->paginate(20);
            return view('admin.users.index', compact('users'));
        })->name('users.index');

        Route::get('/statistics', function () {
            return view('admin.statistics');
        })->name('statistics');

        Route::get('/settings', function () {
            return view('admin.settings');
        })->name('settings');
    });
});
