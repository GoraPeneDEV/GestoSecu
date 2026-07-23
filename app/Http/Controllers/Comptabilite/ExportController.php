<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\BulletinPaie;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    private function bulletinsDuMois(Request $request)
    {
        $mois = (int) $request->input('mois', now()->month);
        $annee = (int) $request->input('annee', now()->year);

        return [
            BulletinPaie::with(['employe.paieData', 'employe.departement'])
                ->where('mois', $mois)
                ->where('annee', $annee)
                ->orderBy('employe_id')
                ->get(),
            $mois,
            $annee,
        ];
    }

    public function livrePaie(Request $request)
    {
        $this->authorize('paie-dashboard-view');

        [$bulletins, $mois, $annee] = $this->bulletinsDuMois($request);

        $pdf = Pdf::loadView('comptabilite.exports.livre-paie', compact('bulletins', 'mois', 'annee'))
            ->setPaper('a3', 'landscape');

        return $pdf->download("livre-de-paie-{$annee}-{$mois}.pdf");
    }

    public function rapportMasseSalariale(Request $request)
    {
        $this->authorize('paie-dashboard-view');

        [$bulletins, $mois, $annee] = $this->bulletinsDuMois($request);

        $parDepartement = $bulletins->groupBy(fn($b) => $b->employe->departement->nom ?? 'Non défini')
            ->map(function ($groupe) {
                return [
                    'nb_employes' => $groupe->count(),
                    'masse_brute' => $groupe->sum('salaire_brut'),
                    'masse_nette' => $groupe->sum('salaire_net_a_payer'),
                    'cotisations_salariales' => $groupe->sum('total_cotisations_salariales'),
                    'cotisations_patronales' => $groupe->sum('total_cotisations_patronales'),
                ];
            });

        $pdf = Pdf::loadView('comptabilite.exports.rapport-masse-salariale', compact('bulletins', 'parDepartement', 'mois', 'annee'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("rapport-masse-salariale-{$annee}-{$mois}.pdf");
    }

    public function virementsBancaires(Request $request)
    {
        $this->authorize('paie-dashboard-view');

        [$bulletins, $mois, $annee] = $this->bulletinsDuMois($request);
        $bulletinsValides = $bulletins->where('statut', '!=', BulletinPaie::STATUT_BROUILLON);

        $filename = "virements-bancaires-{$annee}-{$mois}.csv";

        return new StreamedResponse(function () use ($bulletinsValides) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Matricule', 'Nom', 'Prénom', 'Banque', 'Numéro de compte', 'IBAN', 'Montant net à payer'], ';');

            foreach ($bulletinsValides as $bulletin) {
                $employe = $bulletin->employe;
                $paieData = $employe?->paieData;
                fputcsv($handle, [
                    $employe->matricule ?? '',
                    $employe->nom ?? '',
                    $employe->prenom ?? '',
                    $paieData->banque_nom ?? '',
                    $paieData->numero_compte ?? '',
                    $paieData->iban ?? '',
                    number_format((float) $bulletin->salaire_net_a_payer, 0, '', ''),
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function declarationIpres(Request $request)
    {
        $this->authorize('paie-dashboard-view');

        [$bulletins, $mois, $annee] = $this->bulletinsDuMois($request);

        $totalCotisationSalariale = $bulletins->sum('cotisation_ipres');
        $totalCotisationPatronale = $bulletins->sum('cotisation_patronale_ipres');

        $pdf = Pdf::loadView('comptabilite.exports.declaration-ipres', compact(
            'bulletins', 'mois', 'annee', 'totalCotisationSalariale', 'totalCotisationPatronale'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("declaration-ipres-{$annee}-{$mois}.pdf");
    }
}
