<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Site;
use App\Models\User;

class Intervention extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sav_interventions';

    protected $fillable = [
        'numero_intervention',
        'type',
        'maintenance_id',
        'contrat_id',
        'site_id',
        'technicien_id',
        'date_intervention',
        'recommandations_generales',
        'photos',
        'statut'
    ];

    protected $casts = [
        'date_intervention' => 'datetime',
        'photos' => 'array',
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($intervention) {
            if (empty($intervention->numero_intervention)) {
                $intervention->numero_intervention = self::genererNumero();
            }
        });
    }

    /**
     * Génère un numéro d'intervention unique
     */
    public static function genererNumero()
    {
        $annee = date('Y');
        $mois = date('m');
        $dernier = self::whereYear('created_at', $annee)
            ->whereMonth('created_at', $mois)
            ->withTrashed()
            ->orderBy('id', 'desc')
            ->first();

        $numero = $dernier ? intval(substr($dernier->numero_intervention, -4)) + 1 : 1;

        return sprintf('INT-%s%s-%04d', $annee, $mois, $numero);
    }

    /**
     * Site concerné
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Technicien ayant réalisé l'intervention
     */
    public function technicien()
    {
        return $this->belongsTo(User::class, 'technicien_id');
    }

    /**
     * Maintenance prévue associée (si applicable)
     */
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    /**
     * Contrat associé (directement ou via la maintenance)
     */
    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    /**
     * Appareils concernés par l'intervention
     */
    public function assets()
    {
        return $this->belongsToMany(ClientAsset::class, 'intervention_assets', 'sav_intervention_id', 'client_asset_id')
                    ->withPivot(['actions_faites', 'recommandation_specifique', 'statut_apres'])
                    ->withTimestamps();
    }
}
