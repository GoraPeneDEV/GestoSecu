<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaieAlert extends Model
{
    use SoftDeletes;

    protected $table = 'paie_alerts';

    protected $fillable = [
        'type',
        'titre',
        'message',
        'niveau',
        'periode_mois',
        'periode_annee',
        'data',
        'est_lue',
        'lue_par',
        'date_lecture',
        'expire_le',
    ];

    protected $casts = [
        'data' => 'array',
        'est_lue' => 'boolean',
        'date_lecture' => 'datetime',
        'expire_le' => 'datetime',
        'periode_mois' => 'integer',
        'periode_annee' => 'integer',
    ];

    const TYPE_VARIABLES_NON_SAISIES = 'variables_non_saisies';
    const TYPE_BULLETINS_NON_VALIDES = 'bulletins_non_valides';
    const TYPE_DECLARATION_IPRES = 'declaration_ipres';
    const TYPE_DECLARATION_CSS = 'declaration_css';
    const TYPE_DECLARATION_VRS = 'declaration_vrs';
    const TYPE_DONNEES_BANCAIRES_MANQUANTES = 'donnees_bancaires_manquantes';
    const TYPE_DONNEES_PAIE_MANQUANTES = 'donnees_paie_manquantes';
    const TYPE_BAREMES_OBSOLETES = 'baremes_obsoletes';

    const NIVEAU_INFO = 'info';
    const NIVEAU_WARNING = 'warning';
    const NIVEAU_DANGER = 'danger';

    /**
     * Relations
     */
    public function lecteur()
    {
        return $this->belongsTo(User::class, 'lue_par');
    }

    /**
     * Scopes
     */
    public function scopeNonLues($query)
    {
        return $query->where('est_lue', false);
    }

    public function scopeActives($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expire_le')
                ->orWhere('expire_le', '>=', now());
        });
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeNiveau($query, string $niveau)
    {
        return $query->where('niveau', $niveau);
    }

    public function scopePeriode($query, int $mois, int $annee)
    {
        return $query->where('periode_mois', $mois)
                     ->where('periode_annee', $annee);
    }

    /**
     * Marquer l'alerte comme lue
     */
    public function marquerCommeLue($userId = null): bool
    {
        return $this->update([
            'est_lue' => true,
            'lue_par' => $userId ?? auth()->id(),
            'date_lecture' => now(),
        ]);
    }

    /**
     * Vérifier si l'alerte est expirée
     */
    public function estExpiree(): bool
    {
        return $this->expire_le && $this->expire_le->isPast();
    }

    /**
     * Obtenir la classe CSS pour le niveau
     */
    public function getCssClass(): string
    {
        return match($this->niveau) {
            self::NIVEAU_INFO => 'alert-info',
            self::NIVEAU_WARNING => 'alert-warning',
            self::NIVEAU_DANGER => 'alert-danger',
            default => 'alert-secondary',
        };
    }

    /**
     * Obtenir l'icône pour le type
     */
    public function getIcon(): string
    {
        return match($this->type) {
            self::TYPE_VARIABLES_NON_SAISIES => 'ti-edit',
            self::TYPE_BULLETINS_NON_VALIDES => 'ti-file-check',
            self::TYPE_DECLARATION_IPRES, self::TYPE_DECLARATION_CSS, self::TYPE_DECLARATION_VRS => 'ti-file-upload',
            self::TYPE_DONNEES_BANCAIRES_MANQUANTES => 'ti-credit-card',
            self::TYPE_DONNEES_PAIE_MANQUANTES => 'ti-user-exclamation',
            self::TYPE_BAREMES_OBSOLETES => 'ti-alert-triangle',
            default => 'ti-bell',
        };
    }
}
