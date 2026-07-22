<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Site;

class ClientAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'site_id',
        'type',
        'category',
        'label',
        'brand',
        'model',
        'serial_number',
        'installation_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'installation_date' => 'date',
    ];

    /**
     * Site où se trouve l'appareil
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Filtrer par département (type)
     */
    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Historique des interventions sur cet appareil
     */
    public function interventions()
    {
        return $this->belongsToMany(Intervention::class, 'intervention_assets', 'client_asset_id', 'sav_intervention_id')
                    ->withPivot(['actions_faites', 'recommandation_specifique', 'statut_apres'])
                    ->withTimestamps();
    }
}
