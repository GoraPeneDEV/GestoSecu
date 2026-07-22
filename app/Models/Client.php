<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'nomClient',
        'numeroClient',
        'emailClient',
        'telephoneClient',
        'addresseClient',
        'nomContactClient',
        'infoContactClient',
        'typeClient',
        'type_client',
        'priorite',
        'contact_principal_nom',
        'contact_principal_fonction',
        'contact_principal_tel',
        'contact_principal_email',
        'notes_internes',
        'preferences_contact',
        'alerte_sav_active',
        'responsable_commercial_id',
        'responsable_sav_id',
    ];

    protected $casts = [
        'preferences_contact' => 'array',
        'alerte_sav_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (empty($client->numeroClient)) {
                $maxNumber = self::withTrashed()
                    ->where('numeroClient', 'LIKE', 'CL-%')
                    ->selectRaw("MAX(CAST(SUBSTRING(numeroClient, 4) AS UNSIGNED)) as max_num")
                    ->value('max_num');

                $nextId = $maxNumber ? $maxNumber + 1 : 1;
                $client->numeroClient = 'CL-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function getNomAttribute()
    {
        return $this->nomClient;
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function contacts()
    {
        return $this->hasMany(\App\Models\SAV\ClientContact::class);
    }

    public function contactPrincipal()
    {
        return $this->hasOne(\App\Models\SAV\ClientContact::class)->where('est_principal', true);
    }

    public function contrats()
    {
        return $this->hasMany(\App\Models\SAV\Contrat::class);
    }

    public function contratsActifs()
    {
        return $this->hasMany(\App\Models\SAV\Contrat::class)->where('statut', 'actif');
    }

    public function garanties()
    {
        return $this->hasMany(\App\Models\SAV\Garantie::class);
    }

    public function garantiesActives()
    {
        return $this->hasMany(\App\Models\SAV\Garantie::class)->where('statut', 'active');
    }

    public function fichesProgres()
    {
        return $this->hasMany(\App\Models\SAV\FicheProgres::class);
    }

    public function fichesProgresEnCours()
    {
        return $this->hasMany(\App\Models\SAV\FicheProgres::class)
            ->whereNotIn('statut', ['cloture', 'non_fonde']);
    }

    public function interactions()
    {
        return $this->hasMany(\App\Models\SAV\ClientInteraction::class)->orderBy('created_at', 'desc');
    }

    public function responsableCommercial()
    {
        return $this->belongsTo(User::class, 'responsable_commercial_id');
    }

    public function responsableSav()
    {
        return $this->belongsTo(User::class, 'responsable_sav_id');
    }

    public function scopeClientActifs($query)
    {
        return $query->where('type_client', 'client_actif');
    }

    public function scopeParPriorite($query, $priorite)
    {
        return $query->where('priorite', $priorite);
    }

    public function scopeVip($query)
    {
        return $query->where('priorite', 'vip');
    }

    /**
     * Historique complet du client (timeline SAV)
     */
    public function getTimelineAttribute()
    {
        $interactions = $this->interactions()->with(['user', 'contact'])->get();
        $fiches = $this->fichesProgres()->with(['createur', 'actions'])->get();
        $contrats = $this->contrats()->with(['responsableSav'])->get();

        return collect()
            ->concat($interactions->map(fn ($i) => ['type' => 'interaction', 'date' => $i->created_at, 'data' => $i]))
            ->concat($fiches->map(fn ($f) => ['type' => 'fiche_progres', 'date' => $f->created_at, 'data' => $f]))
            ->concat($contrats->map(fn ($c) => ['type' => 'contrat', 'date' => $c->created_at, 'data' => $c]))
            ->sortByDesc('date')
            ->values();
    }
}
