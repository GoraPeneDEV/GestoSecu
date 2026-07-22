<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImmobilisationCategorie extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'immobilisation_categories';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'type_bien',
        'est_dotable',
        'est_amortissable',
        'methode_amortissement_defaut',
        'duree_amortissement_defaut',
        'taux_amortissement_defaut',
    ];

    protected $casts = [
        'est_dotable' => 'boolean',
        'est_amortissable' => 'boolean',
        'duree_amortissement_defaut' => 'integer',
        'taux_amortissement_defaut' => 'decimal:2',
    ];

    /**
     * Relation : Une catégorie a plusieurs immobilisations
     */
    public function immobilisations()
    {
        return $this->hasMany(Immobilisation::class, 'categorie_id');
    }

    /**
     * Relation : Une catégorie a plusieurs articles liés
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'immobilisation_categorie_id');
    }

    /**
     * Scope : Catégories dotables
     */
    public function scopeDotables($query)
    {
        return $query->where('est_dotable', true);
    }

    /**
     * Scope : Catégories amortissables
     */
    public function scopeAmortissables($query)
    {
        return $query->where('est_amortissable', true);
    }

    /**
     * Accesseur : Taux calculé depuis la durée si non défini
     */
    public function getTauxCalculeAttribute()
    {
        if ($this->taux_amortissement_defaut) {
            return $this->taux_amortissement_defaut;
        }
        if ($this->duree_amortissement_defaut) {
            return 100 / $this->duree_amortissement_defaut;
        }
        return null;
    }

    /**
     * Boot : Générer le code si non fourni
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($categorie) {
            if (empty($categorie->code)) {
                $categorie->code = strtoupper(substr($categorie->libelle, 0, 3)) . '-' . time();
            }
        });
    }
}
