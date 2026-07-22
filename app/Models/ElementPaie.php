<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElementPaie extends Model
{
    protected $table = 'element_paie';

    protected $fillable = [
        'code',
        'libelle',
        'type',
        'mode_calcul',
        'valeur',
        'formule_classe',
        'soumis_ipres',
        'soumis_css',
        'soumis_ipm',
        'soumis_ir',
        'plafond_exoneration',
        'ordre_affichage',
        'afficher_bulletin',
        'actif',
    ];

    protected $casts = [
        'valeur' => 'decimal:2',
        'plafond_exoneration' => 'decimal:2',
        'soumis_ipres' => 'boolean',
        'soumis_css' => 'boolean',
        'soumis_ipm' => 'boolean',
        'soumis_ir' => 'boolean',
        'afficher_bulletin' => 'boolean',
        'actif' => 'boolean',
        'ordre_affichage' => 'integer',
    ];

    // Types d'éléments
    public const TYPE_GAIN = 'gain';
    public const TYPE_RETENUE = 'retenue';
    public const TYPE_COTISATION_SALARIALE = 'cotisation_salariale';
    public const TYPE_COTISATION_PATRONALE = 'cotisation_patronale';

    // Modes de calcul
    public const MODE_FIXE = 'fixe';
    public const MODE_POURCENTAGE = 'pourcentage';
    public const MODE_FORMULE = 'formule';

    /**
     * Scope pour les éléments actifs
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les éléments affichés sur le bulletin
     */
    public function scopeAffichableSurBulletin($query)
    {
        return $query->where('afficher_bulletin', true);
    }

    /**
     * Récupérer les gains
     */
    public static function getGains()
    {
        return self::ofType(self::TYPE_GAIN)
            ->actif()
            ->orderBy('ordre_affichage')
            ->get();
    }

    /**
     * Récupérer les retenues
     */
    public static function getRetenues()
    {
        return self::ofType(self::TYPE_RETENUE)
            ->actif()
            ->orderBy('ordre_affichage')
            ->get();
    }

    /**
     * Vérifier si l'élément est soumis à une cotisation
     */
    public function isSoumisACotisation(string $typeCotisation): bool
    {
        return match ($typeCotisation) {
            'ipres' => $this->soumis_ipres,
            'css' => $this->soumis_css,
            'ipm' => $this->soumis_ipm,
            'ir' => $this->soumis_ir,
            default => false,
        };
    }
}
