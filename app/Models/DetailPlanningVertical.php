<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DetailPlanningVertical extends Model
{
  protected $table = 'details_planning_vertical';

  protected $fillable = [
    'planning_id',
    'date',
    'horaire_id'
  ];

  protected $casts = [
    'date' => 'date'
  ];

  public function planning()
  {
    return $this->belongsTo(Planning::class);
  }

  public function horaire()
  {
    return $this->belongsTo(HorairePlanning::class, 'horaire_id');
  }

  // Obtenir le jour de la semaine
  public function getJourSemaineAttribute()
  {
    return Carbon::parse($this->date)->locale('fr')->dayName;
  }

  // Obtenir le numéro du jour
  public function getJourAttribute()
  {
    return Carbon::parse($this->date)->day;
  }

  // Vérifier si c'est un jour de repos
  public function estRepos()
  {
    return $this->horaire->nombre_heures == 0;
  }
}
