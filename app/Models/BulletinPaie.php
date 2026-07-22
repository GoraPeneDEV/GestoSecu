<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\AuditablePaie;

class BulletinPaie extends Model
{
    use AuditablePaie;
    protected $table = 'bulletin_paie';

    protected $fillable = [
        'employe_id',
        'mois',
        'annee',
        'numero_bulletin',
        'salaire_base',
        'total_gains',
        'total_heures_sup',
        'salaire_brut',
        'cotisation_ipres',
        'cotisation_css',
        'cotisation_ipm',
        'total_cotisations_salariales',
        'cotisation_patronale_ipres',
        'cotisation_patronale_css',
        'cotisation_patronale_ipm',
        'total_cotisations_patronales',
        'salaire_net_imposable',
        'trimf',
        'cfce',
        'impot_revenu',
        'total_autres_retenues',
        'indemnite_transport',
        'prime_panier',
        'salaire_net_a_payer',
        'cumul_brut_annuel',
        'cumul_net_annuel',
        'cumul_ir_annuel',
        'statut',
        'date_generation',
        'genere_par',
        'date_validation',
        'valide_par',
        'date_envoi',
        'pdf_path',
    ];

    protected $casts = [
        'mois' => 'integer',
        'annee' => 'integer',
        'salaire_base' => 'decimal:2',
        'total_gains' => 'decimal:2',
        'total_heures_sup' => 'decimal:2',
        'salaire_brut' => 'decimal:2',
        'cotisation_ipres' => 'decimal:2',
        'cotisation_css' => 'decimal:2',
        'cotisation_ipm' => 'decimal:2',
        'total_cotisations_salariales' => 'decimal:2',
        'cotisation_patronale_ipres' => 'decimal:2',
        'cotisation_patronale_css' => 'decimal:2',
        'cotisation_patronale_ipm' => 'decimal:2',
        'total_cotisations_patronales' => 'decimal:2',
        'salaire_net_imposable' => 'decimal:2',
        'trimf' => 'decimal:2',
        'cfce' => 'decimal:2',
        'impot_revenu' => 'decimal:2',
        'total_autres_retenues' => 'decimal:2',
        'salaire_net_a_payer' => 'decimal:2',
        'cumul_brut_annuel' => 'decimal:2',
        'cumul_net_annuel' => 'decimal:2',
        'cumul_ir_annuel' => 'decimal:2',
        'date_generation' => 'datetime',
        'date_validation' => 'datetime',
        'date_envoi' => 'datetime',
    ];

    // Statuts
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_VALIDE = 'valide';
    public const STATUT_ENVOYE = 'envoye';
    public const STATUT_ARCHIVE = 'archive';

    /**
     * Relation avec l'employé
     */
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    /**
     * Relation avec les lignes du bulletin
     */
    public function lignes(): HasMany
    {
        return $this->hasMany(BulletinLigne::class, 'bulletin_paie_id');
    }

    /**
     * Relation avec l'utilisateur qui a généré
     */
    public function generePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'genere_par');
    }

    /**
     * Relation avec l'utilisateur qui a validé
     */
    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    /**
     * Scope pour une période spécifique
     */
    public function scopeForPeriod($query, int $mois, int $annee)
    {
        return $query->where('mois', $mois)->where('annee', $annee);
    }

    /**
     * Scope pour un statut spécifique
     */
    public function scopeWithStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Générer le numéro de bulletin
     */
    public static function genererNumeroBulletin(int $mois, int $annee): string
    {
        $dernierNumero = self::forPeriod($mois, $annee)->count() + 1;
        return sprintf('%04d-%02d-%05d', $annee, $mois, $dernierNumero);
    }

    /**
     * Vérifier si le bulletin peut être modifié
     */
    public function estModifiable(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    /**
     * Valider le bulletin
     */
    public function valider(int $userId): void
    {
        $this->statut = self::STATUT_VALIDE;
        $this->valide_par = $userId;
        $this->date_validation = now();
        $this->save();
    }

    /**
     * Marquer comme envoyé
     */
    public function marquerEnvoye(): void
    {
        $this->statut = self::STATUT_ENVOYE;
        $this->date_envoi = now();
        $this->save();
    }

    /**
     * Archiver le bulletin
     */
    public function archiver(): void
    {
        $this->statut = self::STATUT_ARCHIVE;
        $this->save();
    }

    /**
     * Calculer le coût total employeur
     */
    public function getCoutTotalEmployeur(): float
    {
        return (float) ($this->salaire_brut + $this->total_cotisations_patronales);
    }
}
