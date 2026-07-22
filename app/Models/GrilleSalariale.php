<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrilleSalariale extends Model
{
    use SoftDeletes;

    protected $table = 'grilles_salariales';

    protected $fillable = [
        'nom',
        'description',
        'annee_debut',
        'annee_fin',
        'est_active',
    ];

    protected $casts = [
        'annee_debut' => 'integer',
        'annee_fin' => 'integer',
        'est_active' => 'boolean',
    ];

    /**
     * Relations
     */
    public function categories()
    {
        return $this->hasMany(CategorieGrille::class, 'grille_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('est_active', true);
    }

    public function scopeValideAnnee($query, int $annee)
    {
        return $query->where('annee_debut', '<=', $annee)
                     ->where(function ($q) use ($annee) {
                         $q->whereNull('annee_fin')
                           ->orWhere('annee_fin', '>=', $annee);
                     });
    }

    /**
     * Activer la grille
     */
    public function activer(): bool
    {
        // Désactiver les autres grilles
        static::where('id', '!=', $this->id)->update(['est_active' => false]);
        
        return $this->update(['est_active' => true]);
    }
}
