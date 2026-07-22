<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AjustementSoldeConge;
use App\Models\DemandeAbsenceAdmin;
use App\Models\Employe;
use App\Models\Ronde;
use App\Models\SAV\Intervention;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Endpoints du socle commun (accueil mobile) : indicateurs par département et
 * bloc "Mes statistiques", limités aux 3 modules du produit GestoSecu (RH +
 * Paie, Ronde + Supervision, SAV + Articles + Dotations + Immobilisations).
 */
class DashboardController extends Controller
{
    /**
     * GET /api/mobile/dashboard/departement
     * Bloc d'indicateurs propre au département de l'utilisateur connecté.
     */
    public function departement(Request $request)
    {
        $user = $request->user();
        $departement = $user->departement?->nom;

        if (!$departement) {
            return response()->json([
                'success' => true,
                'data' => ['departement' => null, 'indicateurs' => []],
            ]);
        }

        $indicateurs = match ($departement) {
            'RH' => $this->indicateursRh(),
            'SAV' => $this->indicateursSav(),
            default => $this->indicateursRondes($user),
        };

        return response()->json([
            'success' => true,
            'data' => [
                'departement' => $departement,
                'indicateurs' => $indicateurs,
            ],
        ]);
    }

    private function indicateursRondes($user): array
    {
        $employeId = $user->id_employe;

        if (!$employeId) {
            return [];
        }

        $debutMois = Carbon::now()->startOfMonth();

        return [
            [
                'label' => 'Rondes ce mois',
                'valeur' => Ronde::where('agent_id', $employeId)
                    ->where('date_debut', '>=', $debutMois)
                    ->count(),
            ],
            ['label' => 'Rondes en cours', 'valeur' => Ronde::where('agent_id', $employeId)->where('statut', 'en_cours')->count()],
        ];
    }

    private function indicateursRh(): array
    {
        return [
            ['label' => 'Effectif actif', 'valeur' => Employe::where('etat', 1)->count()],
            ['label' => 'Nouvelles recrues (mois)', 'valeur' => Employe::where('etat', 1)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count()],
        ];
    }

    private function indicateursSav(): array
    {
        return [
            ['label' => 'Interventions du mois', 'valeur' => Intervention::whereMonth('created_at', now()->month)->count()],
        ];
    }

    /**
     * GET /api/mobile/dashboard/mes-stats
     * Bloc "Mes statistiques" toujours présent, contenu conditionné aux permissions.
     */
    public function mesStats(Request $request)
    {
        $user = $request->user();
        $employeId = $user->id_employe;
        $stats = [];

        if ($employeId && $user->hasAnyPermission(['ronde-view', 'ronde-create'])) {
            $stats[] = [
                'label' => 'Rondes ce mois',
                'valeur' => Ronde::where('agent_id', $employeId)
                    ->where('date_debut', '>=', Carbon::now()->startOfMonth())
                    ->count(),
            ];
        }

        if ($user->hasPermissionTo('sav-intervention-view')) {
            $stats[] = [
                'label' => 'Interventions assignées',
                'valeur' => Intervention::where('technicien_id', $user->id)
                    ->whereNotIn('statut', ['terminee', 'cloturee'])
                    ->count(),
            ];
        }

        if ($employeId) {
            $stats[] = [
                'label' => 'Absences cette année',
                'valeur' => DemandeAbsenceAdmin::where('id_employe', $employeId)
                    ->whereYear('created_at', now()->year)
                    ->whereIn('statut', [DemandeAbsenceAdmin::STATUT_VALIDE_RH, DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR])
                    ->count(),
            ];
        }

        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * GET /api/mobile/absences/mon-solde
     * Lecture seule, aucune permission additionnelle.
     */
    public function monSolde(Request $request)
    {
        $user = $request->user();
        $employe = $user->employe;

        if (!$employe) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun dossier employé associé à ce compte.',
            ], 404);
        }

        $historique = AjustementSoldeConge::where('id_employe', $employe->id)
            ->latest()
            ->take(10)
            ->get(['type', 'montant', 'commentaire', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'solde_conges' => $employe->solde_conges,
                'historique' => $historique,
            ],
        ]);
    }
}
