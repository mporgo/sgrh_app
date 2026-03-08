<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeController;
use App\Http\Controllers\Api\DepartementController;
use App\Http\Controllers\Api\PosteController;
use App\Http\Controllers\Api\CongeController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\FormationController;
use App\Http\Controllers\Api\InscriptionFormationController;
use App\Http\Controllers\Api\PaieController;
use App\Http\Controllers\Api\RapportController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Support\Facades\Route;

// ── Routes publiques ──────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ── Routes protégées (Sanctum) ────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ── Employés ──────────────────────────────────────────────────────────────
    Route::apiResource('employes', EmployeController::class);

    // ── Départements ──────────────────────────────────────────────────────────
    Route::apiResource('departements', DepartementController::class)->except(['show']);

    // ── Postes ────────────────────────────────────────────────────────────────
    Route::apiResource('postes', PosteController::class)->except(['show', 'destroy']);

     // ── Congés ────────────────────────────────────────────────────────────────
    Route::prefix('conges')->group(function () {
        Route::get('/',                    [CongeController::class, 'index']);
        Route::post('/',                   [CongeController::class, 'store']);
        Route::get('/mes-soldes',          [CongeController::class, 'mesSoldes']);
        Route::get('/types',               [CongeController::class, 'typeConges']);
        Route::get('/calendrier',          [CongeController::class, 'calendrier']);
        Route::get('/{conge}',             [CongeController::class, 'show']);
        Route::post('/{conge}/traiter',    [CongeController::class, 'traiter']);
        Route::post('/{conge}/annuler',    [CongeController::class, 'annuler']);
    });

    // ── Évaluations ───────────────────────────────────────────────────────────
    Route::prefix('evaluations')->group(function () {
        Route::get('/',                                  [EvaluationController::class, 'index']);
        Route::post('/',                                 [EvaluationController::class, 'store']);
        Route::get('/stats',                             [EvaluationController::class, 'stats']);
        Route::get('/{evaluation}',                      [EvaluationController::class, 'show']);
        Route::put('/{evaluation}',                      [EvaluationController::class, 'update']);
        Route::delete('/{evaluation}',                   [EvaluationController::class, 'destroy']);
        Route::post('/{evaluation}/commenter-employe',   [EvaluationController::class, 'commenterEmploye']);
    });

    // ── Formations ────────────────────────────────────────────────────────────
    Route::apiResource('formations', FormationController::class);
    Route::get('formations/{formation}/inscrits', [FormationController::class, 'inscrits']);

    // ── Inscriptions ──────────────────────────────────────────────────────────
    Route::prefix('formations')->group(function () {
        Route::post('/{formation}/inscrire',   [InscriptionFormationController::class, 'inscrire']);
        Route::post('/{formation}/desinscrire',[InscriptionFormationController::class, 'desinscrire']);
    });

    Route::prefix('inscriptions')->group(function () {
        Route::get('/mes-formations',             [InscriptionFormationController::class, 'mesFormations']);
        Route::post('/{inscription}/valider',     [InscriptionFormationController::class, 'valider']);
        Route::post('/{inscription}/resultats',   [InscriptionFormationController::class, 'resultats']);
    });

    // ── Paie ──────────────────────────────────────────────────────────────────
    Route::prefix('paies')->group(function () {
        Route::get('/',                       [PaieController::class, 'index']);
        Route::post('/',                      [PaieController::class, 'store']);
        Route::post('/apercu',                [PaieController::class, 'apercu']);
        Route::get('/stats',                  [PaieController::class, 'stats']);
        Route::get('/{paie}',                 [PaieController::class, 'show']);
        Route::put('/{paie}',                 [PaieController::class, 'update']);
        Route::delete('/{paie}',              [PaieController::class, 'destroy']);
        Route::post('/{paie}/valider',        [PaieController::class, 'valider']);
        Route::post('/{paie}/marquer-paye',   [PaieController::class, 'marquerPaye']);
    });

    // ── Rapports ──────────────────────────────────────────────────────────────
    Route::prefix('rapports')->group(function () {
        Route::get('/global',          [RapportController::class, 'global']);
        Route::get('/absenteisme',     [RapportController::class, 'absenteisme']);
        Route::get('/masse-salariale', [RapportController::class, 'masseSalariale']);
        Route::get('/effectifs',       [RapportController::class, 'effectifs']);
    });

    // ── Notifications ─────────────────────────────────────────────────────────
    Route::prefix('notifications')->group(function () {
        Route::get('/',                                   [NotificationController::class, 'index']);
        Route::get('/non-lues',                           [NotificationController::class, 'nonLues']);
        Route::post('/tout-marquer-lu',                   [NotificationController::class, 'toutMarquerLu']);
        Route::post('/{notification}/marquer-lue',        [NotificationController::class, 'marquerLue']);
        Route::delete('/{notification}',                  [NotificationController::class, 'destroy']);
    });

    // ── Administration (admin uniquement) ─────────────────────────────────────
    Route::prefix('admin')->group(function () {

        // Utilisateurs
        Route::get('/users',                          [AdminController::class, 'users']);
        Route::post('/users',                         [AdminController::class, 'createUser']);
        Route::get('/users/{user}',                   [AdminController::class, 'showUser']);
        Route::put('/users/{user}',                   [AdminController::class, 'updateUser']);
        Route::post('/users/{user}/toggle',           [AdminController::class, 'toggleUser']);
        Route::post('/users/{user}/reset-password',   [AdminController::class, 'resetPassword']);
        Route::post('/users/{user}/assigner-role',    [AdminController::class, 'assignerRole']);

        // Rôles
        Route::get('/roles', [AdminController::class, 'roles']);

        // Logs
        Route::get('/logs', [AdminController::class, 'logs']);

        // Système
        Route::get('/infos-systeme', [AdminController::class, 'infosSysteme']);
    });
    // Les autres routes seront ajoutées module par module
});
