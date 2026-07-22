<?php

namespace App\Http\Controllers\Paie;

use App\Http\Controllers\Controller;
use App\Models\BulletinPaie;
use App\Models\Employe;
use App\Models\VariablePaie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardPaieController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('paie-dashboard-view');

        $moisActuel = now()->month;
        $anneeActuelle = now()->year;

        // KPIs principaux
        $stats = $this->getMainStats($moisActuel, $anneeActuelle);

        // Données pour graphiques
        $evolutionMasseSalariale = $this->getEvolutionMasseSalariale($anneeActuelle);
        $repartitionCotisations = $this->getRepartitionCotisations($moisActuel, $anneeActuelle);

        return view('paie.dashboard.index', compact(
            'stats',
            'evolutionMasseSalariale',
            'repartitionCotisations',
            'moisActuel',
            'anneeActuelle'
        ));
    }

    /**
     * Récupérer les statistiques principales
     */
    private function getMainStats(int $mois, int $annee): array
    {
        $bulletinsMois = BulletinPaie::where('mois', $mois)
            ->where('annee', $annee)
            ->whereIn('statut', [BulletinPaie::STATUT_VALIDE, BulletinPaie::STATUT_ENVOYE, BulletinPaie::STATUT_ARCHIVE]);

        $totalBrut = $bulletinsMois->sum('salaire_brut');
        $totalNet = $bulletinsMois->sum('salaire_net_a_payer');
        $totalCotisationsSalariales = $bulletinsMois->sum('total_cotisations_salariales');
        $totalCotisationsPatronales = $bulletinsMois->sum('total_cotisations_patronales');
        $totalIR = $bulletinsMois->sum('impot_revenu');
        $nbEmployes = $bulletinsMois->distinct('employe_id')->count();

        // Comparaison avec le mois précédent
        $moisPrecedent = $mois - 1;
        $anneePrecedente = $annee;
        if ($moisPrecedent < 1) {
            $moisPrecedent = 12;
            $anneePrecedente--;
        }

        $totalBrutPrecedent = BulletinPaie::where('mois', $moisPrecedent)
            ->where('annee', $anneePrecedente)
            ->whereIn('statut', [BulletinPaie::STATUT_VALIDE, BulletinPaie::STATUT_ENVOYE, BulletinPaie::STATUT_ARCHIVE])
            ->sum('salaire_brut');

        $evolutionPourcentage = $totalBrutPrecedent > 0
            ? (($totalBrut - $totalBrutPrecedent) / $totalBrutPrecedent) * 100
            : 0;

        return [
            'masse_salariale_brute' => $totalBrut,
            'masse_salariale_nette' => $totalNet,
            'total_cotisations_salariales' => $totalCotisationsSalariales,
            'total_cotisations_patronales' => $totalCotisationsPatronales,
            'total_charges_patronales' => $totalCotisationsPatronales,
            'total_impots' => $totalIR,
            'nb_employes_payes' => $nbEmployes,
            'evolution_pourcentage' => round($evolutionPourcentage, 2),
            'cout_total_employeur' => $totalBrut + $totalCotisationsPatronales,
        ];
    }

    /**
     * Évolution de la masse salariale sur 12 mois
     */
    private function getEvolutionMasseSalariale(int $annee): array
    {
        $data = [];

        for ($mois = 1; $mois <= 12; $mois++) {
            $totalBrut = BulletinPaie::where('mois', $mois)
                ->where('annee', $annee)
                ->whereIn('statut', [BulletinPaie::STATUT_VALIDE, BulletinPaie::STATUT_ENVOYE, BulletinPaie::STATUT_ARCHIVE])
                ->sum('salaire_brut');

            $totalNet = BulletinPaie::where('mois', $mois)
                ->where('annee', $annee)
                ->whereIn('statut', [BulletinPaie::STATUT_VALIDE, BulletinPaie::STATUT_ENVOYE, BulletinPaie::STATUT_ARCHIVE])
                ->sum('salaire_net_a_payer');

            $data[] = [
                'mois' => Carbon::create($annee, $mois, 1)->translatedFormat('M'),
                'brut' => $totalBrut,
                'net' => $totalNet,
            ];
        }

        return $data;
    }

    /**
     * Répartition des cotisations pour le mois
     */
    private function getRepartitionCotisations(int $mois, int $annee): array
    {
        $bulletins = BulletinPaie::where('mois', $mois)
            ->where('annee', $annee)
            ->whereIn('statut', [BulletinPaie::STATUT_VALIDE, BulletinPaie::STATUT_ENVOYE, BulletinPaie::STATUT_ARCHIVE])
            ->get();

        return [
            'ipres' => $bulletins->sum('cotisation_ipres') + $bulletins->sum('cotisation_patronale_ipres'),
            'css' => $bulletins->sum('cotisation_css') + $bulletins->sum('cotisation_patronale_css'),
            'ipm' => $bulletins->sum('cotisation_ipm') + $bulletins->sum('cotisation_patronale_ipm'),
            'trimf' => $bulletins->sum('trimf'),
            'cfce' => $bulletins->sum('cfce'),
            'ir' => $bulletins->sum('impot_revenu'),
        ];
    }

    /**
     * API pour récupérer les stats en temps réel
     */
    public function getStats(Request $request)
    {
        $mois = $request->input('mois', now()->month);
        $annee = $request->input('annee', now()->year);

        return response()->json([
            'stats' => $this->getMainStats($mois, $annee),
            'evolution' => $this->getEvolutionMasseSalariale($annee),
            'repartition' => $this->getRepartitionCotisations($mois, $annee),
        ]);
    }
}
