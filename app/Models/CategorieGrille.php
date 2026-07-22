<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorieGrille extends Model
{
    use SoftDeletes;

    protected $table = 'categories_grilles';

    protected $fillable = [
        'grille_id',
        'code',
        'nom',
        'description',
        'ordre_affichage',
    ];

    protected $casts = [
        'ordre_affichage' => 'integer',
    ];

    const CATEGORIE_CADRE = 'cadre';
    const CATEGORIE_AGENT_MAITRISE = 'agent_maitrise';
    const CATEGORIE_EMPLOYE = 'employe';
    const CATEGORIE_OUVRIER = 'ouvrier';

    public static function getCategories(): array
    {
        return [
            self::CATEGORIE_CADRE => 'Cadre',
            self::CATEGORIE_AGENT_MAITRISE => 'Agent de Maîtrise',
            self::CATEGORIE_EMPLOYE => 'Employé',
            self::CATEGORIE_OUVRIER => 'Ouvrier',
        ];
    }

    /**
     * Relations
     */
    public function grille()
    {
        return $this->belongsTo(GrilleSalariale::class, 'grille_id');
    }

    public function echelons()
    {
        return $this->hasMany(EchelonGrille::class, 'categorie_id');
    }

    /**
     * Scopes
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre_affichage');
    }
}
