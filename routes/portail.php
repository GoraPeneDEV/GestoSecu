<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Portail\AuthController;
use App\Http\Controllers\Portail\DashboardController;
use App\Http\Controllers\Portail\SiteController;
use App\Http\Controllers\Portail\AgentController;
use App\Http\Controllers\Portail\RondeController;
use App\Http\Controllers\Portail\ParcController;
use App\Http\Controllers\Portail\RapportController;

Route::name('portail.')->group(function () {

    // Routes d'authentification (invités du portail)
    Route::middleware(['guest.portail:portail'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    });

    // Routes protégées du portail
    Route::middleware(['portail.auth', 'portail.filter', 'portail.menu'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/api/stats', [DashboardController::class, 'getStats'])->name('api.stats');

        Route::prefix('sites')->name('sites.')->group(function () {
            Route::get('/', [SiteController::class, 'index'])->name('index');
            Route::get('/data', [SiteController::class, 'getSites'])->name('getSites');
            Route::get('/geo-data', [SiteController::class, 'getGeoData'])->name('geo-data');
            Route::get('/export-csv', [SiteController::class, 'exportCsv'])->name('export-csv');
            Route::get('/{site}', [SiteController::class, 'show'])->name('show');
        });

        Route::prefix('agents')->name('agents.')->group(function () {
            Route::get('/', [AgentController::class, 'index'])->name('index');
            Route::get('/data', [AgentController::class, 'getAgents'])->name('getAgents');
            Route::get('/{agent}', [AgentController::class, 'show'])->name('show');
            Route::get('/{agent}/planning', [AgentController::class, 'planning'])->name('planning');
            Route::get('/{agent}/documents/{document}/download', [AgentController::class, 'downloadDocument'])->name('documents.download');
            Route::get('/{agent}/documents/{document}/view', [AgentController::class, 'viewDocument'])->name('documents.view');
        });

        Route::prefix('parc')->name('parc.')->group(function () {
            Route::get('/', [ParcController::class, 'index'])->name('index');
            Route::get('/data', [ParcController::class, 'getAssets'])->name('getAssets');
            Route::get('/{asset}', [ParcController::class, 'show'])->name('show');
        });

        Route::prefix('rondes')->name('rondes.')->group(function () {
            Route::get('/', [RondeController::class, 'index'])->name('index');
            Route::get('/data', [RondeController::class, 'getRondes'])->name('getRondes');
            Route::get('/stats', [RondeController::class, 'getStats'])->name('stats');
            Route::get('/{ronde}', [RondeController::class, 'show'])->name('show');
            Route::get('/{ronde}/export-anomalies', [RondeController::class, 'exportAnomalies'])->name('export-anomalies');
        });

        Route::prefix('rapports')->name('rapports.')->group(function () {
            Route::get('/', [RapportController::class, 'index'])->name('index');
            Route::get('/sites', [RapportController::class, 'sites'])->name('sites');
            Route::get('/sites/export', [RapportController::class, 'sitesPdf'])->name('sites.export');
            Route::get('/agents', [RapportController::class, 'agents'])->name('agents');
            Route::get('/agents/export', [RapportController::class, 'agentsPdf'])->name('agents.export');
            Route::get('/rondes', [RapportController::class, 'rondes'])->name('rondes');
            Route::get('/rondes/export', [RapportController::class, 'rondesPdf'])->name('rondes.export');
            Route::get('/parc', [RapportController::class, 'parc'])->name('parc');
            Route::get('/parc/export', [RapportController::class, 'parcPdf'])->name('parc.export');
        });

        Route::get('/support', function () {
            return view('portail.support');
        })->name('support');

        Route::get('/profile', function () {
            return view('portail.profile');
        })->name('profile');

        Route::prefix('profile')->name('profile.')->group(function () {
            Route::put('/update', function () {
                return redirect()->route('portail.profile')->with('success', 'Profil mis à jour');
            })->name('update');

            Route::put('/password', function () {
                return redirect()->route('portail.profile')->with('success', 'Mot de passe modifié');
            })->name('password');
        });

        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/charts/evolution', [DashboardController::class, 'getEvolutionData'])->name('charts.evolution');
            Route::get('/charts/repartition', [DashboardController::class, 'getRepartitionData'])->name('charts.repartition');
        });
    });
});
