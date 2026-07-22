<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ronde extends Model
{
    protected $fillable = [
        'planning_ronde_id',
        'agent_id',
        'date_debut',
        'date_fin',
        'statut',
        'steps',
        'commentaire',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    public function planningRonde()
    {
        return $this->belongsTo(PlanningRonde::class);
    }

    public function agent()
    {
        return $this->belongsTo(Employe::class, 'agent_id');
    }

    public function scans()
    {
        return $this->hasMany(RondeScan::class);
    }

    public function gpsTracks()
    {
        return $this->hasMany(RondeGpsTrack::class);
    }
}
