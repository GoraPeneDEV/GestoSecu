<?php

namespace App\Http\Controllers\Portail;

use App\Models\SAV\ClientAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ParcController extends BasePortailController
{
    /**
     * Affiche la liste des équipements / parcs du client connecté
     */
    public function index()
    {
        $user = Auth::guard('portail')->user();

        $siteIds = $user->client->sites()->pluck('id');

        $stats = [
            'totalEquipements' => ClientAsset::whereIn('site_id', $siteIds)->count(),
            'equipementsActifs' => ClientAsset::whereIn('site_id', $siteIds)->where('status', 'fonctionnel')->count(),
            'equipementsMaintenance' => ClientAsset::whereIn('site_id', $siteIds)->whereIn('status', ['panne', 'maintenance_requise'])->count(),
            'equipementsHS' => ClientAsset::whereIn('site_id', $siteIds)->where('status', 'hors_service')->count(),
            'totalSitesEquipes' => ClientAsset::whereIn('site_id', $siteIds)->distinct('site_id')->count('site_id'),
        ];

        $parcsParType = ClientAsset::whereIn('site_id', $siteIds)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        return view('portail.parc.index', compact('stats', 'parcsParType'));
    }

    /**
     * Récupère les équipements pour DataTables (filtré par client)
     */
    public function getAssets(Request $request)
    {
        $user = Auth::guard('portail')->user();
        $siteIds = $user->client->sites()->pluck('id');

        $assets = ClientAsset::with(['site'])
            ->whereIn('site_id', $siteIds);

        if ($request->filled('type')) {
            $assets->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $assets->where('status', $request->status);
        }

        if ($request->filled('site_id')) {
            $assets->where('site_id', $request->site_id);
        }

        return DataTables::of($assets)
            ->editColumn('installation_date', function ($asset) {
                return $asset->installation_date ? $asset->installation_date->format('d/m/Y') : '-';
            })
            ->addColumn('site_nom', function ($asset) {
                return $asset->site ? $asset->site->nom_site : 'Non défini';
            })
            ->editColumn('status', function ($asset) {
                $badges = [
                    'fonctionnel' => 'bg-success',
                    'maintenance_requise' => 'bg-warning',
                    'panne' => 'bg-danger',
                    'hors_service' => 'bg-secondary'
                ];

                $badge = $badges[$asset->status] ?? 'bg-primary';
                $statusText = str_replace('_', ' ', ucfirst($asset->status));
                return '<span class="badge ' . $badge . '">' . $statusText . '</span>';
            })
            ->addColumn('actions', function ($asset) {
                return '
          <div class="d-inline-block text-nowrap">
            <a href="' . route('portail.parc.show', $asset->id) . '"
               class="btn btn-sm btn-icon btn-outline-primary" title="Voir détails">
                <i class="bi bi-eye"></i>
            </a>
          </div>';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    /**
     * Affiche les détails d'un équipement spécifique
     */
    public function show($id)
    {
        $user = Auth::guard('portail')->user();
        $siteIds = $user->client->sites()->pluck('id');

        $asset = ClientAsset::with(['site', 'interventions' => function ($q) {
            $q->orderBy('date_intervention', 'desc');
        }])
            ->whereIn('site_id', $siteIds)
            ->findOrFail($id);

        return view('portail.parc.show', compact('asset'));
    }
}
