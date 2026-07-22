<?php

namespace App\Http\Controllers\Portail;

use App\Models\Site;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class AgentController extends BasePortailController
{
    /**
     * Affiche la liste des agents assignés aux sites du client
     */
    public function index()
    {
        $this->authorize('portail-agent-view');
        $user = Auth::guard('portail')->user();

        $stats = $this->getAgentStats($user->client_id);

        $sites = Site::where('client_id', $user->client_id)->orderBy('nom_site')->get();

        return view('portail.agents.index', compact('stats', 'sites'));
    }

    /**
     * API pour DataTables - Liste des agents
     */
    public function getAgents(Request $request)
    {
        $user = Auth::guard('portail')->user();

        $query = Employe::select([
            'employe.id',
            'employe.matricule',
            'employe.prenom',
            'employe.nom',
            'employe.telephone',
            'employe.photo',
            'employe.fonction',
            'employe.date_debut',
            'departements.nom as departement_nom'
        ])
            ->join('departements', 'employe.id_departement', '=', 'departements.id')
            ->join('plannings', 'employe.id', '=', 'plannings.employe_id')
            ->join('sites', 'plannings.site_id', '=', 'sites.id')
            ->where('sites.client_id', $user->client_id)
            ->where('employe.etat', 1)
            ->whereNull('plannings.date_fin')
            ->with(['plannings' => function ($query) use ($user) {
                $query->whereHas('site', function ($q) use ($user) {
                    $q->where('client_id', $user->client_id);
                })
                    ->whereNull('date_fin')
                    ->with('site');
            }])
            ->distinct();

        if ($request->filled('site_id')) {
            $query->where('sites.id', $request->site_id);
        }

        if ($request->filled('departement')) {
            $query->where('departements.nom', $request->departement);
        }

        return DataTables::of($query)
            ->addColumn('agent_info', function ($agent) {
                $photo = $agent->photo
                    ? '<img src="' . asset('storage/' . $agent->photo) . '" class="rounded-circle me-2" width="32" height="32">'
                    : '<div class="avatar me-2"><span class="avatar-initial rounded-circle bg-primary">' . substr($agent->prenom, 0, 1) . substr($agent->nom, 0, 1) . '</span></div>';

                return '
                    <div class="d-flex justify-content-start align-items-center">
                        <div class="avatar-wrapper">
                            ' . $photo . '
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-medium">' . $agent->prenom . ' ' . $agent->nom . '</span>
                            <small class="text-muted">' . $agent->matricule . '</small>
                        </div>
                    </div>';
            })
            ->addColumn('sites_assignes', function ($agent) {
                $sites = $agent->plannings->pluck('site.nom_site')->unique()->take(3);
                $html = '';

                foreach ($sites as $site) {
                    $html .= '<span class="badge bg-info me-1">' . $site . '</span>';
                }

                if ($agent->plannings->count() > 3) {
                    $html .= '<span class="badge bg-secondary">+' . ($agent->plannings->count() - 3) . '</span>';
                }

                return $html;
            })
            ->addColumn('telephone', function ($agent) {
                return $agent->telephone ?: 'N/A';
            })
            ->editColumn('departement_nom', function ($agent) {
                return '<span class="badge bg-secondary">' . $agent->departement_nom . '</span>';
            })
            ->addColumn('actions', function ($agent) {
                return '
                    <div class="d-inline-block">
                        <a href="' . route('portail.agents.show', $agent->id) . '"
                           class="btn btn-sm btn-icon btn-primary me-1" title="Voir détails">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="' . route('portail.agents.planning', $agent->id) . '"
                           class="btn btn-sm btn-icon btn-warning" title="Voir planning">
                            <i class="bi bi-calendar"></i>
                        </a>
                    </div>';
            })
            ->rawColumns(['agent_info', 'sites_assignes', 'departement_nom', 'actions'])
            ->make(true);
    }

    /**
     * Affiche les détails d'un agent avec ses documents
     */
    public function show($id)
    {
        $user = Auth::guard('portail')->user();

        $agent = Employe::with([
            'departement',
            'plannings' => function ($query) use ($user) {
                $query->whereHas('site', function ($q) use ($user) {
                    $q->where('client_id', $user->client_id);
                })
                    ->whereNull('date_fin')
                    ->with(['site', 'detailsHorizontal.horaire']);
            },
            'documents' => function ($query) {
                $query->with('ajoutePar')->orderBy('created_at', 'desc');
            }
        ])
            ->whereHas('plannings.site', function ($query) use ($user) {
                $query->where('client_id', $user->client_id);
            })
            ->findOrFail($id);

        return view('portail.agents.show', compact('agent'));
    }

    /**
     * Affiche le planning d'un agent
     */
    public function planning($id)
    {
        $user = Auth::guard('portail')->user();

        $agent = Employe::with(['departement', 'plannings' => function ($query) use ($user) {
            $query->whereHas('site', function ($q) use ($user) {
                $q->where('client_id', $user->client_id);
            })
                ->whereNull('date_fin')
                ->with(['site', 'detailsHorizontal.horaire']);
        }])
            ->whereHas('plannings.site', function ($query) use ($user) {
                $query->where('client_id', $user->client_id);
            })
            ->findOrFail($id);

        return view('portail.agents.planning', compact('agent'));
    }

    /**
     * Télécharge un document spécifique d'un agent
     */
    public function downloadDocument($agentId, $documentId)
    {
        $user = Auth::guard('portail')->user();

        $agent = Employe::whereHas('plannings.site', function ($query) use ($user) {
            $query->where('client_id', $user->client_id);
        })->findOrFail($agentId);

        $document = $agent->documents()->findOrFail($documentId);

        $filePaths = [
            public_path('storage/' . $document->chemin_fichier),
            storage_path('app/public/' . $document->chemin_fichier)
        ];

        foreach ($filePaths as $filePath) {
            if (file_exists($filePath)) {
                return response()->download($filePath, $document->nom_fichier);
            }
        }

        abort(404, 'Fichier non trouvé');
    }

    /**
     * Affiche un document spécifique d'un agent
     */
    public function viewDocument($agentId, $documentId)
    {
        $user = Auth::guard('portail')->user();

        $agent = Employe::whereHas('plannings.site', function ($query) use ($user) {
            $query->where('client_id', $user->client_id);
        })->findOrFail($agentId);

        $document = $agent->documents()->findOrFail($documentId);

        $filePaths = [
            public_path('storage/' . $document->chemin_fichier),
            storage_path('app/public/' . $document->chemin_fichier)
        ];

        foreach ($filePaths as $filePath) {
            if (file_exists($filePath)) {
                return response()->file($filePath);
            }
        }

        abort(404, 'Fichier non trouvé');
    }

    /**
     * Récupérer les statistiques des agents
     */
    private function getAgentStats($clientId)
    {
        $totalAgents = Employe::join('plannings', 'employe.id', '=', 'plannings.employe_id')
            ->join('sites', 'plannings.site_id', '=', 'sites.id')
            ->where('sites.client_id', $clientId)
            ->where('employe.etat', 1)
            ->whereNull('plannings.date_fin')
            ->distinct('employe.id')
            ->count();

        $sitesCouverts = Site::where('client_id', $clientId)
            ->whereHas('plannings', function ($query) {
                $query->whereNull('date_fin');
            })
            ->count();

        return [
            'totalAgents' => $totalAgents,
            'sitesCouverts' => $sitesCouverts
        ];
    }
}
