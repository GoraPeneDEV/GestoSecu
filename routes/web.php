<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Rh\DepartementController;
use App\Http\Controllers\Rh\JourFerierController;
use App\Http\Controllers\Rh\SoldeCongeController;
use App\Http\Controllers\Rh\DemandeExplicationController;
use App\Http\Controllers\Rh\DemandeAbsenceAdminController;
use App\Http\Controllers\Rh\EmployeController;
use App\Http\Controllers\Rh\ContratEmployeController;
use App\Http\Controllers\Rh\PlanningController;
use App\Http\Controllers\Rh\HorairePlanningController;
use App\Http\Controllers\Rh\DashboardController as RhDashboardController;
use App\Http\Controllers\Paie\EmployePaieDataController;
use App\Http\Controllers\Paie\VariablePaieController;
use App\Http\Controllers\Paie\BulletinPaieController;
use App\Http\Controllers\Paie\SimulationController;
use App\Http\Controllers\Paie\ElementPaieController;
use App\Http\Controllers\Paie\BaremeFiscalController;
use App\Http\Controllers\Paie\DashboardPaieController;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ── RH ────────────────────────────────────────────────────────
    Route::get('/rh/dashboard', [RhDashboardController::class, 'index'])->name('rh.dashboard');

    // ── Départements ──────────────────────────────────────────────
    Route::resource('departements', DepartementController::class);
    Route::put('/departements/{id}/restore', [DepartementController::class, 'restore'])->name('departements.restore');

    // ── Employés ──────────────────────────────────────────────────
    Route::prefix('employes')->name('employes.')->group(function () {
        Route::get('/', [EmployeController::class, 'index'])->name('index');
        Route::get('/data', [EmployeController::class, 'getEmployes'])->name('data');
        Route::get('/archived', [EmployeController::class, 'archived'])->name('archived');
        Route::get('/archived/data', [EmployeController::class, 'getArchivedEmployes'])->name('archivedData');
        Route::get('/create', [EmployeController::class, 'create'])->name('create');
        Route::post('/', [EmployeController::class, 'store'])->name('store');
        Route::get('/{employe}', [EmployeController::class, 'show'])->name('show');
        Route::get('/{employe}/edit', [EmployeController::class, 'edit'])->name('edit');
        Route::put('/{employe}', [EmployeController::class, 'update'])->name('update');
        Route::delete('/{employe}', [EmployeController::class, 'destroy'])->name('destroy');
        Route::put('/{employe}/unarchive', [EmployeController::class, 'unarchive'])->name('unarchive');
        Route::post('/{employe}/documents', [EmployeController::class, 'uploadDocument'])->name('documents.upload');
        Route::delete('/{employeId}/documents/{documentId}', [EmployeController::class, 'deleteDocument'])->name('documents.delete');
        Route::get('/{employe}/documents/{document}/download', [EmployeController::class, 'downloadDocument'])->name('documents.download');
    });

    // ── Contrats employés ─────────────────────────────────────────
    Route::prefix('contrats')->name('contrats.')->group(function () {
        Route::get('/', [ContratEmployeController::class, 'index'])->name('index');
        Route::get('/data', [ContratEmployeController::class, 'getContrats'])->name('data');
        Route::get('/create', [ContratEmployeController::class, 'create'])->name('create');
        Route::post('/', [ContratEmployeController::class, 'store'])->name('store');
        Route::get('/previous', [ContratEmployeController::class, 'getPreviousContrat'])->name('previous');
        Route::post('/statut', [ContratEmployeController::class, 'updateStatut'])->name('statut.update');
        Route::get('/{employe}/{contrat}/edit', [ContratEmployeController::class, 'edit'])->name('edit');
        Route::put('/{employe}/{contrat}', [ContratEmployeController::class, 'update'])->name('update');
        Route::delete('/{employe}/{contrat}', [ContratEmployeController::class, 'destroy'])->name('destroy');
        Route::delete('/{employeId}/{contratId}/document', [ContratEmployeController::class, 'deleteDocument'])->name('document.delete');
    });

    // ── Plannings ─────────────────────────────────────────────────
    Route::prefix('plannings')->name('plannings.')->group(function () {
        Route::get('/', [PlanningController::class, 'index'])->name('index');
        Route::get('/data', [PlanningController::class, 'getPlannings'])->name('data');
        Route::get('/create', [PlanningController::class, 'create'])->name('create');
        Route::post('/', [PlanningController::class, 'store'])->name('store');
        Route::get('/{planning}', [PlanningController::class, 'show'])->name('show');
        Route::get('/{planning}/edit', [PlanningController::class, 'edit'])->name('edit');
        Route::put('/{planning}', [PlanningController::class, 'update'])->name('update');
        Route::delete('/{planning}', [PlanningController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('horaires')->name('horaires.')->group(function () {
        Route::get('/', [HorairePlanningController::class, 'index'])->name('index');
        Route::get('/data', [HorairePlanningController::class, 'getHoraires'])->name('data');
        Route::get('/create', [HorairePlanningController::class, 'create'])->name('create');
        Route::post('/', [HorairePlanningController::class, 'store'])->name('store');
        Route::get('/{horaire}', [HorairePlanningController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [HorairePlanningController::class, 'edit'])->name('edit');
        Route::put('/{id}', [HorairePlanningController::class, 'update'])->name('update');
        Route::delete('/{id}', [HorairePlanningController::class, 'destroy'])->name('destroy');
    });

    // ── Congés / Absences (absences-admin) ───────────────────────
    Route::prefix('absences-admin')->name('absences-admin.')->group(function () {
        Route::get('/', [DemandeAbsenceAdminController::class, 'index'])->name('index');
        Route::get('/data', [DemandeAbsenceAdminController::class, 'getData'])->name('data');
        Route::get('/departement', [DemandeAbsenceAdminController::class, 'departement'])->name('departement');
        Route::get('/departement/data', [DemandeAbsenceAdminController::class, 'getDataDepartement'])->name('departement-data');
        Route::get('/suivi-global', [DemandeAbsenceAdminController::class, 'suiviGlobal'])->name('suivi-global');
        Route::get('/suivi-global/data', [DemandeAbsenceAdminController::class, 'getDataSuiviGlobal'])->name('suivi-global-data');
        Route::get('/mon-solde', [DemandeAbsenceAdminController::class, 'monSolde'])->name('mon-solde');
        Route::get('/demandes-en-cours', [DemandeAbsenceAdminController::class, 'demandesEnCours'])->name('demandes-en-cours');
        Route::get('/create', [DemandeAbsenceAdminController::class, 'create'])->name('create');
        Route::post('/', [DemandeAbsenceAdminController::class, 'store'])->name('store');
        Route::get('/create-libre', [DemandeAbsenceAdminController::class, 'createLibre'])->name('create-libre');
        Route::post('/libre', [DemandeAbsenceAdminController::class, 'storeLibre'])->name('store-libre');
        Route::get('/enregistrement-direct', [DemandeAbsenceAdminController::class, 'createEnregistrementDirect'])->name('enregistrement-direct');
        Route::post('/enregistrement-direct', [DemandeAbsenceAdminController::class, 'storeEnregistrementDirect'])->name('enregistrement-direct.store');
        Route::post('/calculate-working-days', [DemandeAbsenceAdminController::class, 'calculateWorkingDays'])->name('calculate-working-days');
        Route::get('/{demande}', [DemandeAbsenceAdminController::class, 'show'])->name('show');
        Route::get('/{demande}/edit', [DemandeAbsenceAdminController::class, 'edit'])->name('edit');
        Route::post('/{demandeId}/validation-superieur', [DemandeAbsenceAdminController::class, 'validationSuperieur'])->name('validation-superieur');
        Route::post('/{demandeId}/validation-rh', [DemandeAbsenceAdminController::class, 'validationRH'])->name('validation-rh');
        Route::post('/{demande}/annuler', [DemandeAbsenceAdminController::class, 'annuler'])->name('annuler');
        Route::post('/{demande}/annuler-createur', [DemandeAbsenceAdminController::class, 'annulerParCreateur'])->name('annuler-createur');
        Route::delete('/{demande}', [DemandeAbsenceAdminController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('conge/soldes')->name('conge.soldes.')->group(function () {
        Route::get('/', [SoldeCongeController::class, 'index'])->name('index');
        Route::post('/ajuster', [SoldeCongeController::class, 'ajuster'])->name('ajuster');
        Route::get('/{employe}/historique', [SoldeCongeController::class, 'historique'])->name('historique');
    });

    Route::resource('jours_ferier', JourFerierController::class)->except(['edit']);
    Route::get('/jours_ferier/{jourFerier}/edit', [JourFerierController::class, 'edit'])->name('jours_ferier.edit');
    Route::get('/jours_ferier-data', [JourFerierController::class, 'getJoursFerier'])->name('jours_ferier.data');
    Route::get('/jours_ferier-trashed', [JourFerierController::class, 'trashed'])->name('jours_ferier.trashed');
    Route::put('/jours_ferier/{id}/restore', [JourFerierController::class, 'restore'])->name('jours_ferier.restore');
    Route::delete('/jours_ferier/{id}/force', [JourFerierController::class, 'forceDelete'])->name('jours_ferier.forceDelete');

    Route::prefix('demandes-explications')->name('demandes-explications.')->group(function () {
        Route::get('/', [DemandeExplicationController::class, 'index'])->name('index');
        Route::get('/data', [DemandeExplicationController::class, 'getDemandesExplications'])->name('data');
        Route::get('/stats', [DemandeExplicationController::class, 'stats'])->name('stats');
        Route::get('/employes', [DemandeExplicationController::class, 'getEmployes'])->name('employes');
        Route::get('/create', [DemandeExplicationController::class, 'create'])->name('create');
        Route::post('/', [DemandeExplicationController::class, 'store'])->name('store');
        Route::get('/{demande}', [DemandeExplicationController::class, 'show'])->name('show');
        Route::get('/{id}/ajax', [DemandeExplicationController::class, 'showAjax'])->name('show-ajax');
        Route::get('/{demande}/edit', [DemandeExplicationController::class, 'edit'])->name('edit');
        Route::put('/{demande}', [DemandeExplicationController::class, 'update'])->name('update');
        Route::post('/{id}/repondre', [DemandeExplicationController::class, 'repondre'])->name('repondre');
        Route::delete('/{id}', [DemandeExplicationController::class, 'destroy'])->name('destroy');
    });

    // ── Paie ──────────────────────────────────────────────────────
    Route::prefix('paie')->name('paie.')->group(function () {
        Route::get('/dashboard', [DashboardPaieController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [DashboardPaieController::class, 'getStats'])->name('dashboard.stats');

        Route::get('/employes/{employe}/edit', [EmployePaieDataController::class, 'edit'])->name('employes.edit');
        Route::put('/employes/{employe}', [EmployePaieDataController::class, 'update'])->name('employes.update');
        Route::get('/employes/{employe}', [EmployePaieDataController::class, 'show'])->name('employes.show');

        Route::prefix('variables')->name('variables.')->group(function () {
            Route::get('/', [VariablePaieController::class, 'index'])->name('index');
            Route::post('/', [VariablePaieController::class, 'store'])->name('store');
            Route::post('/valider', [VariablePaieController::class, 'validate'])->name('validate');
            Route::delete('/', [VariablePaieController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('bulletins')->name('bulletins.')->group(function () {
            Route::get('/', [BulletinPaieController::class, 'index'])->name('index');
            Route::post('/generate-batch', [BulletinPaieController::class, 'generateBatch'])->name('generate-batch');
            Route::post('/{employe}/generate', [BulletinPaieController::class, 'generate'])->name('generate');
            Route::get('/{bulletin}', [BulletinPaieController::class, 'show'])->name('show');
            Route::post('/{bulletin}/valider', [BulletinPaieController::class, 'validate'])->name('validate');
            Route::delete('/{bulletin}', [BulletinPaieController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('simulations')->name('simulations.')->group(function () {
            Route::get('/', [SimulationController::class, 'index'])->name('index');
            Route::post('/brut-to-net', [SimulationController::class, 'simulateBrutToNet'])->name('brut-to-net');
            Route::post('/net-to-brut', [SimulationController::class, 'simulateNetToBrut'])->name('net-to-brut');
        });

        Route::prefix('elements-paie')->name('elements-paie.')->group(function () {
            Route::get('/', [ElementPaieController::class, 'index'])->name('index');
            Route::get('/data', [ElementPaieController::class, 'getElementsPaie'])->name('data');
            Route::post('/', [ElementPaieController::class, 'store'])->name('store');
            Route::get('/{element}/edit', [ElementPaieController::class, 'edit'])->name('edit');
            Route::put('/{element}', [ElementPaieController::class, 'update'])->name('update');
            Route::delete('/{element}', [ElementPaieController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('baremes-fiscaux')->name('baremes-fiscaux.')->group(function () {
            Route::get('/', [BaremeFiscalController::class, 'index'])->name('index');
            Route::get('/data', [BaremeFiscalController::class, 'getBaremes'])->name('data');
            Route::post('/', [BaremeFiscalController::class, 'store'])->name('store');
            Route::get('/{bareme}/edit', [BaremeFiscalController::class, 'edit'])->name('edit');
            Route::put('/{bareme}', [BaremeFiscalController::class, 'update'])->name('update');
            Route::delete('/{id}', [BaremeFiscalController::class, 'destroy'])->name('destroy');
        });
    });

    // ===========================
    // ARTICLES
    // ===========================
    Route::prefix('articles')->name('articles.')->group(function () {
        Route::get('/', [App\Http\Controllers\Articles\ArticleController::class, 'index'])->name('index');
        Route::get('/getArticles', [App\Http\Controllers\Articles\ArticleController::class, 'getArticles'])->name('getArticles');

        Route::get('/inventaire', [App\Http\Controllers\Articles\ArticleController::class, 'inventaire'])->name('inventaire');
        Route::post('/inventaire/generer', [App\Http\Controllers\Articles\ArticleController::class, 'genererRapportInventaire'])->name('inventaire.generer');
        Route::post('/inventaire/export-pdf', [App\Http\Controllers\Articles\ArticleController::class, 'exporterInventairePDF'])->name('inventaire.export-pdf');

        Route::post('/', [App\Http\Controllers\Articles\ArticleController::class, 'store'])->name('store');

        Route::get('/{article}', [App\Http\Controllers\Articles\ArticleController::class, 'show'])->name('show');
        Route::get('/{article}/edit', [App\Http\Controllers\Articles\ArticleController::class, 'edit'])->name('edit');
        Route::put('/{article}', [App\Http\Controllers\Articles\ArticleController::class, 'update'])->name('update');
        Route::delete('/{article}', [App\Http\Controllers\Articles\ArticleController::class, 'destroy'])->name('destroy');
    });

    // ===========================
    // DOTATIONS
    // ===========================
    Route::prefix('dotations')->name('dotations.')->group(function () {
        Route::get('/data', [App\Http\Controllers\Articles\DotationController::class, 'data'])->name('data');

        Route::get('/', [App\Http\Controllers\Articles\DotationController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Articles\DotationController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Articles\DotationController::class, 'store'])->name('store');

        Route::post('/detail/return', [App\Http\Controllers\Articles\DotationController::class, 'returnDetail'])->name('detail.return');

        Route::get('/{dotation}', [App\Http\Controllers\Articles\DotationController::class, 'show'])->name('show');
        Route::get('/{dotation}/edit', [App\Http\Controllers\Articles\DotationController::class, 'edit'])->name('edit');
        Route::put('/{dotation}', [App\Http\Controllers\Articles\DotationController::class, 'update'])->name('update');
        Route::delete('/{dotation}', [App\Http\Controllers\Articles\DotationController::class, 'destroy'])->name('destroy');
    });

    Route::get('/dotation', [App\Http\Controllers\Articles\DotationController::class, 'publicIndex'])->name('dotation.public');
    Route::get('/dotation/data', [App\Http\Controllers\Articles\DotationController::class, 'publicData'])->name('dotation.data');

    // ===========================
    // IMMOBILISATIONS
    // ===========================
    Route::prefix('immobilisations')->name('immobilisations.')->group(function () {

        Route::get('/dashboard', [App\Http\Controllers\immobilisations\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [App\Http\Controllers\immobilisations\CategorieController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\immobilisations\CategorieController::class, 'data'])->name('data');
            Route::post('/', [App\Http\Controllers\immobilisations\CategorieController::class, 'store'])->name('store');
            Route::get('/{categorie}/edit', [App\Http\Controllers\immobilisations\CategorieController::class, 'edit'])->name('edit');
            Route::post('/{categorie}/update', [App\Http\Controllers\immobilisations\CategorieController::class, 'update'])->name('update');
            Route::delete('/{categorie}', [App\Http\Controllers\immobilisations\CategorieController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('sites')->name('sites.')->group(function () {
            Route::get('/', [App\Http\Controllers\immobilisations\SiteController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\immobilisations\SiteController::class, 'data'])->name('data');
            Route::post('/', [App\Http\Controllers\immobilisations\SiteController::class, 'store'])->name('store');
            Route::get('/{site}/edit', [App\Http\Controllers\immobilisations\SiteController::class, 'edit'])->name('edit');
            Route::post('/{site}/update', [App\Http\Controllers\immobilisations\SiteController::class, 'update'])->name('update');
            Route::post('/{site}/toggle', [App\Http\Controllers\immobilisations\SiteController::class, 'toggleStatus'])->name('toggle');
            Route::get('/{site}/emplacements', [App\Http\Controllers\immobilisations\SiteController::class, 'getEmplacements'])->name('emplacements');
        });

        Route::prefix('biens')->name('biens.')->group(function () {
            Route::get('/', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'data'])->name('data');
            Route::get('/create', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'create'])->name('create');
            Route::get('/preview-code', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'previewCode'])->name('preview-code');
            Route::post('/', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'store'])->name('store');

            Route::get('/{bien}', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'show'])->name('show');
            Route::get('/{bien}/edit', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'edit'])->name('edit');
            Route::put('/{bien}', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'update'])->name('update');
            Route::delete('/{bien}', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'destroy'])->name('destroy');
            Route::get('/{bien}/qrcode', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'qrcode'])->name('qrcode');
            Route::get('/{bien}/amortissement', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'amortissement'])->name('amortissement');
            Route::post('/{bien}/recalculer', [App\Http\Controllers\immobilisations\ImmobilisationController::class, 'recalculerAmortissement'])->name('recalculer');

            Route::post('/{bien}/affecter', [App\Http\Controllers\immobilisations\AffectationController::class, 'affecter'])->name('affecter');
            Route::post('/{bien}/transferer', [App\Http\Controllers\immobilisations\AffectationController::class, 'transferer'])->name('transferer');
            Route::post('/{bien}/retourner', [App\Http\Controllers\immobilisations\AffectationController::class, 'retourner'])->name('retourner');
            Route::get('/{bien}/historique', [App\Http\Controllers\immobilisations\AffectationController::class, 'historique'])->name('historique');
        });

        Route::get('/scan/{token}', [App\Http\Controllers\immobilisations\ScanController::class, 'show'])->name('scan');
    });

    // ===========================
    // SAV
    // ===========================
    Route::prefix('sav')->name('sav.')->group(function () {

        Route::get('/', [App\Http\Controllers\SAV\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/statistiques', [App\Http\Controllers\SAV\DashboardController::class, 'statistiques'])->name('statistiques');

        Route::prefix('fiches-progres')->name('fiches-progres.')->group(function () {
            Route::get('/', [App\Http\Controllers\SAV\FicheProgresController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\SAV\FicheProgresController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\SAV\FicheProgresController::class, 'store'])->name('store');
            Route::get('/{ficheProgres}', [App\Http\Controllers\SAV\FicheProgresController::class, 'show'])->name('show');
            Route::delete('/{ficheProgres}', [App\Http\Controllers\SAV\FicheProgresController::class, 'destroy'])->name('destroy');
            Route::patch('/{ficheProgres}/analyse', [App\Http\Controllers\SAV\FicheProgresController::class, 'updateAnalyse5M'])->name('analyse');
            Route::post('/{ficheProgres}/actions', [App\Http\Controllers\SAV\FicheProgresController::class, 'addAction'])->name('actions.add');
            Route::patch('/{ficheProgres}/actions/{action}/realiser', [App\Http\Controllers\SAV\FicheProgresController::class, 'realiserAction'])->name('actions.realiser');
            Route::post('/{ficheProgres}/upload', [App\Http\Controllers\SAV\FicheProgresController::class, 'uploadPieceJointe'])->name('upload');
            Route::post('/{ficheProgres}/evaluer', [App\Http\Controllers\SAV\FicheProgresController::class, 'evaluer'])->name('evaluer');
        });

        Route::prefix('contrats')->name('contrats.')->group(function () {
            Route::get('/', [App\Http\Controllers\SAV\ContratController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\SAV\ContratController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\SAV\ContratController::class, 'store'])->name('store');
            Route::get('/{contrat}', [App\Http\Controllers\SAV\ContratController::class, 'show'])->name('show');
            Route::get('/{contrat}/edit', [App\Http\Controllers\SAV\ContratController::class, 'edit'])->name('edit');
            Route::put('/{contrat}', [App\Http\Controllers\SAV\ContratController::class, 'update'])->name('update');
            Route::delete('/{contrat}', [App\Http\Controllers\SAV\ContratController::class, 'destroy'])->name('destroy');
            Route::get('/{contrat}/download', [App\Http\Controllers\SAV\ContratController::class, 'download'])->name('download');
            Route::post('/{contrat}/renouveler', [App\Http\Controllers\SAV\ContratController::class, 'renouveler'])->name('renouveler');
        });

        Route::prefix('garanties')->name('garanties.')->group(function () {
            Route::get('/', [App\Http\Controllers\SAV\GarantieController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\SAV\GarantieController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\SAV\GarantieController::class, 'store'])->name('store');
            Route::get('/{garantie}', [App\Http\Controllers\SAV\GarantieController::class, 'show'])->name('show');
            Route::get('/{garantie}/edit', [App\Http\Controllers\SAV\GarantieController::class, 'edit'])->name('edit');
            Route::put('/{garantie}', [App\Http\Controllers\SAV\GarantieController::class, 'update'])->name('update');
            Route::delete('/{garantie}', [App\Http\Controllers\SAV\GarantieController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('interactions')->name('interactions.')->group(function () {
            Route::get('/', [App\Http\Controllers\SAV\ClientInteractionController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\SAV\ClientInteractionController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\SAV\ClientInteractionController::class, 'store'])->name('store');
            Route::get('/{interaction}', [App\Http\Controllers\SAV\ClientInteractionController::class, 'show'])->name('show');
            Route::delete('/{interaction}', [App\Http\Controllers\SAV\ClientInteractionController::class, 'destroy'])->name('destroy');
            Route::patch('/{interaction}/traite', [App\Http\Controllers\SAV\ClientInteractionController::class, 'marquerTraite'])->name('traite');
            Route::post('/{interaction}/rappel', [App\Http\Controllers\SAV\ClientInteractionController::class, 'programmerRappel'])->name('rappel');
        });

        Route::prefix('parc')->name('parc.')->group(function () {
            Route::get('/', [App\Http\Controllers\SAV\ClientAssetController::class, 'index'])->name('index');
            Route::get('/client/{client}', [App\Http\Controllers\SAV\ClientAssetController::class, 'clientDetail'])->name('client');
            Route::post('/', [App\Http\Controllers\SAV\ClientAssetController::class, 'store'])->name('store');
            Route::get('/{asset}/edit', [App\Http\Controllers\SAV\ClientAssetController::class, 'edit'])->name('edit');
            Route::put('/{asset}', [App\Http\Controllers\SAV\ClientAssetController::class, 'update'])->name('update');
            Route::delete('/{asset}', [App\Http\Controllers\SAV\ClientAssetController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('maintenances')->name('maintenances.')->group(function () {
            Route::get('/', [App\Http\Controllers\SAV\MaintenanceController::class, 'index'])->name('index');
            Route::get('/sites-by-contrat', [App\Http\Controllers\SAV\MaintenanceController::class, 'sitesByContrat'])->name('sites-by-contrat');
            Route::get('/export-pdf', [App\Http\Controllers\SAV\MaintenanceController::class, 'exportPdf'])->name('export-pdf');
            Route::post('/', [App\Http\Controllers\SAV\MaintenanceController::class, 'store'])->name('store');
            Route::get('/{maintenance}', [App\Http\Controllers\SAV\MaintenanceController::class, 'show'])->name('show');
            Route::put('/{maintenance}', [App\Http\Controllers\SAV\MaintenanceController::class, 'update'])->name('update');
            Route::delete('/{maintenance}', [App\Http\Controllers\SAV\MaintenanceController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('interventions')->name('interventions.')->group(function () {
            Route::get('/', [App\Http\Controllers\SAV\InterventionController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\SAV\InterventionController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\SAV\InterventionController::class, 'store'])->name('store');
            Route::get('/{intervention}', [App\Http\Controllers\SAV\InterventionController::class, 'show'])->name('show');
            Route::get('/{intervention}/edit', [App\Http\Controllers\SAV\InterventionController::class, 'edit'])->name('edit');
            Route::put('/{intervention}', [App\Http\Controllers\SAV\InterventionController::class, 'update'])->name('update');
            Route::get('/{intervention}/pdf', [App\Http\Controllers\SAV\InterventionController::class, 'downloadPdf'])->name('pdf');
        });
    });

    Route::prefix('api/clients')->group(function () {
        Route::get('/{client}/contacts', function ($clientId) {
            return \App\Models\SAV\ClientContact::where('client_id', $clientId)->get();
        });
        Route::get('/{client}/contrats', function ($clientId) {
            return \App\Models\SAV\Contrat::where('client_id', $clientId)->where('statut', 'actif')->get();
        });
    });

    // ===========================
    // RONDE (AGENT)
    // ===========================
    Route::prefix('sie/plannings-ronde')->name('sie.plannings-ronde.')->group(function () {
        Route::get('/', [App\Http\Controllers\sie\PlanningRondeController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\sie\PlanningRondeController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\sie\PlanningRondeController::class, 'store'])->name('store');
        Route::get('/data', [App\Http\Controllers\sie\PlanningRondeController::class, 'getPlannings'])->name('data');
        Route::get('/{planningRonde}', [App\Http\Controllers\sie\PlanningRondeController::class, 'show'])->name('show');
        Route::get('/{planningRonde}/edit', [App\Http\Controllers\sie\PlanningRondeController::class, 'edit'])->name('edit');
        Route::put('/{planningRonde}', [App\Http\Controllers\sie\PlanningRondeController::class, 'update'])->name('update');
        Route::delete('/{planningRonde}', [App\Http\Controllers\sie\PlanningRondeController::class, 'destroy'])->name('destroy');
        Route::get('/points-controle/{site}', [App\Http\Controllers\sie\PlanningRondeController::class, 'getPointsControleBySite'])->name('points-controle');
    });

    Route::prefix('sie/pointcontroles')->name('sie.pointcontroles.')->group(function () {
        Route::get('/', [App\Http\Controllers\sie\PointControleController::class, 'index'])->name('index');
        Route::get('/getPointControles', [App\Http\Controllers\sie\PointControleController::class, 'getPointControles'])->name('getPointControles');
        Route::get('/create', [App\Http\Controllers\sie\PointControleController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\sie\PointControleController::class, 'store'])->name('store');
        Route::get('/{pointControle}', [App\Http\Controllers\sie\PointControleController::class, 'show'])->name('show');
        Route::get('/{pointControle}/edit', [App\Http\Controllers\sie\PointControleController::class, 'edit'])->name('edit');
        Route::put('/{pointControle}', [App\Http\Controllers\sie\PointControleController::class, 'update'])->name('update');
        Route::delete('/{pointControle}', [App\Http\Controllers\sie\PointControleController::class, 'destroy'])->name('destroy');
        Route::get('/{pointControle}/download-qr', [App\Http\Controllers\sie\PointControleController::class, 'downloadQR'])->name('download-qr');
    });

    Route::prefix('sie/rondes')->name('sie.rondes.')->group(function () {
        Route::get('/', [App\Http\Controllers\sie\RondeController::class, 'index'])->name('index');
        Route::get('/data', [App\Http\Controllers\sie\RondeController::class, 'getRondes'])->name('data');
        Route::get('/create', [App\Http\Controllers\sie\RondeController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\sie\RondeController::class, 'store'])->name('store');
        Route::get('/{ronde}/scan', [App\Http\Controllers\sie\RondeController::class, 'scan'])->name('scan');
        Route::post('/{ronde}/verify-qr', [App\Http\Controllers\sie\RondeController::class, 'verifyQRCode'])->name('verify-qr');
        Route::post('/{ronde}/anomalie', [App\Http\Controllers\sie\RondeController::class, 'storeAnomalie'])->name('anomalie');
        Route::get('/{ronde}/export-anomalies', [App\Http\Controllers\sie\RondeController::class, 'exportAnomalies'])->name('export-anomalies');
        Route::get('/{ronde}', [App\Http\Controllers\sie\RondeController::class, 'show'])->name('show');
        Route::put('/{ronde}/terminer', [App\Http\Controllers\sie\RondeController::class, 'terminer'])->name('terminer');
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/rondes/{ronde}', [App\Http\Controllers\sie\RondeController::class, 'getRondeInfo'])->name('getRondeInfo');
            Route::post('/scans', [App\Http\Controllers\sie\RondeController::class, 'storeScan'])->name('storeScan');
            Route::post('/scans/{scan}/photo', [App\Http\Controllers\sie\RondeController::class, 'storePhoto'])->name('storePhoto');
            Route::get('rondes/stats', [App\Http\Controllers\sie\RondeController::class, 'getStats'])->name('rondes.stats');
        });
    });

    // ===========================
    // RONDE (SUPERVISEUR)
    // ===========================
    Route::prefix('superviseur/pointcontroles')->name('superviseur.pointcontroles.')->group(function () {
        Route::get('/', [App\Http\Controllers\superviseur\PointControleSuperviseurController::class, 'index'])->name('index');
        Route::get('/getPointControles', [App\Http\Controllers\superviseur\PointControleSuperviseurController::class, 'getPointControles'])->name('getPointControles');
        Route::get('/create', [App\Http\Controllers\superviseur\PointControleSuperviseurController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\superviseur\PointControleSuperviseurController::class, 'store'])->name('store');
        Route::get('/{pointControle}', [App\Http\Controllers\superviseur\PointControleSuperviseurController::class, 'show'])->name('show');
        Route::get('/{pointControle}/edit', [App\Http\Controllers\superviseur\PointControleSuperviseurController::class, 'edit'])->name('edit');
        Route::put('/{pointControle}', [App\Http\Controllers\superviseur\PointControleSuperviseurController::class, 'update'])->name('update');
        Route::delete('/{pointControle}', [App\Http\Controllers\superviseur\PointControleSuperviseurController::class, 'destroy'])->name('destroy');
        Route::get('/{pointControle}/download-qr', [App\Http\Controllers\superviseur\PointControleSuperviseurController::class, 'downloadQR'])->name('download-qr');
    });

    // ===========================
    // SUPERVISION (visites de sites)
    // ===========================
    Route::prefix('supervision')->name('supervision.')->group(function () {
        Route::get('/', [App\Http\Controllers\SupervisionController::class, 'index'])->name('index');
        Route::get('visites', [App\Http\Controllers\SupervisionController::class, 'index'])->name('visites.index');
        Route::get('visites/data', [App\Http\Controllers\SupervisionController::class, 'getVisits'])->name('visites.data');
        Route::get('visites/site-agents/{site}', [App\Http\Controllers\SupervisionController::class, 'getSiteAgents'])->name('visites.site-agents');
        Route::post('visites', [App\Http\Controllers\SupervisionController::class, 'store'])->name('visites.store');
        Route::get('visites/{visite}/edit', [App\Http\Controllers\SupervisionController::class, 'edit'])->name('visites.edit');
        Route::put('visites/{visite}', [App\Http\Controllers\SupervisionController::class, 'update'])->name('visites.update');
        Route::delete('visites/{visite}', [App\Http\Controllers\SupervisionController::class, 'destroy'])->name('visites.destroy');
    });

    // ===========================
    // IT
    // ===========================
    Route::prefix('it')->name('it.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\It\DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('client-users')->name('client-users.')->group(function () {
            Route::get('/', [App\Http\Controllers\It\ClientUserController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\It\ClientUserController::class, 'store'])->name('store');
            Route::get('/{clientUser}/edit', [App\Http\Controllers\It\ClientUserController::class, 'edit'])->name('edit');
            Route::put('/{clientUser}', [App\Http\Controllers\It\ClientUserController::class, 'update'])->name('update');
            Route::delete('/{clientUser}', [App\Http\Controllers\It\ClientUserController::class, 'destroy'])->name('destroy');
            Route::post('/{clientUser}/reset-password', [App\Http\Controllers\It\ClientUserController::class, 'resetPassword'])->name('reset-password');
        });
    });
});
