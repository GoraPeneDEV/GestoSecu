<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class FicheProgresAction extends Model
{
    use HasFactory;

    protected $table = 'fiche_progres_actions';

    protected $fillable = [
        'fiche_progres_id',
        'description',
        'responsable_id',
        'date_echeance',
        'statut',
        'date_realisation',
        'preuves',
        'commentaire'
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'date_realisation' => 'date'
    ];

    /**
     * Fiche de progrès parente
     */
    public function ficheProgres()
    {
        return $this->belongsTo(FicheProgres::class, 'fiche_progres_id');
    }

    /**
     * Responsable de l'action
     */
    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeEnRetard($query)
    {
        return $query->where('date_echeance', '<', now())
                     ->where('statut', '!=', 'realisee');
    }

    public function scopeARealiser($query)
    {
        return $query->whereIn('statut', ['non_demarree', 'en_cours']);
    }

    // ============================================
    // MÉTHODES MÉTIER
    // ============================================

    /**
     * Vérifie si l'action est en retard
     */
    public function estEnRetard()
    {
        return $this->date_echeance < now() && $this->statut !== 'realisee';
    }

    /**
     * Marquer comme réalisée
     */
    public function marquerRealisee($commentaire = null)
    {
        $this->update([
            'statut' => 'realisee',
            'date_realisation' => now(),
            'commentaire' => $commentaire
        ]);
    }

    /**
     * Badge du statut
     */
    public function getStatutBadgeAttribute()
    {
        $badges = [
            'non_demarree' => '<span class="badge bg-secondary">Non démarrée</span>',
            'en_cours' => '<span class="badge bg-info">En cours</span>',
            'realisee' => '<span class="badge bg-success">Réalisée</span>',
            'retardee' => '<span class="badge bg-danger">En retard</span>',
        ];
        return $badges[$this->statut] ?? '<span class="badge bg-light">Inconnu</span>';
    }
}
