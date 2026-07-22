<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployePaieData extends Model
{
    protected $table = 'employe_paie_data';

    protected $fillable = [
        'employe_id',
        'salaire_base',
        'sursalaire',
        'categorie_professionnelle',
        'classification',
        'echelon',
        'coefficient',
        'parts_fiscales',
        'nombre_epouses',
        'nombre_enfants_a_charge',
        'numero_ipres',
        'numero_css',
        'numero_ipm',
        'numero_contribuable',
        'banque_nom',
        'banque_code',
        'banque_guichet',
        'numero_compte',
        'cle_rib',
        'iban',
        'domiciliation_bancaire',
        'actif',
        'date_derniere_augmentation',
        'commentaire_paie',
    ];

    protected $casts = [
        'salaire_base' => 'decimal:2',
        'sursalaire' => 'decimal:2',
        'coefficient' => 'decimal:2',
        'parts_fiscales' => 'decimal:1',
        'nombre_epouses' => 'integer',
        'nombre_enfants_a_charge' => 'integer',
        'echelon' => 'integer',
        'actif' => 'boolean',
        'date_derniere_augmentation' => 'date',
    ];

    /**
     * Relation avec l'employé
     */
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    /**
     * Calculer le salaire brut de base (avant primes et heures sup)
     */
    public function getSalaireBrutBase(): float
    {
        return (float) ($this->salaire_base + $this->sursalaire);
    }

    /**
     * Obtenir le RIB complet formaté
     */
    public function getRibComplet(): ?string
    {
        if (!$this->banque_code || !$this->banque_guichet || !$this->numero_compte || !$this->cle_rib) {
            return null;
        }

        return sprintf(
            '%s %s %s %s',
            $this->banque_code,
            $this->banque_guichet,
            $this->numero_compte,
            $this->cle_rib
        );
    }

    /**
     * Vérifier si les données bancaires sont complètes
     */
    public function hasDonneesBancairesCompletes(): bool
    {
        return !empty($this->banque_nom)
            && !empty($this->numero_compte)
            && (!empty($this->iban) || $this->getRibComplet());
    }

    /**
     * Vérifier si les identifiants fiscaux sont complets
     */
    public function hasIdentifiantsFiscauxComplets(): bool
    {
        return !empty($this->numero_ipres)
            && !empty($this->numero_css);
    }

    /**
     * Calculer les parts fiscales automatiquement selon la situation familiale
     */
    public function calculerPartsFiscales(): float
    {
        // Base: 1 part
        $parts = 1.0;

        // +1 part pour la première épouse
        if ($this->nombre_epouses >= 1) {
            $parts += 1.0;
        }

        // +0.5 part par enfant (max 4 enfants selon législation sénégalaise)
        $enfantsComptabilises = min($this->nombre_enfants_a_charge, 4);
        $parts += ($enfantsComptabilises * 0.5);

        return $parts;
    }

    /**
     * Mettre à jour automatiquement les parts fiscales
     */
    public function updatePartsFiscales(): void
    {
        $this->parts_fiscales = $this->calculerPartsFiscales();
        $this->save();
    }
}
