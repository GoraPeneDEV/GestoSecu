<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class PortailUser extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'portail_users';
    protected $guard_name = 'portail';

    protected $fillable = [
        'client_id',
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'fonction',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }
}
