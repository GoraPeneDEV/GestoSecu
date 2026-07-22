<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanningRondeSup extends Model
{
    use SoftDeletes;

    protected $table = 'planning_ronde_sup';

    protected $fillable = [
        'nom',
        'frequence',
        'heure_debut',
        'duree_estimee',
    ];

    protected $casts = [
        'heure_debut' => 'datetime',
    ];

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'planning_ronde_sup_sites')
            ->withPivot('ordre')
            ->withTimestamps();
    }

    public function pointsControle()
    {
        return $this->belongsToMany(PointControleSup::class, 'planning_ronde_sup_points', 'planning_ronde_sup_id', 'point_controle_sup_id')
            ->withPivot('ordre')
            ->withTimestamps();
    }

    public function rondes()
    {
        return $this->hasMany(RondeSup::class, 'planning_ronde_sup_id');
    }
}
