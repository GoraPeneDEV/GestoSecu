<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPlanningHorizontal extends Model
{
  protected $table = 'details_planning_horizontal';

  protected $fillable = [
    'planning_id',
    'jour_semaine',
    'horaire_id'
  ];

  protected $casts = [
    'jour_semaine' => 'string'
  ];

  public function planning()
  {
    return $this->belongsTo(Planning::class);
  }

  public function horaire()
  {
    return $this->belongsTo(HorairePlanning::class, 'horaire_id');
  }

  // Constantes pour les jours de la semaine
  const JOURS = [
    'lundi',
    'mardi',
    'mercredi',
    'jeudi',
    'vendredi',
    'samedi',
    'dimanche'
  ];

  // Méthode utilitaire pour vérifier si un employé est en repos
  public function estEnRepos()
  {
    return $this->horaire->nombre_heures == 0;
  }

  // Méthode utilitaire pour obtenir le nombre d'heures de travail
  public function getNombreHeuresTravail()
  {
    return $this->horaire->nombre_heures;
  }
}
