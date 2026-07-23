<?php

namespace App\Http\Controllers\Direction;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BulletinPaie;
use App\Models\Client;
use App\Models\DemandeAbsenceAdmin;
use App\Models\DemandeExplication;
use App\Models\Departement;
use App\Models\Employe;
use App\Models\Immobilisation;
use App\Models\PortailUser;
use App\Models\Ronde;
use App\Models\SAV\Contrat as ContratSav;
use App\Models\SAV\FicheProgres;
use App\Models\Site;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('direction-dashboard-view');

        $stats = [
            'effectif_actif' => Employe::where('etat', 1)->count(),
            'sites_actifs' => Site::whereNull('date_arret')->count(),
            'clients_actifs' => Client::clientActifs()->count(),
            'absences_en_cours' => DemandeAbsenceAdmin::enCours()->count(),
            'explications_en_attente' => DemandeExplication::enAttente()->count(),
            'masse_salariale_mois' => BulletinPaie::where('mois', now()->month)->where('annee', now()->year)->sum('salaire_brut'),
            'contrats_sav_actifs' => ContratSav::actifs()->count(),
            'contrats_sav_expirant' => ContratSav::expirant(30)->count(),
            'fiches_sav_en_cours' => FicheProgres::whereNotIn('statut', ['cloture', 'non_fonde'])->count(),
            'comptes_portail_actifs' => PortailUser::where('status', 'active')->count(),
            'articles_sous_stock' => Article::whereRaw('stock_actuel <= stock_minimum')->count(),
            'valeur_immobilisations' => Immobilisation::sum('valeur_acquisition'),
        ];

        // Rondes sur les 7 derniers jours
        $rondes7j = Ronde::selectRaw("DATE(date_debut) as jour, COUNT(*) as total, SUM(statut = 'complete') as completes")
            ->where('date_debut', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('jour')
            ->orderBy('jour')
            ->get()
            ->keyBy('jour');

        $rondesParJour = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $key = $date->toDateString();
            $rondesParJour[] = [
                'jour' => $date->format('d/m'),
                'total' => (int) ($rondes7j[$key]->total ?? 0),
                'completes' => (int) ($rondes7j[$key]->completes ?? 0),
            ];
        }

        $employesParDepartement = Departement::withCount(['employes' => fn ($q) => $q->where('etat', 1)])->get();

        $absencesRecentes = DemandeAbsenceAdmin::with('employe')->orderByDesc('created_at')->limit(5)->get();
        $contratsSavExpirantListe = ContratSav::with('client')->expirant(30)->orderBy('date_fin')->limit(5)->get();
        $fichesSavRecentes = FicheProgres::with('client')->orderByDesc('created_at')->limit(5)->get();

        return view('direction.dashboard', compact(
            'stats', 'rondesParJour', 'employesParDepartement',
            'absencesRecentes', 'contratsSavExpirantListe', 'fichesSavRecentes'
        ));
    }
}
