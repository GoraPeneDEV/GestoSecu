<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasPaieData;

class Employe extends Model
{
    use SoftDeletes, HasPaieData;

    protected $table = 'employe';

    protected $fillable = [
        'matricule',
        'fonction',
        'prenom',
        'nom',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'telephone',
        'adresse',
        'situation_matrimoniale',
        'niveau_experience',
        'nbr_femme',
        'nbr_enfants',
        'date_debut',
        'note',
        'nbr_conges',
        'solde_conges',
        'cni',
        'arts_martiaux',
        'date_delivrance',
        'photo',
        'diplome',
        'niveau_etude',
        'permis',
        'banque',
        'compte_bancaire',
        'langues_parlees',
        'langues_lues',
        'service_militaire',
        'corps_militaire',
        'date_debut_service',
        'date_fin_service',
        'personne_contact',
        'numero_contact',
        'lien_parente',
        'pere',
        'mere',
        'nationnalite',
        'id_departement',
        'id_user',
        'arret',
        'motif_arret',
        'commentaire',
        'etat',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_debut' => 'date',
        'date_delivrance' => 'date',
        'date_debut_service' => 'date',
        'date_fin_service' => 'date',
        'arret' => 'date',
    ];

    // ========================================
    // RELATIONS — socle
    // ========================================

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'id_departement');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id_employe', 'id');
    }

    // ========================================
    // RELATIONS — module RH + Paie
    // ========================================

    public function contrats()
    {
        return $this->hasMany(ContratEmploye::class, 'id_employe');
    }

    public function sanctions()
    {
        return $this->hasMany(Sanction::class, 'employe_id');
    }

    public function demandesAbsencesAdmin()
    {
        return $this->hasMany(DemandeAbsenceAdmin::class, 'id_employe');
    }

    public function plannings()
    {
        return $this->hasMany(Planning::class, 'employe_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeDocument::class);
    }

    public function epouses()
    {
        return $this->hasMany(EmployeEpouse::class, 'employe_id');
    }

    public function enfants()
    {
        return $this->hasMany(EmployeEnfant::class, 'employe_id');
    }

    // ========================================
    // RELATIONS — module SAV + Articles + Dotations + Immobilisations
    // ========================================

    public function dotations()
    {
        return $this->hasMany(Dotation::class, 'employe_id');
    }

    // ========================================
    // RELATIONS — module Ronde + Supervision
    // ========================================

    public function rondes()
    {
        return $this->hasMany(Ronde::class, 'agent_id');
    }

    public function rondesSup()
    {
        return $this->hasMany(RondeSup::class, 'agent_id');
    }

    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    public function scopeActifs($query)
    {
        return $query->where('etat', 1);
    }

    public function scopeDuDepartement($query, $departementId)
    {
        return $query->where('id_departement', $departementId);
    }
}
