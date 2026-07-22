<?php

namespace App\Traits;

use App\Models\EmployePaieData;
use App\Models\BulletinPaie;
use App\Models\VariablePaie;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasPaieData
{
    /**
     * Relation avec les données de paie
     */
    public function paieData(): HasOne
    {
        return $this->hasOne(EmployePaieData::class, 'employe_id');
    }

    /**
     * Relation avec les bulletins de paie
     */
    public function bulletinsPaie(): HasMany
    {
        return $this->hasMany(BulletinPaie::class, 'employe_id');
    }

    /**
     * Relation avec les variables de paie
     */
    public function variablesPaie(): HasMany
    {
        return $this->hasMany(VariablePaie::class, 'employe_id');
    }

    /**
     * Créer automatiquement les données de paie si inexistantes
     */
    public function creerPaieDataSiInexistant(): EmployePaieData
    {
        if (!$this->paieData) {
            return $this->paieData()->create([
                'salaire_base' => 0,
                'actif' => true,
            ]);
        }

        return $this->paieData;
    }

    /**
     * Vérifier si l'employé a des données de paie configurées
     */
    public function hasPaieDataConfiguree(): bool
    {
        return $this->paieData()->exists()
            && $this->paieData->salaire_base > 0;
    }

    /**
     * Obtenir le salaire brut de base
     */
    public function getSalaireBrutBase(): float
    {
        if (!$this->paieData) {
            return 0.0;
        }

        return $this->paieData->getSalaireBrutBase();
    }

    /**
     * Obtenir le dernier bulletin de paie
     */
    public function getDernierBulletin(): ?BulletinPaie
    {
        return $this->bulletinsPaie()
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->first();
    }

    /**
     * Obtenir le bulletin pour une période spécifique
     */
    public function getBulletinPourPeriode(int $mois, int $annee): ?BulletinPaie
    {
        return $this->bulletinsPaie()
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->first();
    }

    /**
     * Obtenir les variables pour une période spécifique
     */
    public function getVariablesPourPeriode(int $mois, int $annee): ?VariablePaie
    {
        return $this->variablesPaie()
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->first();
    }

    /**
     * Vérifier si l'employé a un bulletin pour une période
     */
    public function hasBulletinPourPeriode(int $mois, int $annee): bool
    {
        return $this->bulletinsPaie()
            ->where('mois', $mois)
            ->where('annee', $annee)
            ->exists();
    }

    /**
     * Obtenir les bulletins d'une année
     */
    public function getBulletinsAnnee(int $annee)
    {
        return $this->bulletinsPaie()
            ->where('annee', $annee)
            ->orderBy('mois')
            ->get();
    }

    /**
     * Calculer le cumul brut annuel
     */
    public function getCumulBrutAnnuel(int $annee): float
    {
        return (float) $this->bulletinsPaie()
            ->where('annee', $annee)
            ->where('statut', '!=', BulletinPaie::STATUT_BROUILLON)
            ->sum('salaire_brut');
    }

    /**
     * Calculer le cumul net annuel
     */
    public function getCumulNetAnnuel(int $annee): float
    {
        return (float) $this->bulletinsPaie()
            ->where('annee', $annee)
            ->where('statut', '!=', BulletinPaie::STATUT_BROUILLON)
            ->sum('salaire_net_a_payer');
    }

    /**
     * Calculer le cumul IR annuel
     */
    public function getCumulIRAnnuel(int $annee): float
    {
        return (float) $this->bulletinsPaie()
            ->where('annee', $annee)
            ->where('statut', '!=', BulletinPaie::STATUT_BROUILLON)
            ->sum('impot_revenu');
    }
}
