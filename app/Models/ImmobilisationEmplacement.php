<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImmobilisationEmplacement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'immobilisation_emplacements';

    protected $fillable = [
        'site_id',
        'code',
        'libelle',
        'description',
        'statut',
    ];

    protected $casts = [
        'statut' => 'string',
    ];

    /**
     * Relation : Un emplacement appartient à un site
     */
    public function site()
    {
        return $this->belongsTo(ImmobilisationSite::class, 'site_id');
    }

    /**
     * Relation : Un emplacement a plusieurs immobilisations
     */
    public function immobilisations()
    {
        return $this->hasMany(Immobilisation::class, 'emplacement_id');
    }

    /**
     * Scope : Emplacements actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Accesseur : Nom complet (Site - Emplacement)
     */
    public function getNomCompletAttribute()
    {
        return $this->site->libelle . ' - ' . $this->libelle;
    }
}
