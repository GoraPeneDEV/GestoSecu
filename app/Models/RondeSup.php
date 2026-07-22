<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RondeSup extends Model
{
    protected $table = 'ronde_sup';

    protected $fillable = [
        'planning_ronde_sup_id',
        'agent_id',
        'date_debut',
        'date_fin',
        'statut',
        'commentaire',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    public function planningRonde()
    {
        return $this->belongsTo(PlanningRondeSup::class, 'planning_ronde_sup_id');
    }

    public function agent()
    {
        return $this->belongsTo(Employe::class, 'agent_id');
    }

    public function scans()
    {
        return $this->hasMany(ScanRondeSup::class, 'ronde_sup_id');
    }
}
