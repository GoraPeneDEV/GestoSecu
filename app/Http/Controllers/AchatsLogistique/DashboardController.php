<?php

namespace App\Http\Controllers\AchatsLogistique;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Dotation;
use App\Models\Immobilisation;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('article-view');

        $stats = [
            'total_articles' => Article::count(),
            'articles_sous_stock' => Article::whereRaw('stock_actuel <= stock_minimum')->count(),
            'valeur_stock' => Article::selectRaw('SUM(stock_actuel * prix_unitaire) as valeur')->value('valeur') ?? 0,
            'dotations_mois' => Dotation::whereMonth('date_dotation', now()->month)->whereYear('date_dotation', now()->year)->count(),
            'total_biens' => Immobilisation::count(),
            'valeur_biens' => Immobilisation::sum('valeur_acquisition'),
        ];

        $articlesSousStock = Article::whereRaw('stock_actuel <= stock_minimum')
            ->with('departement')
            ->orderBy('stock_actuel')
            ->limit(10)
            ->get();

        $dotationsRecentes = Dotation::with(['site', 'employe'])
            ->orderByDesc('date_dotation')
            ->limit(10)
            ->get();

        $biensParStatut = Immobilisation::selectRaw('statut, count(*) as total')
            ->groupBy('statut')
            ->get();

        return view('achats-logistique.dashboard', compact(
            'stats', 'articlesSousStock', 'dotationsRecentes', 'biensParStatut'
        ));
    }
}
