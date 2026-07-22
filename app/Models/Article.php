<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'designation',
        'description',
        'unite',
        'stock_minimum',
        'stock_actuel',
        'prix_unitaire',
        'departement_id',
        'est_immobilisable',
        'immobilisation_categorie_id',
        'created_by',
    ];

    protected $casts = [
        'stock_minimum' => 'integer',
        'stock_actuel' => 'integer',
        'prix_unitaire' => 'decimal:2',
        'est_immobilisable' => 'boolean',
    ];

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function dotationDetails()
    {
        return $this->hasMany(DotationDetail::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function immobilisationCategorie()
    {
        return $this->belongsTo(ImmobilisationCategorie::class, 'immobilisation_categorie_id');
    }

    public function immobilisations()
    {
        return $this->hasMany(Immobilisation::class, 'article_id');
    }

    public function scopeImmobilisables($query)
    {
        return $query->where('est_immobilisable', true);
    }
}
