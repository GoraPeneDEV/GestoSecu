<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImmobilisationSite extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'immobilisation_sites';

    protected $fillable = [
        'code_site',
        'libelle',
        'type',
        'adresse',
        'statut',
    ];

    protected $casts = [
        'statut' => 'string',
    ];

    protected $attributes = [
        'type' => 'autre',
    ];

    /**
     * Relation : Un site a plusieurs immobilisations
     */
    public function immobilisations()
    {
        return $this->hasMany(Immobilisation::class, 'site_id');
    }

    /**
     * Relation : Un site a plusieurs emplacements
     */
    public function emplacements()
    {
        return $this->hasMany(ImmobilisationEmplacement::class, 'site_id');
    }

    /**
     * Scope : Sites actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope : Par type
     */
    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Accesseur : Nombre de biens sur ce site
     */
    public function getNombreBiensAttribute()
    {
        return $this->immobilisations()->count();
    }

    /**
     * Accesseur : Valeur totale des biens sur ce site
     */
    public function getValeurTotaleAttribute()
    {
        return $this->immobilisations()->sum('valeur_acquisition');
    }

    /**
     * Boot : Générer le code si non fourni
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($site) {
            if (empty($site->code_site)) {
                $prefixe = strtoupper(substr($site->type, 0, 3));
                $compteur = static::where('type', $site->type)->count() + 1;
                $site->code_site = $prefixe . '-' . str_pad($compteur, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
