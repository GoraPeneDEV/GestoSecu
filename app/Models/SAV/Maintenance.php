<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Site;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contrat_id',
        'site_id',
        'date_prevue',
        'description',
        'status',
        'date_realisation'
    ];

    protected $casts = [
        'date_prevue' => 'date',
        'date_realisation' => 'date',
    ];

    /**
     * Contrat lié à cette maintenance
     */
    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    /**
     * Site concerné par la maintenance
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Interventions réalisées dans le cadre de cette maintenance
     */
    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }

    /**
     * Filtrer par statut
     */
    public function scopeParStatut($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Maintenances à venir
     */
    public function scopeAVenir($query)
    {
        return $query->where('date_prevue', '>=', now())
                     ->where('status', 'planifiee');
    }
}
