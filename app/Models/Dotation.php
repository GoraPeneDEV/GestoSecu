<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dotation extends Model
{
  use SoftDeletes;

  protected $fillable = [
    'reference',
    'date_dotation',
    'type_dotation',
    'site_id',
    'employe_id',
    'motif',
    'document_path',
    'created_by'
  ];

  protected $casts = [
    'date_dotation' => 'date'
  ];

  public function details()
  {
    return $this->hasMany(DotationDetail::class);
  }

  public function site()
  {
    return $this->belongsTo(Site::class);
  }

  public function employe()
  {
    return $this->belongsTo(Employe::class, 'employe_id', 'id');
  }


  public function createur()
  {
    return $this->belongsTo(User::class, 'created_by');
  }
}
