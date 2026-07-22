<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AjustementSoldeConge extends Model
{
    protected $table = 'ajustements_solde_conge';

    protected $fillable = [
        'id_employe',
        'type',
        'montant',
        'commentaire',
        'id_user',
    ];

    protected $casts = [
        'montant' => 'integer',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'id_employe');
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function getMontantSigneAttribute(): string
    {
        return ($this->type === 'ajout' ? '+' : '-') . $this->montant;
    }
}
