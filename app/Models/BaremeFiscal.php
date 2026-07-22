<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BaremeFiscal extends Model
{
    protected $table = 'bareme_fiscal';

    protected $fillable = [
        'type',
        'annee',
        'taux_salarial',
        'taux_patronal',
        'plafond',
        'tranche_min',
        'tranche_max',
        'taux_ir',
        'actif',
        'description',
        'reference_legale',
    ];

    protected $casts = [
        'annee' => 'integer',
        'taux_salarial' => 'decimal:2',
        'taux_patronal' => 'decimal:2',
        'plafond' => 'decimal:2',
        'tranche_min' => 'decimal:2',
        'tranche_max' => 'decimal:2',
        'taux_ir' => 'decimal:2',
        'actif' => 'boolean',
    ];

    // Types de barèmes
    public const TYPE_IPRES_RG = 'ipres_rg';
    public const TYPE_IPRES_CADRE = 'ipres_cadre';
    public const TYPE_CSS = 'css';
    public const TYPE_IPM = 'ipm';
    public const TYPE_IR = 'ir';
    public const TYPE_TRIMF = 'trimf';
    public const TYPE_CFCE = 'cfce';

    /**
     * Scope pour filtrer par type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer par année
     */
    public function scopeForYear($query, int $annee)
    {
        return $query->where('annee', $annee);
    }

    /**
     * Scope pour les barèmes actifs uniquement
     */
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    /**
     * Récupérer les barèmes avec cache
     */
    public static function getCachedBaremes(int $annee): array
    {
        $cacheKey = "baremes_fiscaux_{$annee}";

        return Cache::remember($cacheKey, now()->addMonths(1), function () use ($annee) {
            return self::forYear($annee)
                ->actif()
                ->orderBy('type')
                ->orderBy('tranche_min')
                ->get()
                ->groupBy('type')
                ->toArray();
        });
    }

    /**
     * Invalider le cache des barèmes
     */
    public static function clearCache(int $annee): void
    {
        Cache::forget("baremes_fiscaux_{$annee}");
    }

    /**
     * Récupérer un barème spécifique (cotisations sociales)
     */
    public static function getBaremeCotisation(string $type, int $annee): ?self
    {
        return self::ofType($type)
            ->forYear($annee)
            ->actif()
            ->first();
    }

    /**
     * Récupérer les tranches IR pour une année
     */
    public static function getTranchesIR(int $annee): array
    {
        return self::ofType(self::TYPE_IR)
            ->forYear($annee)
            ->actif()
            ->orderBy('tranche_min')
            ->get()
            ->toArray();
    }

    /**
     * Observer pour invalider le cache lors des modifications
     */
    protected static function booted()
    {
        static::saved(function ($bareme) {
            self::clearCache($bareme->annee);
        });

        static::deleted(function ($bareme) {
            self::clearCache($bareme->annee);
        });
    }
}
