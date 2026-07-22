<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandeExplication extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'demandes_explications';

  protected $fillable = [
    'numero_demande',
    'employe_id',
    'motif',
    'description',
    'date_incident',
    'statut',
    'document_path',
    'reponse_document_path', // Nouveau champ pour le document de réponse
    'date_reponse',
    'cree_par'
  ];

  protected $casts = [
    'date_incident' => 'date',
    'date_reponse' => 'date',
    'deleted_at' => 'datetime'
  ];

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($demande) {
      if (empty($demande->numero_demande)) {
        $year = now()->format('Y');
        $lastDemande = self::whereYear('created_at', $year)->latest('id')->first();
        $nextNumber = $lastDemande ? intval(substr($lastDemande->numero_demande, 3, -5)) + 1 : 1;
        $demande->numero_demande = 'DE-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT) . '/' . $year;
      }
    });
  }

  /**
   * Relation avec l'employé concerné
   */
  public function employe()
  {
    return $this->belongsTo(Employe::class, 'employe_id');
  }

  /**
   * Relation avec l'utilisateur qui a créé la demande
   */
  public function createur()
  {
    return $this->belongsTo(User::class, 'cree_par');
  }

  /**
   * Vérifie si la demande est en attente
   */
  public function isEnAttente()
  {
    return $this->statut === 'en_attente';
  }

  /**
   * Vérifie si la demande a été répondue
   */
  public function isRepondue()
  {
    return $this->statut === 'repondue';
  }

  /**
   * Vérifie si la demande a un document de réponse
   */
  public function hasReponseDocument()
  {
    return !empty($this->reponse_document_path);
  }

  /**
   * Obtient l'URL du document de réponse
   */
  public function getReponseDocumentUrl()
  {
    return $this->reponse_document_path ? asset('storage/' . $this->reponse_document_path) : null;
  }

  /**
   * Scope pour les demandes en attente
   */
  public function scopeEnAttente($query)
  {
    return $query->where('statut', 'en_attente');
  }

  /**
   * Scope pour les demandes répondues
   */
  public function scopeRepondues($query)
  {
    return $query->where('statut', 'repondue');
  }
}
