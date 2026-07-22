<?php

namespace App\Http\Controllers\Portail;

use App\Models\Site;
use App\Models\Ronde;
use App\Models\RondeScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RondeController extends BasePortailController
{
    /**
     * Affiche la liste des rondes pour les sites du client
     */
    public function index()
    {
        $this->authorize('portail-ronde-view');
        $user = Auth::guard('portail')->user();

        $stats = $this->getRondeStats($user->client_id);

        $sites = Site::where('client_id', $user->client_id)->orderBy('nom_site')->get();

        return view('portail.rondes.index', compact('stats', 'sites'));
    }

    /**
     * API pour DataTables - Liste des rondes
     */
    public function getRondes(Request $request)
    {
        $user = Auth::guard('portail')->user();

        $query = Ronde::select([
            'rondes.id',
            'rondes.date_debut',
            'rondes.date_fin',
            'rondes.statut',
            'rondes.commentaire',
            'sites.nom_site',
            'sites.id as site_id',
            'employe.prenom as agent_prenom',
            'employe.nom as agent_nom',
            'employe.matricule as agent_matricule',
            'plannings_ronde.nom as planning_nom',
            'plannings_ronde.frequence'
        ])
            ->join('plannings_ronde', 'rondes.planning_ronde_id', '=', 'plannings_ronde.id')
            ->join('sites', 'plannings_ronde.site_id', '=', 'sites.id')
            ->join('employe', 'rondes.agent_id', '=', 'employe.id')
            ->where('sites.client_id', $user->client_id)
            ->orderBy('rondes.date_debut', 'desc')
            ->with(['scans' => function ($query) {
                $query->with('pointControle');
            }]);

        if ($request->filled('site_id')) {
            $query->where('sites.id', $request->site_id);
        }

        if ($request->filled('statut')) {
            $query->where('rondes.statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('rondes.date_debut', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('rondes.date_debut', '<=', $request->date_fin);
        }

        if ($request->filled('periode')) {
            $periode = $request->periode;
            switch ($periode) {
                case 'aujourd_hui':
                    $query->whereDate('rondes.date_debut', today());
                    break;
                case 'cette_semaine':
                    $query->whereBetween('rondes.date_debut', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'ce_mois':
                    $query->whereMonth('rondes.date_debut', Carbon::now()->month)
                        ->whereYear('rondes.date_debut', Carbon::now()->year);
                    break;
            }
        }

        return DataTables::of($query)
            ->addColumn('ronde_info', function ($ronde) {
                $dateDebut = Carbon::parse($ronde->date_debut);
                $dateFin = $ronde->date_fin ? Carbon::parse($ronde->date_fin) : null;

                return '
          <div class="d-flex flex-column">
            <span class="fw-medium">' . $ronde->planning_nom . '</span>
            <small class="text-muted">
              <i class="bi bi-calendar me-1"></i>' . $dateDebut->format('d/m/Y à H:i') . '
            </small>
            ' . ($dateFin ? '<small class="text-success"><i class="bi bi-check-circle me-1"></i>Terminée à ' . $dateFin->format('H:i') . '</small>' : '') . '
          </div>';
            })
            ->addColumn('site_agent', function ($ronde) {
                return '
          <div class="d-flex flex-column">
            <span class="fw-medium text-primary">' . $ronde->nom_site . '</span>
            <small class="text-muted">
              <i class="bi bi-person me-1"></i>' . $ronde->agent_prenom . ' ' . $ronde->agent_nom . '
              <span class="badge bg-secondary ms-1">' . $ronde->agent_matricule . '</span>
            </small>
          </div>';
            })
            ->addColumn('progression', function ($ronde) {
                $totalPoints = $ronde->scans->count();
                $pointsAnomalies = $ronde->scans->where('anomalie', true)->count();

                if ($totalPoints === 0) {
                    return '<span class="badge bg-warning">Aucun point</span>';
                }

                $couleur = $pointsAnomalies > 0 ? 'danger' : 'success';
                $icone = $pointsAnomalies > 0 ? 'bi-exclamation-triangle' : 'bi-check-circle';

                return '
          <div class="d-flex flex-column">
            <span class="badge bg-' . $couleur . '">
              <i class="bi ' . $icone . ' me-1"></i>' . $totalPoints . ' point(s) contrôlé(s)
            </span>
            ' . ($pointsAnomalies > 0 ? '<small class="text-danger mt-1">' . $pointsAnomalies . ' anomalie(s)</small>' : '') . '
          </div>';
            })
            ->editColumn('statut', function ($ronde) {
                $badges = [
                    'en_cours' => ['class' => 'bg-warning', 'icon' => 'bi-clock', 'text' => 'En cours'],
                    'terminee' => ['class' => 'bg-success', 'icon' => 'bi-check-circle', 'text' => 'Terminée']
                ];

                $badge = $badges[$ronde->statut] ?? ['class' => 'bg-secondary', 'icon' => 'bi-question-circle', 'text' => $ronde->statut];

                return '<span class="badge ' . $badge['class'] . '">
          <i class="bi ' . $badge['icon'] . ' me-1"></i>' . $badge['text'] . '
        </span>';
            })
            ->addColumn('duree', function ($ronde) {
                if (!$ronde->date_fin) {
                    $duree = Carbon::parse($ronde->date_debut)->diffForHumans(null, true);
                    return '<small class="text-warning">' . $duree . ' (en cours)</small>';
                }

                $duree = Carbon::parse($ronde->date_debut)->diff(Carbon::parse($ronde->date_fin));
                $dureeText = '';

                if ($duree->h > 0) $dureeText .= $duree->h . 'h ';
                if ($duree->i > 0) $dureeText .= $duree->i . 'min';

                return '<small class="text-success">' . ($dureeText ?: '< 1min') . '</small>';
            })
            ->addColumn('actions', function ($ronde) {
                return '
          <div class="d-inline-block">
            <a href="' . route('portail.rondes.show', $ronde->id) . '"
               class="btn btn-sm btn-icon btn-primary me-1" title="Voir détails">
                <i class="bi bi-eye"></i>
            </a>
            ' . ($ronde->scans->where('anomalie', true)->count() > 0 ?
                    '<a href="' . route('portail.rondes.export-anomalies', $ronde->id) . '"
                 class="btn btn-sm btn-icon btn-danger" title="Exporter anomalies">
                  <i class="bi bi-file-earmark-arrow-down"></i>
              </a>' : '') . '
          </div>';
            })
            ->order(function ($query) {
                $query->orderBy('rondes.date_debut', 'desc');
            })
            ->rawColumns(['ronde_info', 'site_agent', 'progression', 'statut', 'duree', 'actions'])
            ->make(true);
    }

    /**
     * Affiche les détails d'une ronde
     */
    public function show($id)
    {
        $user = Auth::guard('portail')->user();

        $ronde = Ronde::with([
            'planningRonde.site',
            'planningRonde.pointsControle',
            'agent.departement',
            'scans' => function ($query) {
                $query->with('pointControle')->orderBy('date_scan');
            }
        ])
            ->whereHas('planningRonde.site', function ($query) use ($user) {
                $query->where('client_id', $user->client_id);
            })
            ->findOrFail($id);

        return view('portail.rondes.show', compact('ronde'));
    }

    /**
     * Statistiques des rondes pour le dashboard
     */
    public function getStats(Request $request)
    {
        $user = Auth::guard('portail')->user();
        $periode = $request->get('periode', 'ce_mois');

        $stats = $this->getRondeStats($user->client_id, $periode);

        return response()->json($stats);
    }

    /**
     * Export des anomalies d'une ronde en PDF
     */
    public function exportAnomalies($id)
    {
        $user = Auth::guard('portail')->user();

        $ronde = Ronde::with([
            'scans.pointControle',
            'planningRonde.site',
            'agent'
        ])
            ->whereHas('planningRonde.site', function ($query) use ($user) {
                $query->where('client_id', $user->client_id);
            })
            ->findOrFail($id);

        $anomalies = $ronde->scans()->where('anomalie', true)->with('pointControle')->get();

        $pdf = Pdf::loadView('portail.rondes.pdf.anomalies', compact('ronde', 'anomalies', 'user'));

        $filename = 'anomalies_ronde_' . $ronde->id . '_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Récupérer les statistiques des rondes
     */
    private function getRondeStats($clientId, $periode = 'ce_mois')
    {
        $query = Ronde::join('plannings_ronde', 'rondes.planning_ronde_id', '=', 'plannings_ronde.id')
            ->join('sites', 'plannings_ronde.site_id', '=', 'sites.id')
            ->where('sites.client_id', $clientId);

        switch ($periode) {
            case 'aujourd_hui':
                $query->whereDate('rondes.date_debut', today());
                break;
            case 'cette_semaine':
                $query->whereBetween('rondes.date_debut', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'ce_mois':
            default:
                $query->whereMonth('rondes.date_debut', Carbon::now()->month)
                    ->whereYear('rondes.date_debut', Carbon::now()->year);
                break;
        }

        $totalRondes = (clone $query)->count();
        $rondesTerminees = (clone $query)->where('rondes.statut', 'terminee')->count();
        $rondesEnCours = (clone $query)->where('rondes.statut', 'en_cours')->count();

        $anomalies = RondeScan::join('rondes', 'ronde_scans.ronde_id', '=', 'rondes.id')
            ->join('plannings_ronde', 'rondes.planning_ronde_id', '=', 'plannings_ronde.id')
            ->join('sites', 'plannings_ronde.site_id', '=', 'sites.id')
            ->where('sites.client_id', $clientId)
            ->where('ronde_scans.anomalie', true);

        switch ($periode) {
            case 'aujourd_hui':
                $anomalies->whereDate('rondes.date_debut', today());
                break;
            case 'cette_semaine':
                $anomalies->whereBetween('rondes.date_debut', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'ce_mois':
            default:
                $anomalies->whereMonth('rondes.date_debut', Carbon::now()->month)
                    ->whereYear('rondes.date_debut', Carbon::now()->year);
                break;
        }

        $totalAnomalies = $anomalies->count();

        $sitesAvecRondes = Site::where('client_id', $clientId)
            ->whereHas('planningsRonde.rondes', function ($query) use ($periode) {
                switch ($periode) {
                    case 'aujourd_hui':
                        $query->whereDate('date_debut', today());
                        break;
                    case 'cette_semaine':
                        $query->whereBetween('date_debut', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'ce_mois':
                    default:
                        $query->whereMonth('date_debut', Carbon::now()->month)
                            ->whereYear('date_debut', Carbon::now()->year);
                        break;
                }
            })
            ->count();

        return [
            'totalRondes' => $totalRondes,
            'rondesTerminees' => $rondesTerminees,
            'rondesEnCours' => $rondesEnCours,
            'totalAnomalies' => $totalAnomalies,
            'sitesAvecRondes' => $sitesAvecRondes,
            'tauxReussite' => $totalRondes > 0 ? round((($totalRondes - $totalAnomalies) / $totalRondes) * 100, 1) : 100
        ];
    }
}
