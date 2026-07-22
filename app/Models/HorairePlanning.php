<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class HorairePlanning extends Model
{
  protected $table = 'horaires_planning';

  protected $fillable = [
    'label',
    'heure_debut',
    'heure_fin'
  ];

  protected $casts = [
    'heure_debut' => 'datetime:H:i',
    'heure_fin' => 'datetime:H:i',
    'nombre_heures' => 'decimal:2'
  ];

  public function detailsPlannings()
  {
    return $this->hasMany(DetailPlanningHorizontal::class, 'horaire_id');
  }

  /**
   * Calcule automatiquement le nombre d'heures entre heure_debut et heure_fin
   * 
   * @return float
   */
  public function getNombreHeuresAttribute()
  {
    if (!$this->heure_debut || !$this->heure_fin) {
      return 0;
    }

    try {
      $debut = Carbon::parse($this->heure_debut);
      $fin = Carbon::parse($this->heure_fin);

      // Si l'heure de fin est avant l'heure de début, 
      // on considère que c'est le lendemain
      if ($fin->lessThan($debut)) {
        $fin->addDay();
      }

      // Retourner la différence en heures avec 2 décimales
      return round($debut->diffInMinutes($fin) / 60, 2);
    } catch (\Exception $e) {
      return 0;
    }
  }

  /**
   * Vérifie si c'est un horaire de repos
   * 
   * @return bool
   */
  public function isRepos()
  {
    return $this->label === 'Repos' ||
      $this->nombre_heures == 0 ||
      strtolower($this->label) === 'repos';
  }
}
