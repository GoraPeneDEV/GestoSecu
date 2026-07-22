<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandeAbsenceAdmin extends Model
{
  use SoftDeletes;

  protected $table = 'demandes_absences_administration';


  protected $fillable = [
    'id_employe',
    'type_conges',
    'date_debut',
    'date_fin',
    'nbr_jour',
    'motif',
    'statut',
    'commentaire_sup',
    'commentaire_rh',
    'date_validation_sup',
    'date_val_rh',
    'id_superieur',
    'id_rh',
    'a_deduire',
    'document_path',
    'date_enregistrement',
    'cree_par',
    'id_rh_annulation',
    'motif_annulation_rh',
    'date_annulation'
  ];

  protected $casts = [
    'date_debut' => 'date',
    'date_fin' => 'date',
    'date_validation_sup' => 'datetime',
    'date_val_rh' => 'datetime',
    'date_enregistrement' => 'datetime',
    'date_annulation' => 'datetime',
    'a_deduire' => 'boolean',
  ];

  // Constantes pour les statuts
  const STATUT_EN_ATTENTE = 'en_attente';
  const STATUT_VALIDE_SUPERIEUR = 'valide_superieur';
  const STATUT_VALIDE_RH = 'valide_rh';
  const STATUT_REFUSE_SUPERIEUR = 'refuse_superieur';
  const STATUT_REFUSE_RH = 'refuse_rh';
  const STATUT_ANNULE = 'annule';

  // Types de congés
  const TYPE_CONGE_ANNUEL = 'conge_annuel';
  const TYPE_AUTORISATION_ABSENCE = 'autorisation_absence';
  const TYPE_CONGE_MALADIE = 'conge_maladie';
  const TYPE_CONGE_MATERNITE = 'conge_maternite';
  const TYPE_CONGE_MARIAGE = 'conge_mariage';
  const TYPE_DECES = 'deces';

  /**
   * Relation avec l'employé qui fait la demande
   */
  public function employe()
  {
    return $this->belongsTo(Employe::class, 'id_employe');
  }

  /**
   * Relation avec l'utilisateur qui a validé en tant que supérieur
   */
  public function superieur()
  {
    return $this->belongsTo(User::class, 'id_superieur');
  }

  /**
   * Relation avec l'utilisateur RH qui a validé
   */
  public function responsableRH()
  {
    return $this->belongsTo(User::class, 'id_rh');
  }

  /**
   * Relation avec l'utilisateur RH qui a annulé la demande
   */
  public function responsableAnnulation()
  {
    return $this->belongsTo(User::class, 'id_rh_annulation');
  }

  /**
   * Relation avec l'utilisateur qui a créé la demande
   */
  public function createur()
  {
    return $this->belongsTo(User::class, 'cree_par');
  }

  /**
   * Vérifie si la demande peut être annulée (par RH)
   */
  public function peutEtreAnnule()
  {
    return in_array($this->statut, [
      self::STATUT_VALIDE_RH
    ]);
  }

  /**
   * Vérifie si le créateur peut annuler sa propre demande
   * Possible uniquement si la demande est en attente ou validée par le supérieur
   */
  public function peutEtreAnnuleParCreateur(int $userId): bool
  {
    return $this->cree_par === $userId
      && in_array($this->statut, [
        self::STATUT_EN_ATTENTE,
        self::STATUT_VALIDE_SUPERIEUR,
      ]);
  }

  /**
   * Vérifie si la demande est en cours de traitement
   */
  public function estEnCours()
  {
    return in_array($this->statut, [
      self::STATUT_EN_ATTENTE,
      self::STATUT_VALIDE_SUPERIEUR
    ]);
  }

  /**
   * Vérifie si la demande est terminée (validée ou refusée)
   */
  public function estTerminee()
  {
    return in_array($this->statut, [
      self::STATUT_VALIDE_RH,
      self::STATUT_REFUSE_SUPERIEUR,
      self::STATUT_REFUSE_RH,
      self::STATUT_ANNULE
    ]);
  }

  /**
   * Vérifie si la demande a été validée
   */
  public function estValidee()
  {
    return $this->statut === self::STATUT_VALIDE_RH;
  }

  /**
   * Vérifie si la demande a été refusée
   */
  public function estRefusee()
  {
    return in_array($this->statut, [
      self::STATUT_REFUSE_SUPERIEUR,
      self::STATUT_REFUSE_RH
    ]);
  }

  /**
   * Retourne le libellé du statut
   */
  public function getStatutLibelleAttribute()
  {
    $statuts = [
      self::STATUT_EN_ATTENTE => 'En attente',
      self::STATUT_VALIDE_SUPERIEUR => 'Validé par le supérieur',
      self::STATUT_VALIDE_RH => 'Validé par les RH',
      self::STATUT_REFUSE_SUPERIEUR => 'Refusé par le supérieur',
      self::STATUT_REFUSE_RH => 'Refusé par les RH',
      self::STATUT_ANNULE => 'Annulé'
    ];

    return $statuts[$this->statut] ?? $this->statut;
  }

  /**
   * Retourne le libellé du type de congé
   */
  public function getTypeCongesLibelleAttribute()
  {
    $types = [
      self::TYPE_CONGE_ANNUEL => 'Congé annuel',
      self::TYPE_AUTORISATION_ABSENCE => 'Autorisation d\'absence',
      self::TYPE_CONGE_MALADIE => 'Congé maladie',
      self::TYPE_CONGE_MATERNITE => 'Congé maternité',
      self::TYPE_CONGE_MARIAGE => 'Congé mariage',
      self::TYPE_DECES => 'Décès'
    ];

    return $types[$this->type_conges] ?? $this->type_conges;
  }

  /**
   * Retourne la classe CSS pour le badge du statut
   */
  public function getStatutBadgeClassAttribute()
  {
    $classes = [
      self::STATUT_EN_ATTENTE => 'bg-warning',
      self::STATUT_VALIDE_SUPERIEUR => 'bg-info',
      self::STATUT_VALIDE_RH => 'bg-success',
      self::STATUT_REFUSE_SUPERIEUR => 'bg-danger',
      self::STATUT_REFUSE_RH => 'bg-danger',
      self::STATUT_ANNULE => 'bg-secondary'
    ];

    return $classes[$this->statut] ?? 'bg-secondary';
  }

  /**
   * Scope pour filtrer par statut
   */
  public function scopeParStatut($query, $statut)
  {
    return $query->where('statut', $statut);
  }

  /**
   * Scope pour filtrer par employé
   */
  public function scopeParEmploye($query, $employeId)
  {
    return $query->where('id_employe', $employeId);
  }

  /**
   * Scope pour filtrer par département
   */
  public function scopeParDepartement($query, $departementId)
  {
    return $query->whereHas('employe', function ($q) use ($departementId) {
      $q->where('id_departement', $departementId);
    });
  }

  /**
   * Scope pour filtrer par période
   */
  public function scopeParPeriode($query, $dateDebut, $dateFin)
  {
    return $query->whereBetween('date_debut', [$dateDebut, $dateFin]);
  }

  /**
   * Scope pour les demandes en cours
   */
  public function scopeEnCours($query)
  {
    return $query->whereIn('statut', [
      self::STATUT_EN_ATTENTE,
      self::STATUT_VALIDE_SUPERIEUR
    ]);
  }

  /**
   * Scope pour les demandes terminées
   */
  public function scopeTerminees($query)
  {
    return $query->whereIn('statut', [
      self::STATUT_VALIDE_RH,
      self::STATUT_REFUSE_SUPERIEUR,
      self::STATUT_REFUSE_RH,
      self::STATUT_ANNULE
    ]);
  }

  /**
   * Calcule la durée en jours ouvrables
   */
  public function calculerDureeOuvrables()
  {
    if (!$this->date_debut || !$this->date_fin) {
      return 0;
    }

    $debut = Carbon::parse($this->date_debut);
    $fin = Carbon::parse($this->date_fin);
    $jours = 0;

    // Récupérer les jours fériés
    $joursFeries = \App\Models\JourFerier::whereBetween('date_ferier', [$debut, $fin])
      ->pluck('date_ferier')
      ->toArray();

    $dateCourante = $debut->copy();
    while ($dateCourante->lte($fin)) {
      // Exclure les dimanches et les jours fériés
      if (
        $dateCourante->dayOfWeek !== Carbon::SUNDAY &&
        !in_array($dateCourante->format('Y-m-d'), $joursFeries)
      ) {
        $jours++;
      }
      $dateCourante->addDay();
    }

    return $jours;
  }

  /**
   * Retourne l'URL du document
   */
  public function getDocumentUrlAttribute()
  {
    return $this->document_path ? asset('storage/' . $this->document_path) : null;
  }

  /**
   * Vérifie si un document est attaché
   */
  public function hasDocument()
  {
    return !empty($this->document_path);
  }
}
