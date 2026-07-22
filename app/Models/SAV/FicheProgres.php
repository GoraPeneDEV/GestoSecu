<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Client;
use App\Models\User;


class FicheProgres extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fiches_progres';

    protected $fillable = [
        'numero_fiche',
        'client_id',
        'contrat_id',
        'contact_id',
        'type',
        'processus_concerne',
        'objet',
        'constat_client',
        'cause_analyse',
        'statut',
        'analyse_5m',
        'efficacite_actions',
        'commentaire_efficacite',
        'redemarrage_analyse',
        'pilote_processus_id',
        'date_validation_pilote',
        'responsable_qualite_id',
        'date_cloture',
        'cree_par',
        'date_reception'
    ];

    protected $casts = [
        'analyse_5m' => 'array',
        'efficacite_actions' => 'boolean',
        'redemarrage_analyse' => 'boolean',
        'date_validation_pilote' => 'datetime',
        'date_cloture' => 'datetime',
        'date_reception' => 'datetime'
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fiche) {
            if (empty($fiche->numero_fiche)) {
                $fiche->numero_fiche = self::genererNumero();
            }
            if (empty($fiche->date_reception)) {
                $fiche->date_reception = now();
            }
        });
    }

    /**
     * Génère un numéro de fiche unique
     */
    public static function genererNumero()
    {
        $annee = date('Y');
        $dernier = self::whereYear('created_at', $annee)
            ->withTrashed()
            ->orderBy('id', 'desc')
            ->first();

        $numero = $dernier ? intval(substr($dernier->numero_fiche, -4)) + 1 : 1;

        return sprintf('FP-%s-%04d', $annee, $numero);
    }

    // ============================================
    // RELATIONS
    // ============================================

    /**
     * Client associé
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Contrat lié (si applicable)
     */
    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    /**
     * Contact client
     */
    public function contact()
    {
        return $this->belongsTo(ClientContact::class, 'contact_id');
    }

    /**
     * Créateur
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    /**
     * Pilote de processus
     */
    public function piloteProcessus()
    {
        return $this->belongsTo(User::class, 'pilote_processus_id');
    }

    /**
     * Responsable qualité
     */
    public function responsableQualite()
    {
        return $this->belongsTo(User::class, 'responsable_qualite_id');
    }

    /**
     * Plan d'action
     */
    public function actions()
    {
        return $this->hasMany(FicheProgresAction::class, 'fiche_progres_id');
    }

    /**
     * Pièces jointes
     */
    public function piecesJointes()
    {
        return $this->hasMany(FicheProgresPieceJointe::class, 'fiche_progres_id');
    }

    /**
     * Interactions liées
     */
    public function interactions()
    {
        return $this->morphMany(ClientInteraction::class, 'relatable');
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeEnCours($query)
    {
        return $query->whereNotIn('statut', ['cloture', 'non_fonde']);
    }

    public function scopeCloturees($query)
    {
        return $query->where('statut', 'cloture');
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeParProcessus($query, $processus)
    {
        return $query->where('processus_concerne', $processus);
    }

    // ============================================
    // MÉTHODES MÉTIER
    // ============================================

    /**
     * Avancement du workflow en pourcentage
     */
    public function pourcentageAvancement()
    {
        $phases = [
            'nouveau' => 0,
            'analyse_en_cours' => 20,
            'plan_action_etabli' => 40,
            'actions_en_cours' => 60,
            'evaluation' => 80,
            'cloture' => 100,
            'non_fonde' => 100
        ];
        return $phases[$this->statut] ?? 0;
    }

    /**
     * Vérifie si la fiche peut passer à l'évaluation
     */
    public function peutEvaluer()
    {
        if ($this->statut !== 'actions_en_cours') {
            return false;
        }
        // Toutes les actions doivent être réalisées
        return $this->actions()->where('statut', '!=', 'realisee')->count() === 0;
    }

    /**
     * Nombre d'actions réalisées
     */
    public function actionsRealisees()
    {
        return $this->actions()->where('statut', 'realisee')->count();
    }

    /**
     * Total des actions
     */
    public function totalActions()
    {
        return $this->actions()->count();
    }

    /**
     * Libellé du type
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'amelioration' => 'Amélioration',
            'reclamation' => 'Réclamation',
            'incident' => 'Incident',
            'dysfonctionnement' => 'Dysfonctionnement',
            'non_conformite' => 'Non-Conformité'
        ];
        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Libellé du processus
     */
    public function getProcessusLabelAttribute()
    {
        $labels = [
            'gardiennage' => 'Gardiennage',
            'securite_electronique' => 'Sécurité Électronique',
            'securite_incendie' => 'Sécurité Incendie',
            'monetique' => 'Monétique',
            'nettoyage' => 'Nettoyage',
            'formation' => 'Formation',
            'solution_it' => 'Solution IT',
            'comptabilite' => 'Comptabilité',
            'commercial' => 'Commercial',
            'accueil' => 'Accueil'
        ];
        return $labels[$this->processus_concerne] ?? $this->processus_concerne;
    }

    /**
     * Badge du statut
     */
    public function getStatutBadgeAttribute()
    {
        $badges = [
            'nouveau' => '<span class="badge bg-info">Nouveau</span>',
            'analyse_en_cours' => '<span class="badge bg-warning">Analyse en cours</span>',
            'plan_action_etabli' => '<span class="badge bg-primary">Plan d\'action établi</span>',
            'actions_en_cours' => '<span class="badge bg-info">Actions en cours</span>',
            'evaluation' => '<span class="badge bg-secondary">Évaluation</span>',
            'cloture' => '<span class="badge bg-success">Clôturé</span>',
            'non_fonde' => '<span class="badge bg-dark">Non fondé</span>',
        ];
        return $badges[$this->statut] ?? '<span class="badge bg-light">Inconnu</span>';
    }

    /**
     * Durée depuis la création
     */
    public function dureeTraitement()
    {
        $fin = $this->date_cloture ?? now();
        return $this->created_at->diffInDays($fin);
    }
}
