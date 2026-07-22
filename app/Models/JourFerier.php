<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JourFerier extends Model
{
  use SoftDeletes;
  protected $table = 'jours_ferier';
  protected $fillable = ['date_ferier', 'description'];
  protected $casts = ['date_ferier' => 'date'];
  protected $dates = ['date_ferier'];
}
