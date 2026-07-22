<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContratEmploye extends Model
{
  use SoftDeletes;
  public $timestamps = false;
  protected $table = 'contrat_employe';
  protected $fillable = [
    'type_contrat',
    'date_debut',
    'date_prevu_fin',
    'date_fin',
    'motif',
    'montant',
    'document',
    'id_employe',
    'etat',
    'id_user',
    'created_at',
  ];
  protected $attributes = [
    'motif' => null,
  ];

  protected $casts = [
    'date_debut' => 'date',
    'date_prevu_fin' => 'date',
    'date_fin' => 'date',
  ];

  // Relation avec l'employé
  public function employe()
  {
    return $this->belongsTo(Employe::class, 'id_employe');
  }

  // Relation avec l'utilisateur
  public function user()
  {
    return $this->belongsTo(User::class, 'id_user');
  }
}
