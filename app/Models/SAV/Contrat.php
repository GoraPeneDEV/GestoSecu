<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Client;
use App\Models\User;

class Contrat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contrats';

    protected $fillable = [
        'numero_contrat',
        'client_id',
        'type',
        'date_signature',
        'date_debut',
        'date_fin',
        'statut',
        'montant_total',
        'frequence_paiement',
        'prestations_incluses',
        'delai_intervention_heures',
        'nombre_interventions_incluses',
        'garantie_incluse',
        'duree_garantie_mois',
        'renouvellement_auto',
        'preavis_renouvellement_jours',
        'responsable_sav_id',
        'signataire_id',
        'fichier_contrat',
        'notes',
    ];

    protected $casts = [
        'date_signature' => 'date',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant_total' => 'decimal:2',
        'delai_intervention_heures' => 'integer',
        'nombre_interventions_incluses' => 'integer',
        'garantie_incluse' => 'boolean',
        'duree_garantie_mois' => 'integer',
        'renouvellement_auto' => 'boolean',
        'preavis_renouvellement_jours' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contrat) {
            if (empty($contrat->numero_contrat)) {
                $contrat->numero_contrat = self::genererNumero();
            }
        });
    }

    public static function genererNumero()
    {
        $annee = date('Y');
        $dernier = self::whereYear('created_at', $annee)->withTrashed()->orderBy('id', 'desc')->first();
        $numero = $dernier ? intval(substr($dernier->numero_contrat, -4)) + 1 : 1;

        return sprintf('CT-%s-%04d', $annee, $numero);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function responsableSav()
    {
        return $this->belongsTo(User::class, 'responsable_sav_id');
    }

    public function signataire()
    {
        return $this->belongsTo(User::class, 'signataire_id');
    }

    public function fichesProgres()
    {
        return $this->hasMany(FicheProgres::class);
    }

    public function garanties()
    {
        return $this->hasMany(Garantie::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class);
    }

    public function interactions()
    {
        return $this->morphMany(ClientInteraction::class, 'relatable');
    }

    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeExpirant($query, $jours = 30)
    {
        return $query->where('date_fin', '<=', now()->addDays($jours))->where('date_fin', '>=', now())->where('statut', 'actif');
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function estEnCours()
    {
        return $this->statut === 'actif' && $this->date_debut <= now() && $this->date_fin >= now();
    }

    public function joursRestants()
    {
        if ($this->date_fin < now()) {
            return 0;
        }
        return now()->diffInDays($this->date_fin);
    }

    public function expireBientot($jours = 30)
    {
        return $this->estEnCours() && $this->joursRestants() <= $jours;
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'maintenance' => 'Maintenance',
            'gardiennage' => 'Gardiennage',
            'securite_electronique' => 'Sécurité Électronique',
            'securite_incendie' => 'Sécurité Incendie',
            'monetique' => 'Monétique',
            'nettoyage' => 'Nettoyage',
            'it' => 'IT',
            'formation' => 'Formation',
            'prestation_ponctuelle' => 'Prestation Ponctuelle',
            'mixte' => 'Mixte',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getStatutBadgeAttribute()
    {
        $badges = [
            'brouillon' => '<span class="badge bg-secondary">Brouillon</span>',
            'en_attente_signature' => '<span class="badge bg-warning">En attente signature</span>',
            'actif' => '<span class="badge bg-success">Actif</span>',
            'suspendu' => '<span class="badge bg-info">Suspendu</span>',
            'resilie' => '<span class="badge bg-dark">Résilié</span>',
            'expire' => '<span class="badge bg-danger">Expiré</span>',
            'renouvele' => '<span class="badge bg-primary">Renouvelé</span>',
        ];
        return $badges[$this->statut] ?? '<span class="badge bg-light">Inconnu</span>';
    }
}
