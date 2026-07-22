<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanningRonde extends Model
{
    use SoftDeletes;

    protected $table = 'plannings_ronde';

    protected $fillable = [
        'nom',
        'site_id',
        'frequence',
        'heure_debut',
        'duree_estimee',
    ];

    protected $casts = [
        'heure_debut' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function pointsControle()
    {
        return $this->belongsToMany(PointControle::class, 'planning_ronde_points')
            ->withPivot('ordre')
            ->orderBy('planning_ronde_points.ordre');
    }

    public function rondes()
    {
        return $this->hasMany(Ronde::class);
    }
}
