<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterventionAsset extends Model
{
    use HasFactory;

    protected $table = 'intervention_assets';

    protected $fillable = [
        'sav_intervention_id',
        'client_asset_id',
        'actions_faites',
        'recommandation_specifique',
        'statut_apres'
    ];

    /**
     * Intervention parente
     */
    public function intervention()
    {
        return $this->belongsTo(Intervention::class, 'sav_intervention_id');
    }

    /**
     * Appareil concerné
     */
    public function asset()
    {
        return $this->belongsTo(ClientAsset::class, 'client_asset_id');
    }
}
