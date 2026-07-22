<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmortissementLigne extends Model
{
    use HasFactory;

    protected $table = 'amortissement_lignes';

    protected $fillable = [
        'immobilisation_id',
        'annee_exercice',
        'date_debut',
        'date_fin',
        'duree_jours',
        'montant_amortissement',
        'cumul_amortissement',
        'valeur_nette',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant_amortissement' => 'decimal:2',
        'cumul_amortissement' => 'decimal:2',
        'valeur_nette' => 'decimal:2',
    ];

    /**
     * Relation : Une ligne appartient à une immobilisation
     */
    public function immobilisation()
    {
        return $this->belongsTo(Immobilisation::class, 'immobilisation_id');
    }

    /**
     * Scope : Par année
     */
    public function scopeParAnnee($query, $annee)
    {
        return $query->where('annee_exercice', $annee);
    }

    /**
     * Scope : Par immobilisation
     */
    public function scopeParImmobilisation($query, $immobilisationId)
    {
        return $query->where('immobilisation_id', $immobilisationId);
    }
}
