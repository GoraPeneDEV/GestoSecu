<?php

namespace App\Services;

use App\Models\Immobilisation;
use App\Models\AmortissementLigne;
use Carbon\Carbon;

class AmortissementService
{
    /**
     * Calculer les lignes d'amortissement pour une immobilisation
     */
    public function calculerLignes(Immobilisation $immobilisation)
    {
        if (!$immobilisation->est_amortissable) {
            return collect();
        }

        $methode = $immobilisation->methode_amortissement;
        
        switch ($methode) {
            case 'degressif':
                return $this->calculerDegressif($immobilisation);
            case 'variable':
                return $this->calculerVariable($immobilisation);
            case 'lineaire':
            default:
                return $this->calculerLineaire($immobilisation);
        }
    }

    /**
     * Calcul linéaire
     */
    protected function calculerLineaire(Immobilisation $immobilisation)
    {
        $lignes = collect();
        
        $valeurAmortissable = $immobilisation->valeur_acquisition - $immobilisation->valeur_residuelle;
        $dureeAnnees = (int) $immobilisation->duree_amortissement_annees;
        $taux = $immobilisation->taux_amortissement ?? (100 / $dureeAnnees);
        
        $dateDebut = Carbon::parse($immobilisation->date_debut_amortissement ?? $immobilisation->date_acquisition);
        $dateFinTotale = $dateDebut->copy()->addYears($dureeAnnees);
        
        $anneeEnCours = $dateDebut->year;
        $anneeFin = $dateFinTotale->year;
        $cumul = 0;
        
        for ($annee = $anneeEnCours; $annee <= $anneeFin; $annee++) {
            $debutExercice = ($annee === $anneeEnCours) ? $dateDebut->copy() : Carbon::create($annee, 1, 1);
            $finExercice = ($annee === $anneeFin) ? $dateFinTotale->copy() : Carbon::create($annee, 12, 31);
            
            // Calculer le nombre de jours au prorata (base 365 jours selon demande utilisateur)
            $jours = $debutExercice->diffInDays($finExercice);
            
            // Si c'est le dernier jour de l'année, diffInDays peut manquer un jour pour une année complète
            // En comptabilité, on fait souvent Fin - Debut + 1
            $jours += 1; 

            // Exception : si l'année est complète (365 ou 366 jours), on considère 365 jours pour le calcul si demandé
            // Mais ici l'utilisateur dit "durée doit être 365 jours", on va calculer au prorata sur base 365
            
            $anneePleineJours = 365;
            $montant = ($valeurAmortissable * $taux / 100) * ($jours / $anneePleineJours);
            
            // Sécurité : Ne pas dépasser le montant total
            if ($cumul + $montant > $valeurAmortissable || $annee === $anneeFin) {
                $montant = $valeurAmortissable - $cumul;
            }

            if ($montant <= 0 && $annee === $anneeFin) continue;

            $cumul += $montant;
            $valeurNette = max(0, $immobilisation->valeur_acquisition - $cumul);
            
            $lignes->push([
                'immobilisation_id' => $immobilisation->id,
                'annee_exercice' => $annee,
                'date_debut' => $debutExercice,
                'date_fin' => $finExercice,
                'duree_jours' => $jours,
                'montant_amortissement' => round($montant, 2),
                'cumul_amortissement' => round($cumul, 2),
                'valeur_nette' => round($valeurNette, 2),
            ]);
            
            if ($cumul >= $valeurAmortissable) break;
        }

        return $lignes;
    }

    /**
     * Calcul dégressif
     */
    protected function calculerDegressif(Immobilisation $immobilisation)
    {
        $lignes = collect();
        
        $valeurAmortissable = $immobilisation->valeur_acquisition - $immobilisation->valeur_residuelle;
        $duree = $immobilisation->duree_amortissement_annees;
        $tauxLineaire = 100 / $duree;
        $tauxDegressif = $tauxLineaire * 1.5; // Coefficient 1.5 (OHADA)
        
        $dateDebut = Carbon::parse($immobilisation->date_debut_amortissement);
        $anneeDebut = $dateDebut->year;
        $cumul = 0;
        $valeurRestante = $valeurAmortissable;
        
        for ($i = 0; $i < $duree; $i++) {
            $anneeExercice = $anneeDebut + $i;
            
            // Calculer le taux linéaire restant
            $anneesRestantes = $duree - $i;
            $tauxLineaireRestant = 100 / $anneesRestantes;
            
            // Choisir le taux le plus avantageux
            $tauxApplique = min($tauxDegressif, $tauxLineaireRestant);
            
            // Prorata pour première année
            if ($i === 0) {
                $jours = $dateDebut->diffInDays(Carbon::create($anneeDebut, 12, 31));
                $dureeAnnee = $dateDebut->isLeapYear() ? 366 : 365;
                $montant = $valeurRestante * $tauxApplique / 100 * ($jours / $dureeAnnee);
            } else {
                $montant = $valeurRestante * $tauxApplique / 100;
            }
            
            // Ne pas dépasser la valeur restante
            $montant = min($montant, $valeurRestante);
            
            $valeurRestante -= $montant;
            $cumul += $montant;
            $valeurNette = max(0, $immobilisation->valeur_acquisition - $cumul);
            
            $lignes->push([
                'immobilisation_id' => $immobilisation->id,
                'annee_exercice' => $anneeExercice,
                'date_debut' => $i === 0 ? $dateDebut : Carbon::create($anneeExercice, 1, 1),
                'date_fin' => Carbon::create($anneeExercice, 12, 31),
                'duree_jours' => $i === 0 ? $jours : 365,
                'montant_amortissement' => round($montant, 2),
                'cumul_amortissement' => round($cumul, 2),
                'valeur_nette' => round($valeurNette, 2),
            ]);
        }

        return $lignes;
    }

    /**
     * Calcul variable (basé sur l'usage)
     * Nécessite des données d'usage à implémenter selon le besoin
     */
    protected function calculerVariable(Immobilisation $immobilisation)
    {
        // Par défaut, retourne le calcul linéaire
        // Pour une implémentation complète, il faudrait stocker les unités d'œuvre
        return $this->calculerLineaire($immobilisation);
    }

    /**
     * Générer et sauvegarder les lignes d'amortissement
     */
    public function genererLignes(Immobilisation $immobilisation)
    {
        // Supprimer les lignes existantes
        $immobilisation->amortissementLignes()->delete();
        
        // Calculer et créer les nouvelles lignes
        $lignes = $this->calculerLignes($immobilisation);
        
        foreach ($lignes as $ligne) {
            AmortissementLigne::create($ligne);
        }
        
        // Mettre à jour la valeur nette comptable
        $cumul = $lignes->sum('montant_amortissement');
        $valeurNette = max(0, $immobilisation->valeur_acquisition - $cumul - $immobilisation->valeur_residuelle);
        
        $immobilisation->update(['valeur_nette_comptable' => $valeurNette]);
        
        return $lignes;
    }

    /**
     * Obtenir la valeur nette à une date donnée
     */
    public function getValeurNetteA(Immobilisation $immobilisation, ?Carbon $date = null)
    {
        $date = $date ?? now();
        
        $cumul = $immobilisation->amortissementLignes()
            ->where('date_fin', '<=', $date)
            ->sum('montant_amortissement');
        
        return max(0, $immobilisation->valeur_acquisition - $cumul - $immobilisation->valeur_residuelle);
    }

    /**
     * Obtenir le cumul des amortissements à une date donnée
     */
    public function getCumulAmortissements(Immobilisation $immobilisation, ?Carbon $date = null)
    {
        $date = $date ?? now();
        
        return $immobilisation->amortissementLignes()
            ->where('date_fin', '<=', $date)
            ->sum('montant_amortissement');
    }

    /**
     * Calculer pour toutes les immobilisations
     */
    public function calculerTout()
    {
        $immobilisations = Immobilisation::whereHas('categorie', function ($q) {
            $q->where('est_amortissable', true);
        })->get();
        
        $resultats = [];
        foreach ($immobilisations as $immobilisation) {
            $lignes = $this->genererLignes($immobilisation);
            $resultats[$immobilisation->code_interne] = $lignes->count();
        }
        
        return $resultats;
    }
}
