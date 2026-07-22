<?php

namespace App\Http\Controllers\SAV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SAV\FicheProgres;
use App\Models\SAV\Contrat;
use App\Models\SAV\ClientInteraction;
use App\Models\SAV\Maintenance;
use App\Models\SAV\Intervention;
use App\Models\SAV\ClientAsset;
use App\Models\SAV\FicheProgresAction;
use App\Models\Client;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('sav-dashboard-view');

        $stats = [
            'total_clients' => Client::where('type_client', 'client_actif')->count(),
            'clients_vip' => Client::where('priorite', 'vip')->count(),

            'fiches_nouvelles' => FicheProgres::where('statut', 'nouveau')->count(),
            'fiches_en_cours' => FicheProgres::whereNotIn('statut', ['cloture', 'non_fonde'])->count(),
            'fiches_cloturees_mois' => FicheProgres::where('statut', 'cloture')->whereMonth('date_cloture', now()->month)->count(),

            'contrats_actifs' => Contrat::where('statut', 'actif')->count(),
            'contrats_expirant' => Contrat::expirant(30)->count(),

            'interactions_semaine' => ClientInteraction::where('created_at', '>=', now()->subDays(7))->count(),
            'rappels_a_faire' => ClientInteraction::aRappeler()->count(),

            'maintenances_mois' => Maintenance::whereMonth('date_prevue', now()->month)->count(),
            'interventions_realisees' => Intervention::where('statut', 'termine')->count(),
            'total_appareils' => ClientAsset::count(),
        ];

        $fichesParType = FicheProgres::selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');
        $fichesParStatut = FicheProgres::selectRaw('statut, count(*) as total')->groupBy('statut')->pluck('total', 'statut');

        $fichesRecentes = FicheProgres::with(['client', 'createur'])->orderBy('created_at', 'desc')->limit(10)->get();
        $contratsExpirant = Contrat::with('client')->expirant(30)->orderBy('date_fin')->limit(10)->get();
        $actionsEnRetard = FicheProgresAction::with(['ficheProgres', 'responsable'])->enRetard()->limit(10)->get();
        $timeline = ClientInteraction::with(['client', 'user'])->orderBy('created_at', 'desc')->limit(20)->get();

        return view('sav.dashboard.index', compact(
            'stats', 'fichesParType', 'fichesParStatut', 'fichesRecentes', 'contratsExpirant', 'actionsEnRetard', 'timeline'
        ));
    }

    public function statistiques(Request $request)
    {
        $this->authorize('sav-dashboard-view');

        $periode = $request->get('periode', 6);

        $labels = [];
        $dataFiches = [];
        $dataContrats = [];

        for ($i = $periode - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $dataFiches[] = FicheProgres::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();
            $dataContrats[] = Contrat::whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();
        }

        return response()->json(['labels' => $labels, 'fiches' => $dataFiches, 'contrats' => $dataContrats]);
    }
}
