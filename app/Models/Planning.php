<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Planning extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'employe_id',
    'site_id',
    'date_debut',
    'date_fin',
    'type_planning',
    'created_by'
  ];

  protected $casts = [
    'date_debut' => 'date',
    'date_fin' => 'date',
    'type_planning' => 'string'
  ];

  // Relation avec l'employé
  public function employe()
  {
    return $this->belongsTo(Employe::class, 'employe_id');
  }

  // Relation avec le site
  public function site()
  {
    return $this->belongsTo(Site::class, 'site_id');
  }

  // Relation avec l'utilisateur qui a créé le planning
  public function createur()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  // Relation avec les détails du planning horizontal
  public function detailsHorizontal()
  {
    return $this->hasMany(DetailPlanningHorizontal::class);
  }

  // Relation avec les détails du planning horizontal
  public function detailsVertical()
  {
    return $this->hasMany(DetailPlanningVertical::class);
  }
  // Méthode pour vérifier si le planning est actif
  public function estActif()
  {
    return is_null($this->date_fin);
  }

  // Méthode pour obtenir le total d'heures hebdomadaires
  public function getTotalHeuresHebdomadaires()
  {
    return $this->detailsHorizontal()
      ->join('horaires_planning', 'horaires_planning.id', '=', 'details_planning_horizontal.horaire_id')
      ->sum('horaires_planning.nombre_heures');
  }

  // Méthode pour vérifier si un employé est en repos un jour donné
  public function estEnRepos($jour)
  {
    $detail = $this->detailsHorizontal()
      ->where('jour_semaine', $jour)
      ->first();

    return !$detail || $detail->estEnRepos();
  }

  // Scope pour les plannings actifs
  public function scopeActifs($query)
  {
    return $query->whereNull('date_fin');
  }

  // Scope pour filtrer par département
  public function scopeParDepartement($query, $departement)
  {
    return $query->whereHas('employe.departement', function ($q) use ($departement) {
      $q->where('nom', $departement);
    });
  }

  // Méthode pour terminer un planning
  public function terminer($date_fin)
  {
    $this->update(['date_fin' => $date_fin]);
  }

  // Méthodes utilitaires
  public function getJoursDuMois()
  {
    $debut = Carbon::parse($this->date_debut)->startOfMonth();
    $fin = $debut->copy()->endOfMonth();

    $jours = [];
    for ($date = $debut; $date->lte($fin); $date->addDay()) {
      $jours[] = $date->copy();
    }

    return $jours;
  }

  // Vérifier si le planning chevauche un autre planning pour le même employé
  public function chercherChevauchement()
  {
    return static::where('employe_id', $this->employe_id)
      ->where('id', '!=', $this->id)
      ->where(function ($query) {
        $query->whereBetween('date_debut', [$this->date_debut, $this->date_fin])
          ->orWhereBetween('date_fin', [$this->date_debut, $this->date_fin]);
      })
      ->first();
  }
}
