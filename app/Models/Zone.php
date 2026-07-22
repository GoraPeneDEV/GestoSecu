<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom',
        'code',
        'description',
    ];

    public function sites()
    {
        return $this->hasMany(Site::class, 'zone_id');
    }
}
