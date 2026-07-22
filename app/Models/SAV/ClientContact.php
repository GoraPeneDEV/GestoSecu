<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

class ClientContact extends Model
{
    use HasFactory;

    protected $table = 'client_contacts';

    protected $fillable = [
        'client_id',
        'nom',
        'fonction',
        'telephone',
        'email',
        'est_principal',
        'recevoir_notifications',
        'notes'
    ];

    protected $casts = [
        'est_principal' => 'boolean',
        'recevoir_notifications' => 'boolean'
    ];

    /**
     * Client associé
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Interactions liées à ce contact
     */
    public function interactions()
    {
        return $this->hasMany(ClientInteraction::class, 'contact_client_id');
    }

    /**
     * Scope pour le contact principal
     */
    public function scopePrincipal($query)
    {
        return $query->where('est_principal', true);
    }
}
