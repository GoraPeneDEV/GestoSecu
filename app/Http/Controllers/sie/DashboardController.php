<?php

namespace App\Http\Controllers\sie;

use App\Http\Controllers\Controller;
use App\Models\Ronde;
use App\Models\RondeScan;
use App\Models\PlanningRonde;
use App\Models\PointControle;
use App\Models\Site;
use App\Models\SupervisorVisit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('ronde-view');

        $stats = [
            'rondes_aujourdhui' => Ronde::whereDate('date_debut', today())->count(),
            'rondes_en_cours' => Ronde::where('statut', 'en_cours')->count(),
            'rondes_semaine' => Ronde::where('date_debut', '>=', now()->startOfWeek())->count(),
            'anomalies_ouvertes' => RondeScan::where('anomalie', true)->count(),
            'points_controle_actifs' => PointControle::where('actif', true)->count(),
            'visites_supervision_semaine' => SupervisorVisit::where('created_at', '>=', now()->startOfWeek())->count(),
        ];

        $rondesRecentes = Ronde::with(['agent', 'planningRonde.site', 'scans'])
            ->orderByDesc('date_debut')
            ->limit(10)
            ->get();

        $visitesRecentes = SupervisorVisit::with(['site', 'supervisor'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('sie.dashboard.index', compact('stats', 'rondesRecentes', 'visitesRecentes'));
    }

    public function rapport(Request $request)
    {
        $this->authorize('ronde-view');

        $dateDebut = $request->input('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));
        $siteId = $request->input('site_id');

        $query = Ronde::with(['agent', 'planningRonde.site', 'scans'])
            ->whereDate('date_debut', '>=', $dateDebut)
            ->whereDate('date_debut', '<=', $dateFin);

        if ($siteId) {
            $query->whereHas('planningRonde', fn($q) => $q->where('site_id', $siteId));
        }

        $rondes = $query->get();

        $totalRondes = $rondes->count();
        $rondesTerminees = $rondes->where('statut', 'terminee')->count();
        $totalAnomalies = $rondes->sum(fn($r) => $r->scans->where('anomalie', true)->count());
        $tauxCompletion = $totalRondes > 0 ? round(($rondesTerminees / $totalRondes) * 100) : 0;

        $parAgent = $rondes->groupBy('agent_id')->map(function ($group) {
            $agent = $group->first()->agent;
            return [
                'agent' => $agent ? $agent->prenom . ' ' . $agent->nom : 'Inconnu',
                'total' => $group->count(),
                'terminees' => $group->where('statut', 'terminee')->count(),
                'anomalies' => $group->sum(fn($r) => $r->scans->where('anomalie', true)->count()),
            ];
        })->values();

        $sites = Site::orderBy('nom_site')->get();

        return view('sie.rapport.index', compact(
            'rondes', 'totalRondes', 'rondesTerminees', 'totalAnomalies', 'tauxCompletion',
            'parAgent', 'sites', 'dateDebut', 'dateFin', 'siteId'
        ));
    }

    public function rapportPdf(Request $request)
    {
        $this->authorize('ronde-view');

        $dateDebut = $request->input('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));
        $siteId = $request->input('site_id');

        $query = Ronde::with(['agent', 'planningRonde.site', 'scans'])
            ->whereDate('date_debut', '>=', $dateDebut)
            ->whereDate('date_debut', '<=', $dateFin);

        if ($siteId) {
            $query->whereHas('planningRonde', fn($q) => $q->where('site_id', $siteId));
        }

        $rondes = $query->get();

        $totalRondes = $rondes->count();
        $rondesTerminees = $rondes->where('statut', 'terminee')->count();
        $totalAnomalies = $rondes->sum(fn($r) => $r->scans->where('anomalie', true)->count());
        $tauxCompletion = $totalRondes > 0 ? round(($rondesTerminees / $totalRondes) * 100) : 0;

        $pdf = Pdf::loadView('sie.rapport.pdf', compact('rondes', 'totalRondes', 'rondesTerminees', 'totalAnomalies', 'tauxCompletion', 'dateDebut', 'dateFin'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('rapport-rondes-' . $dateDebut . '-au-' . $dateFin . '.pdf');
    }
}
