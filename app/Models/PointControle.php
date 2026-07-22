<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PointControle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'point_controles';

    protected $fillable = [
        'site_id',
        'nom',
        'emplacement',
        'ordre',
        'actif',
        'qr_code',
        'nfc_tag',
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

    public function scans()
    {
        return $this->hasMany(RondeScan::class);
    }

    public function planningsRonde()
    {
        return $this->belongsToMany(PlanningRonde::class, 'planning_ronde_points')
            ->withPivot('ordre')
            ->withTimestamps();
    }
}
