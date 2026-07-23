<?php

namespace App\Http\Controllers\Portail;

use App\Models\Ronde;
use App\Models\Site;
use App\Models\Employe;
use App\Models\SAV\ClientAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class RapportController extends BasePortailController
{
    public function index()
    {
        return view('portail.rapports.index');
    }

    public function sites(Request $request)
    {
        $this->authorize('portail-site-view');
        [$sites, $dateDebut, $dateFin] = $this->sitesData($request);

        return view('portail.rapports.sites', compact('sites', 'dateDebut', 'dateFin'));
    }

    public function sitesPdf(Request $request)
    {
        $this->authorize('portail-site-view');
        [$sites, $dateDebut, $dateFin] = $this->sitesData($request);

        $pdf = Pdf::loadView('portail.rapports.pdf.sites', compact('sites', 'dateDebut', 'dateFin'))->setPaper('a4', 'landscape');

        return $pdf->download('rapport-sites-' . $dateDebut . '-au-' . $dateFin . '.pdf');
    }

    public function agents(Request $request)
    {
        $this->authorize('portail-agent-view');
        [$agents, $dateDebut, $dateFin] = $this->agentsData($request);

        return view('portail.rapports.agents', compact('agents', 'dateDebut', 'dateFin'));
    }

    public function agentsPdf(Request $request)
    {
        $this->authorize('portail-agent-view');
        [$agents, $dateDebut, $dateFin] = $this->agentsData($request);

        $pdf = Pdf::loadView('portail.rapports.pdf.agents', compact('agents', 'dateDebut', 'dateFin'))->setPaper('a4', 'landscape');

        return $pdf->download('rapport-agents-' . $dateDebut . '-au-' . $dateFin . '.pdf');
    }

    public function rondes(Request $request)
    {
        $this->authorize('portail-ronde-view');
        [$rondes, $totalRondes, $rondesTerminees, $totalAnomalies, $tauxCompletion, $sites, $dateDebut, $dateFin, $siteId] = $this->rondesData($request);

        return view('portail.rapports.rondes', compact(
            'rondes', 'totalRondes', 'rondesTerminees', 'totalAnomalies', 'tauxCompletion', 'sites', 'dateDebut', 'dateFin', 'siteId'
        ));
    }

    public function rondesPdf(Request $request)
    {
        $this->authorize('portail-ronde-view');
        [$rondes, $totalRondes, $rondesTerminees, $totalAnomalies, $tauxCompletion, , $dateDebut, $dateFin] = $this->rondesData($request);

        $pdf = Pdf::loadView('portail.rapports.pdf.rondes', compact('rondes', 'totalRondes', 'rondesTerminees', 'totalAnomalies', 'tauxCompletion', 'dateDebut', 'dateFin'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('rapport-rondes-' . $dateDebut . '-au-' . $dateFin . '.pdf');
    }

    public function parc(Request $request)
    {
        [$assets, $parStatut] = $this->parcData();

        return view('portail.rapports.parc', compact('assets', 'parStatut'));
    }

    public function parcPdf(Request $request)
    {
        [$assets, $parStatut] = $this->parcData();

        $pdf = Pdf::loadView('portail.rapports.pdf.parc', compact('assets', 'parStatut'))->setPaper('a4', 'landscape');

        return $pdf->download('rapport-parc-' . now()->format('Y-m-d') . '.pdf');
    }

    private function sitesData(Request $request): array
    {
        $client = Auth::guard('portail')->user()->client;

        $dateDebut = $request->input('date_debut', now()->startOfYear()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));

        $sites = $client->sites()
            ->whereDate('date_debut', '>=', $dateDebut)
            ->whereDate('date_debut', '<=', $dateFin)
            ->orderByDesc('date_debut')
            ->get();

        return [$sites, $dateDebut, $dateFin];
    }

    private function agentsData(Request $request): array
    {
        $client = Auth::guard('portail')->user()->client;

        $dateDebut = $request->input('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));

        $agents = Employe::select([
            'employe.id', 'employe.matricule', 'employe.prenom', 'employe.nom', 'employe.fonction',
        ])
            ->join('plannings', 'employe.id', '=', 'plannings.employe_id')
            ->join('sites', 'plannings.site_id', '=', 'sites.id')
            ->where('sites.client_id', $client->id)
            ->where('employe.etat', 1)
            ->whereNull('plannings.date_fin')
            ->with(['plannings' => function ($query) use ($client) {
                $query->whereHas('site', fn ($q) => $q->where('client_id', $client->id))
                    ->whereNull('date_fin')
                    ->with('site');
            }])
            ->distinct()
            ->get();

        return [$agents, $dateDebut, $dateFin];
    }

    private function rondesData(Request $request): array
    {
        $client = Auth::guard('portail')->user()->client;

        $dateDebut = $request->input('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));
        $siteId = $request->input('site_id');

        $query = Ronde::with(['agent', 'planningRonde.site', 'scans'])
            ->whereHas('planningRonde.site', fn ($q) => $q->where('client_id', $client->id))
            ->whereDate('date_debut', '>=', $dateDebut)
            ->whereDate('date_debut', '<=', $dateFin);

        if ($siteId) {
            $query->whereHas('planningRonde', fn ($q) => $q->where('site_id', $siteId));
        }

        $rondes = $query->get();

        $totalRondes = $rondes->count();
        $rondesTerminees = $rondes->where('statut', 'terminee')->count();
        $totalAnomalies = $rondes->sum(fn ($r) => $r->scans->where('anomalie', true)->count());
        $tauxCompletion = $totalRondes > 0 ? round(($rondesTerminees / $totalRondes) * 100) : 0;

        $sites = Site::where('client_id', $client->id)->orderBy('nom_site')->get();

        return [$rondes, $totalRondes, $rondesTerminees, $totalAnomalies, $tauxCompletion, $sites, $dateDebut, $dateFin, $siteId];
    }

    private function parcData(): array
    {
        $client = Auth::guard('portail')->user()->client;
        $siteIds = $client->sites()->pluck('id');

        $assets = ClientAsset::with('site')->whereIn('site_id', $siteIds)->orderBy('type')->get();

        $parStatut = ClientAsset::whereIn('site_id', $siteIds)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [$assets, $parStatut];
    }
}
