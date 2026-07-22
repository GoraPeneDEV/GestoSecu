<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeDocument extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'employe_id',
    'type_document',
    'nom_fichier',
    'chemin_fichier',
    'ajoute_par'
  ];

  public function employe()
  {
    return $this->belongsTo(Employe::class);
  }

  public function ajoutePar()
  {
    return $this->belongsTo(User::class, 'ajoute_par');
  }
}
