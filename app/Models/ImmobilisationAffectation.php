<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImmobilisationAffectation extends Model
{
    use HasFactory;

    protected $table = 'immobilisation_affectations';

    protected $fillable = [
        'immobilisation_id',
        'employe_id',
        'date_affectation',
        'date_fin_prevue',
        'date_fin_reelle',
        'type_affectation',
        'dotation_id',
        'etat_retour',
        'observation_retour',
        'documents',
        'created_by',
    ];

    protected $casts = [
        'date_affectation' => 'date',
        'date_fin_prevue' => 'date',
        'date_fin_reelle' => 'date',
        'documents' => 'array',
    ];

    public function immobilisation()
    {
        return $this->belongsTo(Immobilisation::class, 'immobilisation_id');
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    public function dotation()
    {
        return $this->belongsTo(Dotation::class, 'dotation_id');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeEnCours($query)
    {
        return $query->whereNull('date_fin_reelle');
    }

    public function scopeTerminees($query)
    {
        return $query->whereNotNull('date_fin_reelle');
    }

    public function scopeParEmploye($query, $employeId)
    {
        return $query->where('employe_id', $employeId);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_affectation', $type);
    }

    public function getEstActiveAttribute()
    {
        return is_null($this->date_fin_reelle);
    }

    public function getDureeJoursAttribute()
    {
        $dateFin = $this->date_fin_reelle ?? now();
        return $this->date_affectation->diffInDays($dateFin);
    }

    public function getJoursRestantsAttribute()
    {
        if (!$this->date_fin_prevue || $this->date_fin_reelle) {
            return null;
        }

        return max(0, now()->diffInDays($this->date_fin_prevue, false));
    }

    public function terminer($dateFin = null, $etatRetour = 'bon', $observation = null)
    {
        $this->update([
            'date_fin_reelle' => $dateFin ?? now(),
            'etat_retour' => $etatRetour,
            'observation_retour' => $observation,
        ]);

        $this->immobilisation->update([
            'statut' => 'en_stock',
            'employe_id' => null,
            'date_affectation' => null,
        ]);

        $this->immobilisation->mouvements()->create([
            'type_mouvement' => 'retour_stock',
            'ancien_employe_id' => $this->employe_id,
            'motif' => 'Retour en stock - Affectation terminée',
            'created_by' => auth()->id(),
        ]);
    }
}
