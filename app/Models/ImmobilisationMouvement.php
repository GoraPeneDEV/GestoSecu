<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImmobilisationMouvement extends Model
{
    use HasFactory;

    protected $table = 'immobilisation_mouvements';

    protected $fillable = [
        'immobilisation_id',
        'type_mouvement',
        'ancien_employe_id',
        'ancien_site_id',
        'nouvel_employe_id',
        'nouveau_site_id',
        'motif',
        'created_by',
    ];

    /**
     * Relation : Un mouvement concerne une immobilisation
     */
    public function immobilisation()
    {
        return $this->belongsTo(Immobilisation::class, 'immobilisation_id');
    }

    /**
     * Relation : Ancien employé
     */
    public function ancienEmploye()
    {
        return $this->belongsTo(Employe::class, 'ancien_employe_id');
    }

    /**
     * Relation : Nouvel employé
     */
    public function nouvelEmploye()
    {
        return $this->belongsTo(Employe::class, 'nouvel_employe_id');
    }

    /**
     * Relation : Ancien site
     */
    public function ancienSite()
    {
        return $this->belongsTo(ImmobilisationSite::class, 'ancien_site_id');
    }

    /**
     * Relation : Nouveau site
     */
    public function nouveauSite()
    {
        return $this->belongsTo(ImmobilisationSite::class, 'nouveau_site_id');
    }

    /**
     * Relation : Créateur
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope : Par type
     */
    public function scopeParType($query, $type)
    {
        return $query->where('type_mouvement', $type);
    }

    /**
     * Scope : Inventaires
     */
    public function scopeInventaires($query)
    {
        return $query->where('type_mouvement', 'inventaire');
    }

    /**
     * Accesseur : Libellé du type
     */
    public function getTypeLibelleAttribute()
    {
        $libelles = [
            'creation' => 'Création',
            'affectation' => 'Affectation',
            'transfert_site' => 'Transfert de site',
            'transfert_employe' => 'Transfert d\'employé',
            'retour_stock' => 'Retour en stock',
            'reparation_debut' => 'Début réparation',
            'reparation_fin' => 'Fin réparation',
            'cession' => 'Cession',
            'reforme' => 'Réforme',
            'inventaire' => 'Inventaire',
        ];

        return $libelles[$this->type_mouvement] ?? $this->type_mouvement;
    }
}
