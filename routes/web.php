<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StatistiquesController;
use App\Http\Controllers\SelectionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Routes publiques
Route::get('/', [HomeController::class, 'index'])->name('home');

// Routes étudiant
Route::prefix('etudiant')->group(function () {
    Route::get('/formulaire', [EtudiantController::class, 'showForm'])->name('etudiant.form');
    Route::post('/enregistrer', [EtudiantController::class, 'store'])->name('etudiant.store');
});

// Routes authentification
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Routes admin (protégées par authentification)
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Gestion des étudiants
    Route::get('/etudiants', [AdminController::class, 'listeEtudiants'])->name('admin.etudiants');
    Route::get('/etudiants/importer', [AdminController::class, 'importerEtudiants'])->name('admin.etudiants.importer');
    Route::post('/etudiants/importer', [AdminController::class, 'traiterImportation'])->name('admin.etudiants.importer.traiter');
    Route::get('/rechercher-etudiant', [AdminController::class, 'rechercherEtudiant'])->name('admin.rechercher-etudiant');
    Route::get('/etudiants/template/{niveau}', [AdminController::class, 'telechargerTemplate'])->name('admin.etudiants.template');
    Route::delete('/etudiants/{id}', [EtudiantController::class, 'supprimerEtudiant'])->name('admin.etudiants.supprimer');
    Route::post('/etudiants/reinitialiser-liste', [EtudiantController::class, 'reinitialiserListeEtudiants'])->name('admin.etudiants.reinitialiser-liste');

    // Critères de sélection
    Route::get('/criteres', [AdminController::class, 'criteresSelection'])->name('admin.criteres');
    Route::post('/criteres', [AdminController::class, 'enregistrerCriteres'])->name('admin.criteres.enregistrer');
    Route::get('/criteres/{id}', [AdminController::class, 'getCritere'])->name('admin.criteres.get');
    Route::delete('/criteres/{id}', [AdminController::class, 'supprimerCritere'])->name('admin.criteres.supprimer');
    Route::get('/selections/{id}', [AdminController::class, 'afficherSelection'])->name('admin.selections.details');
    Route::get('/criteres/{id}/details', [AdminController::class, 'getCritereDetails'])->name('admin.criteres.details');

    // Sélection
    Route::get('/generer-selection', [SelectionController::class, 'index'])->name('admin.selections.generer');
    Route::post('/generer-selection', [SelectionController::class, 'generer'])->name('admin.generer-selection');
    Route::get('/generer-selection-globale', [SelectionController::class, 'indexGlobal'])->name('admin.selections.generer-global');
    Route::post('/generer-selection-globale', [SelectionController::class, 'genererGlobal'])->name('admin.generer-selection-global');
    Route::post('/reinitialiser-selection-globale', [SelectionController::class, 'reinitialiserGlobal'])->name('admin.reinitialiser-selection-globale');
    Route::get('/selections', [AdminController::class, 'listeSelectionnes'])->name('admin.selections');
    Route::post('/selections/reinitialiser', [AdminController::class, 'reinitialiserSelection'])->name('admin.selections.reinitialiser');
    Route::post('/selections/notifier', [AdminController::class, 'notifierEtudiants'])->name('admin.selections.notifier');

    // Statistiques
    Route::get('/statistiques', [StatistiquesController::class, 'index'])->name('admin.statistiques');

    // Gestion des administrateurs (uniquement pour super_admin)
    Route::middleware(['super.admin'])->group(function () {
        Route::get('/gestion-admins', [AdminController::class, 'gestionAdmins'])->name('admin.gestion-admins');
        Route::post('/admins', [AdminController::class, 'storeAdmin'])->name('admin.admins.store');
        Route::put('/admins/{id}', [AdminController::class, 'updateAdmin'])->name('admin.admins.update');
        Route::delete('/admins/{id}', [AdminController::class, 'destroyAdmin'])->name('admin.admins.destroy');
        Route::get('/admins/{id}/permissions', [AdminController::class, 'getAdminPermissions'])->name('admin.admins.get-permissions');
        Route::post('/admins/permissions', [AdminController::class, 'updateAdminPermissions'])->name('admin.admins.permissions');
    });

    // Routes de profil (protégées par l'authentification)
    Route::middleware(['auth'])->prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/password', [ProfileController::class, 'editPassword'])->name('profile.edit.password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
        Route::post('/avatar/delete', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    });

});
?>
