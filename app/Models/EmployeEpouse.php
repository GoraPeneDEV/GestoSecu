<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeEpouse extends Model
{
    use HasFactory;

    /**
     * Nom de la table
     */
    protected $table = 'employe_epouses';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'employe_id',
        'nom_complet',
        'telephone'
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========================================
    // RELATIONS
    // ========================================

    /**
     * Relation avec l'employé
     * Une épouse appartient à un employé
     */
    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id', 'id');
    }

    // ========================================
    // ACCESSEURS (GETTERS)
    // ========================================

    /**
     * Obtenir le nom complet formaté
     */
    public function getNomCompletFormateAttribute()
    {
        return ucwords(strtolower($this->nom_complet));
    }

    /**
     * Obtenir le téléphone formaté
     */
    public function getTelephoneFormateAttribute()
    {
        if (!$this->telephone) {
            return 'N/A';
        }

        // Format : +221 XX XXX XX XX
        $phone = preg_replace('/[^0-9]/', '', $this->telephone);

        if (strlen($phone) === 9) {
            return '+221 ' . substr($phone, 0, 2) . ' ' .
                   substr($phone, 2, 3) . ' ' .
                   substr($phone, 5, 2) . ' ' .
                   substr($phone, 7, 2);
        }

        return $this->telephone;
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope pour rechercher par nom
     */
    public function scopeRechercherParNom($query, $nom)
    {
        return $query->where('nom_complet', 'LIKE', "%{$nom}%");
    }

    /**
     * Scope pour les épouses avec téléphone
     */
    public function scopeAvecTelephone($query)
    {
        return $query->whereNotNull('telephone')
                     ->where('telephone', '!=', '');
    }

    /**
     * Scope pour les épouses sans téléphone
     */
    public function scopeSansTelephone($query)
    {
        return $query->whereNull('telephone')
                     ->orWhere('telephone', '');
    }

    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    /**
     * Vérifier si l'épouse a un numéro de téléphone
     */
    public function aTelephone()
    {
        return !empty($this->telephone);
    }

    /**
     * Obtenir les informations complètes de l'épouse
     */
    public function getInfosCompletes()
    {
        return [
            'id' => $this->id,
            'nom_complet' => $this->nom_complet,
            'telephone' => $this->telephone ?? 'N/A',
            'telephone_formate' => $this->telephone_formate,
            'employe' => [
                'id' => $this->employe->id ?? null,
                'nom_complet' => $this->employe ? $this->employe->prenom . ' ' . $this->employe->nom : 'N/A'
            ],
            'date_ajout' => $this->created_at ? $this->created_at->format('d/m/Y') : 'N/A'
        ];
    }

    /**
     * Valider le format du téléphone sénégalais
     */
    public static function validerTelephone($telephone)
    {
        if (empty($telephone)) {
            return true; // Téléphone optionnel
        }

        // Retirer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $telephone);

        // Vérifier si c'est un numéro sénégalais valide (9 chiffres)
        // ou avec indicatif pays (12 chiffres : 221 + 9 chiffres)
        return (strlen($phone) === 9 || strlen($phone) === 12);
    }

    /**
     * Formater le téléphone avant sauvegarde
     */
    public function setTelephoneAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['telephone'] = null;
        } else {
            // Nettoyer et formater le numéro
            $phone = preg_replace('/[^0-9]/', '', $value);
            $this->attributes['telephone'] = $phone;
        }
    }

    // ========================================
    // ÉVÉNEMENTS DU MODÈLE
    // ========================================

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Avant la création
        static::creating(function ($epouse) {
            // Nettoyer le nom complet
            $epouse->nom_complet = trim($epouse->nom_complet);
        });

        // Avant la mise à jour
        static::updating(function ($epouse) {
            // Nettoyer le nom complet
            $epouse->nom_complet = trim($epouse->nom_complet);
        });

        // Après la suppression
        static::deleted(function ($epouse) {
            \Log::info('Épouse supprimée', [
                'id' => $epouse->id,
                'nom_complet' => $epouse->nom_complet,
                'employe_id' => $epouse->employe_id
            ]);
        });
    }
}
