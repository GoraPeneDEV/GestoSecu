<?php

namespace App\Http\Controllers\Portail;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SiteController extends BasePortailController
{
    /**
     * Affiche la liste des sites du client connecté
     */
    public function index()
    {
        $this->authorize('portail-site-view');
        $user = Auth::guard('portail')->user();

        $stats = [
            'totalSites' => $user->client->sites()->count(),
            'sitesActifs' => $user->client->sites()->whereNull('date_arret')->count(),
            'sitesArchives' => $user->client->sites()->whereNotNull('date_arret')->count(),
            'sitesGardiennage' => $user->client->sites()->where('type_site', 'gardiennage')->count(),
            'sitesNettoyage' => $user->client->sites()->where('type_site', 'nettoyage')->count(),
            'sitesMixtes' => $user->client->sites()->where('type_site', 'mixte')->count(),
            'totalAgents' => $user->client->sites()
                ->withCount(['plannings as plannings_actifs_count' => function ($query) {
                    $query->whereNull('date_fin');
                }])
                ->get()
                ->sum('plannings_actifs_count')
        ];

        $sitesParRegion = $user->client->sites()
            ->selectRaw('region, count(*) as total')
            ->groupBy('region')
            ->orderByDesc('total')
            ->get();

        return view('portail.sites.index', compact('stats', 'sitesParRegion'));
    }

    /**
     * Récupère les sites pour DataTables (filtré par client)
     */
    public function getSites(Request $request)
    {
        $user = Auth::guard('portail')->user();

        $sites = $user->client->sites()
            ->with(['zone'])
            ->withCount(['plannings as plannings_actifs_count' => function ($query) {
                $query->whereNull('date_fin');
            }])
            ->whereNull('date_arret');

        if ($request->filled('region')) {
            $sites->where('region', $request->region);
        }

        if ($request->filled('type_site')) {
            $sites->where('type_site', $request->type_site);
        }

        return DataTables::of($sites)
            ->editColumn('date_debut', function ($site) {
                return $site->date_debut ? $site->date_debut->format('d/m/Y') : '-';
            })
            ->addColumn('zone', function ($site) {
                return $site->zone ? $site->zone->nom : 'Non définie';
            })
            ->addColumn('agents_count', function ($site) {
                $nombreAgents = $site->plannings_actifs_count;

                if ($nombreAgents > 0) {
                    $badgeClass = $nombreAgents >= 3 ? 'bg-success' : ($nombreAgents >= 2 ? 'bg-warning' : 'bg-info');
                    return '<span class="badge ' . $badgeClass . '" title="' . $nombreAgents . ' agent(s) actif(s)">' . $nombreAgents . ' agent' . ($nombreAgents > 1 ? 's' : '') . '</span>';
                } else {
                    return '<span class="badge bg-secondary" title="Aucun agent assigné">0 agent</span>';
                }
            })
            ->editColumn('type_site', function ($site) {
                $badges = [
                    'gardiennage' => 'bg-primary',
                    'nettoyage' => 'bg-success',
                    'mixte' => 'bg-warning'
                ];

                $badge = $badges[$site->type_site] ?? 'bg-secondary';
                return '<span class="badge ' . $badge . '">' . ucfirst($site->type_site) . '</span>';
            })
            ->addColumn('actions', function ($site) {
                $actions = '
          <div class="d-inline-block text-nowrap">
            <a href="' . route('portail.sites.show', $site->id) . '"
               class="btn btn-sm btn-icon btn-outline-primary" title="Voir détails">
                <i class="bi bi-eye"></i>
            </a>';

                if ($site->latitude && $site->longitude) {
                    $actions .= '
            <a href="https://www.google.com/maps?q=' . $site->latitude . ',' . $site->longitude . '"
               target="_blank" class="btn btn-sm btn-icon btn-outline-success" title="Ouvrir dans Google Maps">
                <i class="bi bi-geo-alt"></i>
            </a>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['type_site', 'agents_count', 'actions'])
            ->make(true);
    }

    /**
     * Affiche les détails d'un site spécifique
     */
    public function show($id)
    {
        $user = Auth::guard('portail')->user();

        $site = $user->client->sites()
            ->with(['zone', 'client'])
            ->withCount(['plannings as plannings_actifs_count' => function ($query) {
                $query->whereNull('date_fin');
            }])
            ->findOrFail($id);

        $agents = \App\Models\Employe::with([
            'departement',
            'plannings' => function ($query) use ($id) {
                $query->where('site_id', $id)
                    ->whereNull('date_fin')
                    ->with(['detailsHorizontal.horaire']);
            }
        ])
            ->whereHas('plannings', function ($query) use ($id) {
                $query->where('site_id', $id)
                    ->whereNull('date_fin');
            })
            ->where('etat', 1)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $siteStats = [
            'planningsActifs' => $site->plannings_actifs_count,
            'employesAssignes' => $agents->count(),
        ];

        return view('portail.sites.show', compact('site', 'agents', 'siteStats'));
    }

    /**
     * Récupère les données de géolocalisation pour la carte
     */
    public function getGeoData(Request $request)
    {
        $user = Auth::guard('portail')->user();

        $sites = $user->client->sites()
            ->whereNull('date_arret')
            ->withCount(['plannings as plannings_actifs_count' => function ($query) {
                $query->whereNull('date_fin');
            }])
            ->get();

        // Coordonnées approximatives des régions du Sénégal, utilisées en
        // secours quand un site n'a pas de latitude/longitude renseignées.
        $regionCoords = [
            'Dakar' => [-17.4441, 14.6937],
            'Thiès' => [-16.9536, 14.7906],
            'Diourbel' => [-16.2330, 14.7295],
            'Fatick' => [-16.4110, 14.3390],
            'Kaolack' => [-16.0726, 14.1652],
            'Kaffrine' => [-15.5420, 14.1053],
            'Kédougou' => [-12.1813, 12.5603],
            'Kolda' => [-14.9414, 12.8983],
            'Louga' => [-16.2240, 15.6173],
            'Matam' => [-13.2548, 15.6559],
            'Saint-Louis' => [-16.4818, 16.0326],
            'Sédhiou' => [-15.5569, 12.7080],
            'Tambacounda' => [-13.6730, 13.7713],
            'Ziguinchor' => [-16.2887, 12.5598]
        ];

        $features = [];

        foreach ($sites as $site) {
            $coords = [$site->longitude, $site->latitude];

            if (!$site->longitude || !$site->latitude) {
                if (isset($regionCoords[$site->region])) {
                    $baseCoords = $regionCoords[$site->region];
                    $latOffset = (mt_rand(-100, 100) / 1000);
                    $lngOffset = (mt_rand(-100, 100) / 1000);
                    $coords = [$baseCoords[0] + $lngOffset, $baseCoords[1] + $latOffset];
                }
            }

            $features[] = [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => $coords
                ],
                'properties' => [
                    'id' => $site->id,
                    'nom' => $site->nom_site,
                    'region' => $site->region,
                    'localisation' => $site->localisation,
                    'type_site' => $site->type_site,
                    'contact_nom' => $site->contact_nom,
                    'contact_telephone' => $site->contact_telephone,
                    'date_debut' => $site->date_debut ? $site->date_debut->format('Y-m-d') : null,
                    'zone' => $site->zone ? $site->zone->nom : null,
                    'plannings_actifs_count' => $site->plannings_actifs_count,
                    'numero_rpe' => $site->numero_rpe
                ]
            ];
        }

        $geoJson = [
            'type' => 'FeatureCollection',
            'features' => $features
        ];

        return response()->json($geoJson);
    }

    /**
     * Exporte les sites du client au format CSV
     */
    public function exportCsv(Request $request)
    {
        $user = Auth::guard('portail')->user();

        $sites = $user->client->sites()
            ->whereNull('date_arret')
            ->with(['zone'])
            ->withCount(['plannings as plannings_actifs_count' => function ($query) {
                $query->whereNull('date_fin');
            }])
            ->get();

        $headers = [
            'Nom du site',
            'Type',
            'Région',
            'Localisation',
            'Contact',
            'Téléphone',
            'Zone',
            'Date de début',
            'Nombre d\'agents',
            'Numéro RPE',
            'Coordonnées GPS'
        ];

        $csvContent = implode(',', $headers) . "\n";

        foreach ($sites as $site) {
            $coords = '';
            if ($site->latitude && $site->longitude) {
                $coords = $site->latitude . ',' . $site->longitude;
            }

            $row = [
                '"' . str_replace('"', '""', $site->nom_site) . '"',
                '"' . str_replace('"', '""', ucfirst($site->type_site)) . '"',
                '"' . str_replace('"', '""', $site->region) . '"',
                '"' . str_replace('"', '""', $site->localisation) . '"',
                '"' . str_replace('"', '""', $site->contact_nom) . '"',
                '"' . str_replace('"', '""', $site->contact_telephone) . '"',
                '"' . str_replace('"', '""', $site->zone ? $site->zone->nom : 'N/A') . '"',
                '"' . ($site->date_debut ? $site->date_debut->format('d/m/Y') : 'N/A') . '"',
                '"' . $site->plannings_actifs_count . '"',
                '"' . ($site->numero_rpe ?: 'N/A') . '"',
                '"' . ($coords ?: 'N/A') . '"'
            ];
            $csvContent .= implode(',', $row) . "\n";
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'export_sites_client_');
        file_put_contents($tempFile, $csvContent);

        $clientName = str_replace(' ', '_', $user->client->nomClient);
        $filename = 'sites_' . strtolower($clientName) . '_' . date('Y-m-d') . '.csv';

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ])->deleteFileAfterSend(true);
    }
}
