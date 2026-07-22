<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departement extends Model
{
    use SoftDeletes;

    protected $table = 'departements';

    protected $fillable = [
        'nom',
        'responsable_id',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'departement_id');
    }

    public function employes()
    {
        return $this->hasMany(Employe::class, 'id_departement');
    }

    public function responsable()
    {
        return $this->belongsTo(Employe::class, 'responsable_id');
    }
}
