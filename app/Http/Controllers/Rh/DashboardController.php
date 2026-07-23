<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\ContratEmploye;
use App\Models\DemandeAbsenceAdmin;
use App\Models\DemandeExplication;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('employe-view');

        $stats = [
            'total_employes' => Employe::where('etat', 1)->count(),
            'cdi' => Employe::where('etat', 1)->whereHas('contrats', fn($q) => $q->where('type_contrat', 'CDI'))->count(),
            'cdd' => Employe::where('etat', 1)->whereHas('contrats', fn($q) => $q->where('type_contrat', 'CDD'))->count(),
            'stage' => Employe::where('etat', 1)->whereHas('contrats', fn($q) => $q->where('type_contrat', 'Stage'))->count(),
            'absences_en_cours' => DemandeAbsenceAdmin::enCours()->count(),
            'explications_en_attente' => DemandeExplication::enAttente()->count(),
        ];

        $contratsExpirant = ContratEmploye::with('employe')
            ->whereNotNull('date_prevu_fin')
            ->whereBetween('date_prevu_fin', [now(), now()->addDays(30)])
            ->orderBy('date_prevu_fin')
            ->limit(10)
            ->get();

        $employesParDepartement = Employe::where('etat', 1)
            ->selectRaw('id_departement, count(*) as total')
            ->groupBy('id_departement')
            ->with('departement')
            ->get();

        $absencesRecentes = DemandeAbsenceAdmin::with('employe')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('rh.dashboard.index', compact('stats', 'contratsExpirant', 'employesParDepartement', 'absencesRecentes'));
    }
}
