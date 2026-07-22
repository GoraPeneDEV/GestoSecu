<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EchelonGrille extends Model
{
    use SoftDeletes;

    protected $table = 'echelons_grilles';

    protected $fillable = [
        'categorie_id',
        'code',
        'nom',
        'niveau',
        'coefficient',
        'salaire_min',
        'salaire_max',
        'description',
        'ordre_affichage',
    ];

    protected $casts = [
        'niveau' => 'integer',
        'coefficient' => 'decimal:2',
        'salaire_min' => 'decimal:2',
        'salaire_max' => 'decimal:2',
        'ordre_affichage' => 'integer',
    ];

    /**
     * Relations
     */
    public function categorie()
    {
        return $this->belongsTo(CategorieGrille::class, 'categorie_id');
    }

    /**
     * Scopes
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre_affichage');
    }

    /**
     * Vérifier si un salaire est dans la fourchette
     */
    public function estDansFourchette(float $salaire): bool
    {
        return $salaire >= $this->salaire_min && $salaire <= $this->salaire_max;
    }

    /**
     * Calculer le salaire recommandé selon le coefficient
     */
    public function getSalaireRecommande(float $salaireBase = 100000): float
    {
        return $salaireBase * $this->coefficient;
    }
}
