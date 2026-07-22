<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sanction extends Model
{
  use SoftDeletes;

  protected $table = 'sanctions';

  protected $fillable = [
    'employe_id',
    'mois',
    'annee',
    'motif',
    'montant',
    'date',
    'observation',
    'created_by',
  ];

  protected $casts = [
    'date' => 'date',
    'montant' => 'decimal:2',
  ];

  public function employe()
  {
    return $this->belongsTo(Employe::class, 'employe_id');
  }

  public function createdBy()
  {
    return $this->belongsTo(User::class, 'created_by');
  }
}
