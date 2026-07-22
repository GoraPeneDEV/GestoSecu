<?php

namespace App\Services\Payroll;

use App\Models\BaremeFiscal;
use Illuminate\Support\Facades\Cache;

class SalaireSimulatorService
{
    /**
     * Calculer le net à partir du brut
     */
    public function calculateBrutToNet(
        float $salaireBrut,
        string $categorie,
        float $partsFiscales,
        int $nbEnfants = 0,
        int $nbEpouses = 0
    ): array {
        $annee = now()->year;

        // 1. Cotisations IPRES
        $cotisationIpres = $this->calculateIpres($salaireBrut, $categorie, 'salarial', $annee);
        $cotisationPatronaleIpres = $this->calculateIpres($salaireBrut, $categorie, 'patronal', $annee);

        // 2. Cotisations CSS
        $cotisationCss = $this->calculateCss($salaireBrut, 'salarial', $annee);
        $cotisationPatronaleCss = $this->calculateCss($salaireBrut, 'patronal', $annee);

        // 3. Cotisations IPM
        $cotisationIpm = $this->calculateIpm($salaireBrut, 'salarial', $annee);
        $cotisationPatronaleIpm = $this->calculateIpm($salaireBrut, 'patronal', $annee);

        // 4. Total cotisations salariales
        $totalCotisationsSalariales = $cotisationIpres + $cotisationCss + $cotisationIpm;

        // 5. Salaire net imposable
        $salaireNetImposable = $salaireBrut - $totalCotisationsSalariales;

        // 6. Impôt sur le revenu
        $impotRevenu = $this->calculateImpotRevenu(
            $salaireNetImposable,
            $partsFiscales,
            $nbEnfants,
            $nbEpouses,
            $annee
        );

        // 7. Salaire net à payer
        $salaireNetAPayer = $salaireNetImposable - $impotRevenu;

        // 8. Charges patronales
        $trimf = $this->calculateTrimf($salaireBrut, $annee);
        $cfce = $this->calculateCfce($salaireBrut, $annee);
        $totalChargesPatronales = $cotisationPatronaleIpres + $cotisationPatronaleCss + $cotisationPatronaleIpm + $cfce;

        // 9. Coût total employeur
        $coutTotalEmployeur = $salaireBrut + $totalChargesPatronales;

        return [
            'salaire_brut' => round($salaireBrut, 0),
            'cotisations_salariales' => [
                'ipres' => round($cotisationIpres, 0),
                'css' => round($cotisationCss, 0),
                'ipm' => round($cotisationIpm, 0),
                'total' => round($totalCotisationsSalariales, 0),
            ],
            'salaire_net_imposable' => round($salaireNetImposable, 0),
            'impot_revenu' => round($impotRevenu, 0),
            'salaire_net_a_payer' => round($salaireNetAPayer, 0),
            'charges_patronales' => [
                'ipres' => round($cotisationPatronaleIpres, 0),
                'css' => round($cotisationPatronaleCss, 0),
                'ipm' => round($cotisationPatronaleIpm, 0),
                'trimf' => round($trimf, 0),
                'cfce' => round($cfce, 0),
                'total' => round($totalChargesPatronales, 0),
            ],
            'cout_total_employeur' => round($coutTotalEmployeur, 0),
            'taux_prelevements' => round(($totalCotisationsSalariales + $impotRevenu) / $salaireBrut * 100, 2),
        ];
    }

    /**
     * Calculer le brut à partir du net (approche itérative)
     */
    public function calculateNetToBrut(
        float $salaireNetSouhaite,
        string $categorie,
        float $partsFiscales,
        int $nbEnfants = 0,
        int $nbEpouses = 0
    ): array {
        // Estimation initiale : net = brut * 0.75 (approximation)
        $salaireBrutEstime = $salaireNetSouhaite / 0.75;
        $tolerance = 100;
        $maxIterations = 50;
        $iteration = 0;

        do {
            $result = $this->calculateBrutToNet(
                $salaireBrutEstime,
                $categorie,
                $partsFiscales,
                $nbEnfants,
                $nbEpouses
            );

            $difference = $salaireNetSouhaite - $result['salaire_net_a_payer'];

            if (abs($difference) <= $tolerance) {
                break;
            }

            // Ajuster l'estimation
            $salaireBrutEstime += $difference * 1.3;
            $iteration++;

        } while ($iteration < $maxIterations);

        return $result;
    }

    /**
     * Calculer la cotisation IPRES
     */
    private function calculateIpres(float $salaireBrut, string $categorie, string $part, int $annee): float
    {
        $typeBareme = ($categorie === 'Cadre') ? 'IPRES_CADRE' : 'IPRES_RG';
        $bareme = $this->getBareme($typeBareme, $annee);

        if (!$bareme) {
            return 0;
        }

        $assiette = min($salaireBrut, $bareme->plafond ?? PHP_INT_MAX);
        $taux = ($part === 'salarial') ? $bareme->taux_salarial : $bareme->taux_patronal;

        return $assiette * ($taux / 100);
    }

    /**
     * Calculer la cotisation CSS
     */
    private function calculateCss(float $salaireBrut, string $part, int $annee): float
    {
        $bareme = $this->getBareme('CSS', $annee);

        if (!$bareme) {
            return 0;
        }

        $assiette = min($salaireBrut, $bareme->plafond ?? PHP_INT_MAX);
        $taux = ($part === 'salarial') ? $bareme->taux_salarial : $bareme->taux_patronal;

        return $assiette * ($taux / 100);
    }

    /**
     * Calculer la cotisation IPM
     */
    private function calculateIpm(float $salaireBrut, string $part, int $annee): float
    {
        $bareme = $this->getBareme('IPM', $annee);

        if (!$bareme) {
            return 0;
        }

        $taux = ($part === 'salarial') ? $bareme->taux_salarial : $bareme->taux_patronal;

        return $salaireBrut * ($taux / 100);
    }

    /**
     * Calculer TRIMF
     */
    private function calculateTrimf(float $salaireBrut, int $annee): float
    {
        $bareme = $this->getBareme('TRIMF', $annee);
        return $bareme ? ($salaireBrut * ($bareme->taux_patronal / 100)) : 0;
    }

    /**
     * Calculer CFCE
     */
    private function calculateCfce(float $salaireBrut, int $annee): float
    {
        $bareme = $this->getBareme('CFCE', $annee);
        return $bareme ? ($salaireBrut * ($bareme->taux_patronal / 100)) : 0;
    }

    /**
     * Calculer l'impôt sur le revenu
     */
    private function calculateImpotRevenu(
        float $salaireNetImposable,
        float $partsFiscales,
        int $nbEnfants,
        int $nbEpouses,
        int $annee
    ): float {
        // Revenu annuel
        $revenuAnnuel = $salaireNetImposable * 12;

        // Abattements mensuels (plafonnés)
        $abattementConjoint = min($nbEpouses * 50000, 50000);
        $abattementEnfants = min($nbEnfants * 25000, 100000);
        $abattementsAnnuels = ($abattementConjoint + $abattementEnfants) * 12;

        // Revenu imposable
        $revenuImposable = max(0, $revenuAnnuel - $abattementsAnnuels);

        // Quotient familial
        $quotientFamilial = $revenuImposable / $partsFiscales;

        // Barème progressif IR
        $baremes = BaremeFiscal::where('type', 'IR')
            ->where('annee', $annee)
            ->orderBy('tranche_min')
            ->get();

        $impotQuotient = 0;

        foreach ($baremes as $bareme) {
            $trancheMin = $bareme->tranche_min ?? 0;
            $trancheMax = $bareme->tranche_max ?? PHP_INT_MAX;

            if ($quotientFamilial > $trancheMin) {
                $baseImposable = min($quotientFamilial, $trancheMax) - $trancheMin;
                $impotQuotient += $baseImposable * ($bareme->taux_ir / 100);
            }

            if ($quotientFamilial <= $trancheMax) {
                break;
            }
        }

        // Impôt total annuel
        $impotAnnuel = $impotQuotient * $partsFiscales;

        // Impôt mensuel
        return max(0, $impotAnnuel / 12);
    }

    /**
     * Récupérer un barème fiscal (avec cache)
     */
    private function getBareme(string $type, int $annee)
    {
        $cacheKey = "bareme_{$type}_{$annee}";

        return Cache::remember($cacheKey, 3600, function () use ($type, $annee) {
            return BaremeFiscal::where('type', $type)
                ->where('annee', $annee)
                ->first();
        });
    }
}
