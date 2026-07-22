<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DotationDetail extends Model
{
  protected $fillable = [
    'dotation_id',
    'article_id',
    'quantite',
    'is_returned',
    'date_retour',
    'statut_retour',
    'observation',
  ];

  protected $casts = [
    'quantite' => 'integer',
    'date_retour' => 'date'
  ];

  public function dotation()
  {
    return $this->belongsTo(Dotation::class);
  }

  public function article()
  {
    return $this->belongsTo(Article::class);
  }
}
