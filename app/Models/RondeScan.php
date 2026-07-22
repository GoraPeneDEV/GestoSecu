<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RondeScan extends Model
{
    protected $fillable = [
        'ronde_id',
        'point_controle_id',
        'date_scan',
        'anomalie',
        'type_anomalie',
        'urgence',
        'commentaire',
        'actions',
        'photo_url',
        'photos',
        'gps_lat',
        'gps_lng',
    ];

    protected $casts = [
        'date_scan' => 'datetime',
        'anomalie' => 'boolean',
        'actions' => 'array',
        'photos' => 'array',
    ];

    public function ronde()
    {
        return $this->belongsTo(Ronde::class);
    }

    public function pointControle()
    {
        return $this->belongsTo(PointControle::class);
    }
}
