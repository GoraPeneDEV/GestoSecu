<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RondeGpsTrack extends Model
{
    protected $fillable = [
        'ronde_id',
        'latitude',
        'longitude',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function ronde()
    {
        return $this->belongsTo(Ronde::class);
    }
}
