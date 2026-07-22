<?php

namespace App\Http\Controllers\Portail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BasePortailController
{
    /**
     * Affiche le dashboard du portail client
     */
    public function index()
    {
        $user = Auth::guard('portail')->user();
        $client = $user->client;

        $siteIds = $client->sites()->pluck('id');

        $stats = [
            'totalSites' => $client->sites()->count(),
            'sitesActifs' => $client->sites()->whereNull('date_arret')->count(),
            'sitesArchives' => $client->sites()->whereNotNull('date_arret')->count(),
            'sitesGardiennage' => $client->sites()->where('type_site', 'gardiennage')->count(),
            'sitesNettoyage' => $client->sites()->where('type_site', 'nettoyage')->count(),
            'sitesMixtes' => $client->sites()->where('type_site', 'mixte')->count(),
            'totalEquipements' => \App\Models\SAV\ClientAsset::whereIn('site_id', $siteIds)->count(),
            'equipementsMaintenance' => \App\Models\SAV\ClientAsset::whereIn('site_id', $siteIds)->whereIn('status', ['panne', 'maintenance_requise'])->count(),
            'equipementsHS' => \App\Models\SAV\ClientAsset::whereIn('site_id', $siteIds)->where('status', 'hors_service')->count(),
        ];

        $sitesParRegion = $client->sites()
            ->whereNull('date_arret')
            ->selectRaw('region, count(*) as total')
            ->groupBy('region')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $sitesRecents = $client->sites()
            ->whereNull('date_arret')
            ->orderByDesc('date_debut')
            ->limit(5)
            ->get();

        return view('portail.dashboard', compact(
            'stats',
            'sitesParRegion',
            'sitesRecents'
        ));
    }

    /**
     * API pour récupérer les statistiques (pour AJAX)
     */
    public function getStats()
    {
        $user = Auth::guard('portail')->user();
        $client = $user->client;

        $stats = [
            'totalSites' => $client->sites()->count(),
            'sitesActifs' => $client->sites()->whereNull('date_arret')->count(),
            'sitesArchives' => $client->sites()->whereNotNull('date_arret')->count(),
            'sitesGardiennage' => $client->sites()->where('type_site', 'gardiennage')->count(),
            'sitesNettoyage' => $client->sites()->where('type_site', 'nettoyage')->count(),
            'sitesMixtes' => $client->sites()->where('type_site', 'mixte')->count(),
        ];

        $evolutionMensuelle = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $evolutionMensuelle[] = [
                'mois' => $date->format('M Y'),
                'sites' => $client->sites()
                    ->where('date_debut', '<=', $date->endOfMonth())
                    ->whereNull('date_arret')
                    ->count()
            ];
        }

        return response()->json([
            'stats' => $stats,
            'evolution' => $evolutionMensuelle
        ]);
    }

    /**
     * API pour les données d'évolution (graphique ligne/barre)
     */
    public function getEvolutionData()
    {
        $user = Auth::guard('portail')->user();
        if (!$user || !$user->client) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $client = $user->client;

        $evolution = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = $client->sites()
                ->whereYear('date_debut', $date->year)
                ->whereMonth('date_debut', $date->month)
                ->count();

            $evolution[] = [
                'mois' => $date->format('M Y'),
                'valeur' => $count
            ];
        }

        return response()->json($evolution);
    }

    /**
     * API pour les données de répartition (graphique "camembert")
     */
    public function getRepartitionData()
    {
        $user = Auth::guard('portail')->user();
        if (!$user || !$user->client) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $client = $user->client;

        $repartition = $client->sites()
            ->selectRaw('type_site as label, count(*) as value')
            ->whereNotNull('type_site')
            ->groupBy('type_site')
            ->get();

        return response()->json($repartition);
    }
}
