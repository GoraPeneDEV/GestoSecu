<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\BulletinPaie;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('paie-dashboard-view');

        $mois = (int) $request->input('mois', now()->month);
        $annee = (int) $request->input('annee', now()->year);

        $bulletins = BulletinPaie::where('mois', $mois)->where('annee', $annee)->get();

        $stats = [
            'nb_bulletins' => $bulletins->count(),
            'masse_salariale_brute' => $bulletins->sum('salaire_brut'),
            'masse_salariale_nette' => $bulletins->sum('salaire_net_a_payer'),
            'total_cotisations_salariales' => $bulletins->sum('total_cotisations_salariales'),
            'total_cotisations_patronales' => $bulletins->sum('total_cotisations_patronales'),
            'total_impots' => $bulletins->sum('impot_revenu'),
            'brouillons' => $bulletins->where('statut', BulletinPaie::STATUT_BROUILLON)->count(),
            'valides' => $bulletins->where('statut', BulletinPaie::STATUT_VALIDE)->count(),
        ];

        return view('comptabilite.dashboard', compact('stats', 'mois', 'annee'));
    }
}
