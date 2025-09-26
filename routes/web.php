<?php

use App\Http\Controllers\CasseNotificationController;
use App\Http\Controllers\CasseVenteEpaveController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CasseController;
use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    return match($user->role->value) {
        'casse' => redirect('/casse/dashboard'),
        'client' => redirect('/client/dashboard'),
        'admin' => redirect('/admin/dashboard'),
        default => view('dashboard')
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes pour les casses automobiles
// routes/web.php (ajout aux routes existantes)
Route::prefix('casse')->middleware(['auth', 'verified', 'casse'])->group(function () {
    // Routes existantes...
    Route::get('/dashboard', [CasseController::class, 'dashboard'])->name('casse.dashboard');
    Route::get('/vehicules', [CasseController::class, 'vehicules'])->name('casse.vehicules');
    Route::get('/stock', [CasseController::class, 'stock'])->name('casse.stock');
    Route::get('/commandes', [CasseController::class, 'commandes'])->name('casse.commandes');

    // Nouvelles routes
    Route::get('/vehicules/create', [CasseController::class, 'createVehicule'])->name('casse.vehicules.create');
    Route::post('/vehicules', [CasseController::class, 'storeVehicule'])->name('casse.vehicules.store');

    Route::get('/stock/create', [CasseController::class, 'createPiece'])->name('casse.stock.create');
    Route::post('/stock', [CasseController::class, 'storePiece'])->name('casse.stock.store');
    Route::get('/stock/{piece}/edit', [CasseController::class, 'editPiece'])->name('casse.stock.edit');
    Route::put('/stock/{piece}', [CasseController::class, 'updatePiece'])->name('casse.stock.update');
    Route::delete('/stock/{piece}', [CasseController::class, 'destroyPiece'])->name('casse.stock.destroy');

    Route::put('/commandes/{commande}', [CasseController::class, 'updateCommandeStatut'])->name('casse.commandes.update');


    Route::get('/ventes-epaves', [CasseVenteEpaveController::class, 'index'])->name('casse.ventes-epaves');
    Route::get('/ventes-epaves/{venteEpave}', [CasseVenteEpaveController::class, 'show'])->name('casse.ventes-epaves.show');
    Route::post('/ventes-epaves/{venteEpave}/evaluer', [CasseVenteEpaveController::class, 'evaluer'])->name('casse.ventes-epaves.evaluer');
    Route::put('/ventes-epaves/{venteEpave}/statut', [CasseVenteEpaveController::class, 'updateStatut'])->name('casse.ventes-epaves.statut');


// routes/web.php (ajouter cette route)
    Route::get('/ventes-epaves/{venteEpave}/evaluer', [CasseVenteEpaveController::class, 'evaluerForm'])->name('casse.ventes-epaves.evaluer-form');
    // Routes pour les notifications
    Route::get('/notifications', [CasseNotificationController::class, 'index'])->name('casse.notifications');
    Route::get('/notifications/{notification}', [CasseNotificationController::class, 'show'])->name('casse.notifications.show');
    Route::post('/notifications/mark-all-read', [CasseNotificationController::class, 'markAllAsRead'])->name('casse.notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [CasseNotificationController::class, 'destroy'])->name('casse.notifications.destroy');

    Route::get('/profile', [CasseController::class, 'profile'])->name('casse.profile');
    Route::put('/profile', [CasseController::class, 'updateProfile'])->name('casse.profile.update');
});





// Routes pour les clients
Route::prefix('client')->middleware(['auth', 'verified', 'client'])->group(function () {
    // Routes existantes...
    Route::get('/dashboard', [ClientController::class, 'dashboard'])->name('client.dashboard');
    Route::get('/recherche-pieces', [ClientController::class, 'recherchePieces'])->name('client.recherche-pieces');
    Route::get('/panier', [ClientController::class, 'panier'])->name('client.panier');
    Route::get('/vente-epaves', [ClientController::class, 'venteEpaves'])->name('client.vente-epaves');

    Route::get('/pieces/{piece}', [ClientController::class, 'detailPiece'])->name('client.detail-piece');

    // Nouvelles routes
    Route::post('/panier/ajouter/{piece}', [ClientController::class, 'ajouterAuPanier'])->name('client.panier.ajouter');
    Route::post('/panier/retirer/{panier}', [ClientController::class, 'retirerDuPanier'])->name('client.panier.retirer');
    Route::post('/panier/update/{panier}', [ClientController::class, 'updateQuantitePanier'])->name('client.panier.update');
    Route::post('/commander', [ClientController::class, 'passerCommande'])->name('client.passer-commande');


    Route::get('/commandes', [ClientController::class, 'commandes'])->name('client.commandes');
    Route::get('/commandes/{commande}', [ClientController::class, 'detailCommande'])->name('client.detail-commande');

    Route::get('/favoris', [ClientController::class, 'favoris'])->name('client.favoris');
    Route::post('/favoris/ajouter/{piece}', [ClientController::class, 'ajouterAuxFavoris'])->name('client.favoris.ajouter');
    Route::post('/favoris/retirer/{piece}', [ClientController::class, 'retirerDesFavoris'])->name('client.favoris.retirer');

    Route::post('/vente-epaves/creer', [ClientController::class, 'creerVenteEpave'])->name('client.vente-epaves.creer');
    Route::get('/vente-epaves/{vente}', [ClientController::class, 'detailVenteEpave'])->name('client.vente-epaves.detail');

    Route::get('/notifications', [ClientController::class, 'notifications'])->name('client.notifications');
    Route::post('/notifications/{notification}/marquer-lue', [ClientController::class, 'marquerNotificationLue'])->name('client.notifications.marquer-lue');
    Route::post('/notifications/marquer-toutes-lues', [ClientController::class, 'marquerToutesNotificationsLues'])->name('client.notifications.marquer-toutes-lues');

    Route::get('/profile', [ClientController::class, 'profile'])->name('client.profile');
    Route::post('/profile/update', [ClientController::class, 'updateProfile'])->name('client.profile.update');

    Route::get('/pieces/{piece}', [ClientController::class, 'detailPiece'])->name('client.detail-piece');



    Route::get('/profile', [ClientController::class, 'profile'])->name('client.profile');
    Route::post('/profile', [ClientController::class, 'updateProfile'])->name('client.profile.update');
});





Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
