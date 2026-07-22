<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanRondeSup extends Model
{
    protected $table = 'scan_ronde_sup';

    protected $fillable = [
        'ronde_sup_id',
        'point_controle_sup_id',
        'date_scan',
        'anomalie',
        'type_anomalie',
        'urgence',
        'commentaire',
        'actions',
        'photo_url',
    ];

    protected $casts = [
        'date_scan' => 'datetime',
        'anomalie' => 'boolean',
        'actions' => 'array',
    ];

    public function ronde()
    {
        return $this->belongsTo(RondeSup::class, 'ronde_sup_id');
    }

    public function pointControle()
    {
        return $this->belongsTo(PointControleSup::class, 'point_controle_sup_id');
    }
}
