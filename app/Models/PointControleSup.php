<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PointControleSup extends Model
{
    use SoftDeletes;

    protected $table = 'points_controle_sup';

    protected $fillable = [
        'site_id',
        'nom',
        'emplacement',
        'ordre',
        'actif',
        'qr_code',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pointControle) {
            if (empty($pointControle->qr_code)) {
                $pointControle->qr_code = Str::uuid()->toString();
            }
        });
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function planningsRondeSup()
    {
        return $this->belongsToMany(PlanningRondeSup::class, 'planning_ronde_sup_points')
            ->withPivot('ordre')
            ->withTimestamps();
    }

    public function scans()
    {
        return $this->hasMany(ScanRondeSup::class, 'point_controle_sup_id');
    }
}
