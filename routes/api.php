<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RondeApiController;
use App\Http\Controllers\API\RondeSupApiController;
use App\Http\Controllers\API\SupervisionApiController;
use App\Http\Controllers\API\Mobile\AbsencesController;
use App\Http\Controllers\API\Mobile\AchatsController;
use App\Http\Controllers\API\Mobile\DashboardController as MobileDashboardController;
use App\Http\Controllers\API\Mobile\ImmobilisationsController;
use App\Http\Controllers\API\Mobile\NotificationsController;
use App\Http\Controllers\API\Mobile\PaieController;
use App\Http\Controllers\API\Mobile\ProfileController as MobileProfileController;
use App\Http\Controllers\API\Mobile\PushTokenController;
use App\Http\Controllers\API\Mobile\RhController;
use App\Http\Controllers\API\Mobile\SavController;

// -------------------------------------------------------------------------
// Routes publiques (sans authentification)
// -------------------------------------------------------------------------
Route::prefix('mobile')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);

    Route::get('ping', [AuthController::class, 'ping']);
});

// -------------------------------------------------------------------------
// Routes protégées (token Sanctum requis)
// -------------------------------------------------------------------------
Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {

    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    // --- Notifications push (token Expo) ---
    Route::post('push-token', [PushTokenController::class, 'store']);
    Route::delete('push-token', [PushTokenController::class, 'destroy']);

    // --- Socle commun : profil, dashboard, mes stats, solde congés ---
    Route::put('profile', [MobileProfileController::class, 'update']);
    Route::put('profile/password', [MobileProfileController::class, 'updatePassword']);
    Route::post('profile/avatar', [MobileProfileController::class, 'updateAvatar']);
    Route::get('dashboard/departement', [MobileDashboardController::class, 'departement']);
    Route::get('dashboard/mes-stats', [MobileDashboardController::class, 'mesStats']);
    Route::get('absences/mon-solde', [MobileDashboardController::class, 'monSolde']);

    // --- Absences & Congés ---
    Route::prefix('absences')->group(function () {
        Route::get('moi', [AbsencesController::class, 'mesDemandes']);
        Route::post('/', [AbsencesController::class, 'store']);
        Route::get('calculate-working-days', [AbsencesController::class, 'calculateWorkingDays']);
        Route::get('departement', [AbsencesController::class, 'departement']);
        Route::get('suivi-global', [AbsencesController::class, 'suiviGlobal']);
        Route::post('{demande}/annuler-par-createur', [AbsencesController::class, 'annulerParCreateur']);
        Route::post('{demande}/validation-superieur', [AbsencesController::class, 'validationSuperieur']);
        Route::post('{demande}/validation-rh', [AbsencesController::class, 'validationRH']);
        Route::post('{demande}/annuler', [AbsencesController::class, 'annuler']);
    });

    // --- SAV ---
    Route::prefix('sav')->group(function () {
        Route::get('interventions/moi', [SavController::class, 'mesInterventions']);
        Route::put('interventions/{intervention}', [SavController::class, 'updateIntervention']);
        Route::post('interventions/{intervention}/photos', [SavController::class, 'storePhotos']);
        Route::get('fiches-progres/moi', [SavController::class, 'mesFichesProgres']);
        Route::get('fiches-progres/{fiche}', [SavController::class, 'showFicheProgres']);
        Route::patch('fiches-progres/{fiche}/analyse', [SavController::class, 'updateAnalyse5M']);
        Route::post('fiches-progres/{fiche}/actions', [SavController::class, 'storeAction']);
        Route::patch('fiches-progres/actions/{action}/realiser', [SavController::class, 'realiserAction']);
        Route::post('fiches-progres/{fiche}/evaluer', [SavController::class, 'evaluer']);
        Route::get('maintenances/moi', [SavController::class, 'mesMaintenances']);
    });

    // --- Immobilisations ---
    Route::prefix('immobilisations')->group(function () {
        Route::get('mes-biens', [ImmobilisationsController::class, 'mesBiens']);
        Route::get('scan/{token}', [ImmobilisationsController::class, 'scan']);
    });

    // --- Paie ---
    Route::prefix('paie')->group(function () {
        Route::get('bulletins/moi', [PaieController::class, 'mesBulletins']);
    });

    // --- RH consultation ---
    Route::prefix('rh')->group(function () {
        Route::get('ma-fiche', [RhController::class, 'maFiche']);
        Route::get('mes-contrats', [RhController::class, 'mesContrats']);
        Route::get('mon-planning', [RhController::class, 'monPlanning']);
        Route::get('demandes-explication', [RhController::class, 'mesDemandesExplication']);
        Route::post('demandes-explication/{demande}/repondre', [RhController::class, 'repondreDemandeExplication']);
    });

    // --- Dotations ---
    Route::prefix('achats')->group(function () {
        Route::get('mes-dotations', [AchatsController::class, 'mesDotations']);
    });

    // --- Notifications ---
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationsController::class, 'index']);
        Route::get('unread-count', [NotificationsController::class, 'unreadCount']);
        Route::post('{id}/read', [NotificationsController::class, 'markAsRead']);
        Route::post('read-all', [NotificationsController::class, 'markAllAsRead']);
    });

    // --- Rondes (agents terrain) ---
    Route::prefix('rondes')->group(function () {
        Route::get('stats', [RondeApiController::class, 'stats']);
        Route::get('plannings', [RondeApiController::class, 'plannings']);
        Route::get('plannings/{id}/points', [RondeApiController::class, 'planningPoints']);
        Route::get('/', [RondeApiController::class, 'index']);
        Route::post('/', [RondeApiController::class, 'store']);
        Route::get('{id}', [RondeApiController::class, 'show']);
        Route::post('{id}/verify-qr', [RondeApiController::class, 'verifyQR']);
        Route::post('{id}/scan', [RondeApiController::class, 'storeScan']);
        Route::post('{id}/gps-tracks', [RondeApiController::class, 'storeGpsTracks']);
        Route::patch('{id}/steps', [RondeApiController::class, 'updateSteps']);
        Route::put('{id}/terminer', [RondeApiController::class, 'terminer']);
    });

    // --- Rondes Superviseur ---
    Route::prefix('superviseur')->group(function () {
        Route::get('stats', [RondeSupApiController::class, 'stats']);
        Route::get('plannings', [RondeSupApiController::class, 'plannings']);
        Route::get('plannings/{id}/points', [RondeSupApiController::class, 'planningPoints']);
        Route::get('rondes', [RondeSupApiController::class, 'index']);
        Route::post('rondes', [RondeSupApiController::class, 'store']);
        Route::get('rondes/{id}', [RondeSupApiController::class, 'show']);
        Route::post('rondes/{id}/verify-qr', [RondeSupApiController::class, 'verifyQR']);
        Route::post('rondes/{id}/scan', [RondeSupApiController::class, 'storeScan']);
        Route::put('rondes/{id}/terminer', [RondeSupApiController::class, 'terminer']);
    });

    // --- Visites Superviseurs (contrôle des sites) ---
    Route::prefix('supervision')->group(function () {
        Route::get('/', [SupervisionApiController::class, 'index']);
        Route::post('scan', [SupervisionApiController::class, 'scan']);
        Route::post('submit', [SupervisionApiController::class, 'submitReport']);
    });

    // --- Agents (liste pour sélection au démarrage de ronde) ---
    Route::get('agents', [RondeApiController::class, 'agents']);
    Route::get('agents/{id}/ronde-active', [RondeApiController::class, 'agentRondeActive']);
});
