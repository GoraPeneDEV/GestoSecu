<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrimeEmploye extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'primes_employes';

    protected $fillable = [
        'employe_id',
        'element_paie_id',
        'type_prime',
        'libelle',
        'montant',
        'pourcentage',
        'est_permanente',
        'date_debut',
        'date_fin',
        'est_active',
        'est_soumise_ipres',
        'est_soumise_css',
        'est_soumise_ipm',
        'est_soumise_ir',
        'description',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'pourcentage' => 'decimal:2',
        'est_permanente' => 'boolean',
        'est_active' => 'boolean',
        'est_soumise_ipres' => 'boolean',
        'est_soumise_css' => 'boolean',
        'est_soumise_ipm' => 'boolean',
        'est_soumise_ir' => 'boolean',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    const TYPE_TRANSPORT = 'transport';
    const TYPE_PANIER = 'panier';
    const TYPE_ANCIENNETE = 'anciennete';
    const TYPE_RESPONSABILITE = 'responsabilite';
    const TYPE_PERFORMANCE = 'performance';
    const TYPE_PROJET = 'projet';
    const TYPE_13EME_MOIS = '13eme_mois';
    const TYPE_AUTRE = 'autre';

    public static function getTypes(): array
    {
        return [
            self::TYPE_TRANSPORT => 'Prime de Transport',
            self::TYPE_PANIER => 'Prime de Panier',
            self::TYPE_ANCIENNETE => 'Prime d\'Ancienneté',
            self::TYPE_RESPONSABILITE => 'Prime de Responsabilité',
            self::TYPE_PERFORMANCE => 'Prime de Performance',
            self::TYPE_PROJET => 'Prime de Projet',
            self::TYPE_13EME_MOIS => '13ème Mois',
            self::TYPE_AUTRE => 'Autre Prime',
        ];
    }

    /**
     * Relations
     */
    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function elementPaie()
    {
        return $this->belongsTo(ElementPaie::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('est_active', true);
    }

    public function scopePermanente($query)
    {
        return $query->where('est_permanente', true);
    }

    public function scopeValideADate($query, $date)
    {
        return $query->where(function ($q) use ($date) {
            $q->whereNull('date_debut')
                ->orWhere('date_debut', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('date_fin')
                ->orWhere('date_fin', '>=', $date);
        });
    }

    /**
     * Vérifier si la prime est valide pour une date donnée
     */
    public function estValideADate($date): bool
    {
        if (!$this->est_active) {
            return false;
        }

        if ($this->date_debut && $date < $this->date_debut) {
            return false;
        }

        if ($this->date_fin && $date > $this->date_fin) {
            return false;
        }

        return true;
    }

    /**
     * Calculer le montant de la prime
     */
    public function calculerMontant(float $salaireBase = 0): float
    {
        if ($this->montant) {
            return $this->montant;
        }

        if ($this->pourcentage && $salaireBase > 0) {
            return $salaireBase * ($this->pourcentage / 100);
        }

        return 0;
    }

    /**
     * Activer la prime
     */
    public function activer(): bool
    {
        return $this->update(['est_active' => true]);
    }

    /**
     * Désactiver la prime
     */
    public function desactiver(): bool
    {
        return $this->update(['est_active' => false]);
    }
}
