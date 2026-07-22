<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom_site',
        'numero_rpe',
        'localisation',
        'region',
        'risques',
        'type_site',
        'latitude',
        'longitude',
        'date_debut',
        'date_arret',
        'motif_arret',
        'contact_nom',
        'contact_telephone',
        'client_id',
        'zone_id',
        'cree_par',
        'supprime_par',
        'qr_code',
        'nfc_tag',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_arret' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function clientAssets()
    {
        return $this->hasMany(\App\Models\SAV\ClientAsset::class);
    }

    public function plannings()
    {
        return $this->hasMany(Planning::class, 'site_id');
    }

    public function planningsRonde()
    {
        return $this->hasMany(PlanningRonde::class, 'site_id');
    }
}
