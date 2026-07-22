<?php

namespace App\Http\Controllers\immobilisations;

use App\Http\Controllers\Controller;
use App\Models\Immobilisation;
use App\Models\ImmobilisationSite;
use App\Models\ImmobilisationCategorie;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('immobilisations-view');

        $stats = [
            'total_biens' => Immobilisation::count(),
            'valeur_totale' => Immobilisation::sum('valeur_acquisition'),
            'valeur_nette_totale' => Immobilisation::sum('valeur_nette_comptable') ?? 0,
            'biens_affectes' => Immobilisation::where('statut', 'affecte')->count(),
            'biens_en_stock' => Immobilisation::where('statut', 'en_stock')->count(),
            'biens_en_reparation' => Immobilisation::where('statut', 'en_reparation')->count(),
        ];

        // Répartition par catégorie
        $repartitionCategories = ImmobilisationCategorie::withCount('immobilisations')
            ->withSum('immobilisations', 'valeur_acquisition')
            ->get();

        // Répartition par site
        $repartitionSites = ImmobilisationSite::actifs()
            ->withCount('immobilisations')
            ->withSum('immobilisations', 'valeur_acquisition')
            ->get();

        // Biens récemment acquis (30 derniers jours)
        $biensRecents = Immobilisation::with(['categorie', 'site'])
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->latest()
            ->limit(10)
            ->get();

        // Alertes : biens non amortissables avec valeur élevée, biens perdus, etc.
        $alertes = [];

        return view('immobilisations.dashboard', compact(
            'stats',
            'repartitionCategories',
            'repartitionSites',
            'biensRecents',
            'alertes'
        ));
    }
}
