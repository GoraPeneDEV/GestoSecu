<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeEnfant extends Model
{
    use HasFactory;

    /**
     * Nom de la table
     */
    protected $table = 'employe_enfants';

    /**
     * Les attributs qui peuvent être assignés en masse
     */
    protected $fillable = [
        'employe_id',
        'nom_complet',
        'telephone',
        'date_naissance'
    ];

    /**
     * Les attributs qui doivent être castés
     */
    protected $casts = [
        'date_naissance' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Les attributs à formater en dates
     */
    protected $dates = [
        'date_naissance',
        'created_at',
        'updated_at'
    ];

    // ========================================
    // RELATIONS
    // ========================================

    /**
     * Relation avec l'employé
     * Un enfant appartient à un employé
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

    /**
     * Obtenir la date de naissance formatée
     */
    public function getDateNaissanceFormateeAttribute()
    {
        if (!$this->date_naissance) {
            return 'N/A';
        }

        return Carbon::parse($this->date_naissance)->format('d/m/Y');
    }

    /**
     * Calculer l'âge de l'enfant
     */
    public function getAgeAttribute()
    {
        if (!$this->date_naissance) {
            return null;
        }

        return Carbon::parse($this->date_naissance)->age;
    }

    /**
     * Obtenir l'âge formaté avec unité
     */
    public function getAgeFormateAttribute()
    {
        $age = $this->age;

        if ($age === null) {
            return 'N/A';
        }

        if ($age === 0) {
            // Calculer en mois pour les bébés
            $mois = Carbon::parse($this->date_naissance)->diffInMonths(Carbon::now());

            if ($mois === 0) {
                // Calculer en jours
                $jours = Carbon::parse($this->date_naissance)->diffInDays(Carbon::now());
                return $jours . ' jour' . ($jours > 1 ? 's' : '');
            }

            return $mois . ' mois';
        }

        return $age . ' an' . ($age > 1 ? 's' : '');
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
     * Scope pour les enfants avec téléphone
     */
    public function scopeAvecTelephone($query)
    {
        return $query->whereNotNull('telephone')
                     ->where('telephone', '!=', '');
    }

    /**
     * Scope pour les enfants sans téléphone
     */
    public function scopeSansTelephone($query)
    {
        return $query->whereNull('telephone')
                     ->orWhere('telephone', '');
    }

    /**
     * Scope pour les enfants avec date de naissance
     */
    public function scopeAvecDateNaissance($query)
    {
        return $query->whereNotNull('date_naissance');
    }

    /**
     * Scope pour les enfants sans date de naissance
     */
    public function scopeSansDateNaissance($query)
    {
        return $query->whereNull('date_naissance');
    }

    /**
     * Scope pour les enfants mineurs (moins de 18 ans)
     */
    public function scopeMineurs($query)
    {
        $dateMinimale = Carbon::now()->subYears(18);
        return $query->where('date_naissance', '>', $dateMinimale);
    }

    /**
     * Scope pour les enfants majeurs (18 ans et plus)
     */
    public function scopeMajeurs($query)
    {
        $dateMaximale = Carbon::now()->subYears(18);
        return $query->where('date_naissance', '<=', $dateMaximale);
    }

    /**
     * Scope pour les enfants par tranche d'âge
     */
    public function scopeParTrancheAge($query, $ageMin, $ageMax)
    {
        $dateMax = Carbon::now()->subYears($ageMin);
        $dateMin = Carbon::now()->subYears($ageMax + 1)->addDay();

        return $query->whereBetween('date_naissance', [$dateMin, $dateMax]);
    }

    // ========================================
    // MÉTHODES UTILITAIRES
    // ========================================

    /**
     * Vérifier si l'enfant a un numéro de téléphone
     */
    public function aTelephone()
    {
        return !empty($this->telephone);
    }

    /**
     * Vérifier si l'enfant est mineur
     */
    public function estMineur()
    {
        if (!$this->date_naissance) {
            return null;
        }

        return $this->age < 18;
    }

    /**
     * Vérifier si l'enfant est majeur
     */
    public function estMajeur()
    {
        if (!$this->date_naissance) {
            return null;
        }

        return $this->age >= 18;
    }

    /**
     * Obtenir les informations complètes de l'enfant
     */
    public function getInfosCompletes()
    {
        return [
            'id' => $this->id,
            'nom_complet' => $this->nom_complet,
            'telephone' => $this->telephone ?? 'N/A',
            'telephone_formate' => $this->telephone_formate,
            'date_naissance' => $this->date_naissance_formatee,
            'age' => $this->age_formate,
            'est_mineur' => $this->estMineur(),
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
     * Valider la date de naissance (ne doit pas être dans le futur)
     */
    public static function validerDateNaissance($date)
    {
        if (empty($date)) {
            return true; // Date optionnelle
        }

        try {
            $dateCarbon = Carbon::parse($date);
            return $dateCarbon->isPast();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir la catégorie d'âge
     */
    public function getCategorieAge()
    {
        $age = $this->age;

        if ($age === null) {
            return 'Non défini';
        }

        if ($age < 1) {
            return 'Nourrisson';
        } elseif ($age < 3) {
            return 'Bébé';
        } elseif ($age < 6) {
            return 'Petite enfance';
        } elseif ($age < 12) {
            return 'Enfant';
        } elseif ($age < 18) {
            return 'Adolescent';
        } else {
            return 'Adulte';
        }
    }

    // ========================================
    // MUTATEURS (SETTERS)
    // ========================================

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

    /**
     * Formater la date de naissance avant sauvegarde
     */
    public function setDateNaissanceAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['date_naissance'] = null;
        } else {
            try {
                $this->attributes['date_naissance'] = Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->attributes['date_naissance'] = null;
            }
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
        static::creating(function ($enfant) {
            // Nettoyer le nom complet
            $enfant->nom_complet = trim($enfant->nom_complet);

            // Valider la date de naissance
            if ($enfant->date_naissance && !self::validerDateNaissance($enfant->date_naissance)) {
                throw new \Exception('La date de naissance ne peut pas être dans le futur');
            }
        });

        // Avant la mise à jour
        static::updating(function ($enfant) {
            // Nettoyer le nom complet
            $enfant->nom_complet = trim($enfant->nom_complet);

            // Valider la date de naissance
            if ($enfant->date_naissance && !self::validerDateNaissance($enfant->date_naissance)) {
                throw new \Exception('La date de naissance ne peut pas être dans le futur');
            }
        });

        // Après la suppression
        static::deleted(function ($enfant) {
            \Log::info('Enfant supprimé', [
                'id' => $enfant->id,
                'nom_complet' => $enfant->nom_complet,
                'employe_id' => $enfant->employe_id
            ]);
        });
    }
}
