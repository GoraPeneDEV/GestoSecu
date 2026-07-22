<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\AuditablePaie;

class VariablePaie extends Model
{
    use AuditablePaie;
    protected $table = 'variable_paie';

    protected $fillable = [
        'employe_id',
        'mois',
        'annee',
        'jours_travailles',
        'jours_absence_non_payee',
        'heures_sup_15',
        'heures_sup_40',
        'heures_sup_60',
        'heures_sup_100',
        'prime_exceptionnelle',
        'motif_prime_exceptionnelle',
        'retenue_exceptionnelle',
        'motif_retenue_exceptionnelle',
        'montant_acompte',
        'montant_avance',
        'commentaire',
        'saisi_par',
        'date_saisie',
        'validee',
        'validee_par',
        'date_validation',
        'verrouillee',
    ];

    protected $casts = [
        'mois' => 'integer',
        'annee' => 'integer',
        'jours_travailles' => 'decimal:2',
        'jours_absence_non_payee' => 'decimal:2',
        'heures_sup_15' => 'decimal:2',
        'heures_sup_40' => 'decimal:2',
        'heures_sup_60' => 'decimal:2',
        'heures_sup_100' => 'decimal:2',
        'prime_exceptionnelle' => 'decimal:2',
        'retenue_exceptionnelle' => 'decimal:2',
        'montant_acompte' => 'decimal:2',
        'montant_avance' => 'decimal:2',
        'date_saisie' => 'datetime',
        'date_validation' => 'datetime',
        'validee' => 'boolean',
        'verrouillee' => 'boolean',
    ];

    /**
     * Relation avec l'employé
     */
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    /**
     * Relation avec l'utilisateur qui a saisi
     */
    public function saisiPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saisi_par');
    }

    /**
     * Relation avec l'utilisateur qui a validé
     */
    public function valideePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validee_par');
    }

    /**
     * Scope pour un mois/année spécifique
     */
    public function scopeForPeriod($query, int $mois, int $annee)
    {
        return $query->where('mois', $mois)->where('annee', $annee);
    }

    /**
     * Scope pour les variables validées
     */
    public function scopeValidee($query)
    {
        return $query->where('validee', true);
    }

    /**
     * Scope pour les variables non verrouillées
     */
    public function scopeNonVerrouillee($query)
    {
        return $query->where('verrouillee', false);
    }

    /**
     * Calculer le total des heures supplémentaires
     */
    public function getTotalHeuresSup(): float
    {
        return (float) (
            $this->heures_sup_15 +
            $this->heures_sup_40 +
            $this->heures_sup_60 +
            $this->heures_sup_100
        );
    }

    /**
     * Vérifier si la variable peut être modifiée
     */
    public function estModifiable(): bool
    {
        return !$this->verrouillee;
    }

    /**
     * Verrouiller la variable (après génération bulletin)
     */
    public function verrouiller(): void
    {
        $this->verrouillee = true;
        $this->save();
    }
}
