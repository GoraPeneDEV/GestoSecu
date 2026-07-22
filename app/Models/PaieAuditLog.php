<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaieAuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'mois',
        'annee',
        'employe_id',
        'severity',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'mois' => 'integer',
        'annee' => 'integer',
    ];

    // Actions possibles
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_VALIDATED = 'validated';
    public const ACTION_SENT = 'sent';
    public const ACTION_GENERATED = 'generated';
    public const ACTION_EXPORTED = 'exported';
    public const ACTION_LOCKED = 'locked';
    public const ACTION_UNLOCKED = 'unlocked';

    // Niveaux de sévérité
    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_CRITICAL = 'critical';

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    /**
     * Scopes
     */
    public function scopeForEntity($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)
            ->where('entity_id', $entityId);
    }

    public function scopeForPeriod($query, int $mois, int $annee)
    {
        return $query->where('mois', $mois)
            ->where('annee', $annee);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    /**
     * Créer un log d'audit
     */
    public static function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $mois = null,
        ?int $annee = null,
        ?int $employeId = null,
        string $severity = self::SEVERITY_INFO
    ): self {
        $user = auth()->user();

        return self::create([
            'user_id' => $user?->id,
            'user_name' => $user ? $user->nom_complet : 'System',
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ],
            'mois' => $mois,
            'annee' => $annee,
            'employe_id' => $employeId,
            'severity' => $severity,
        ]);
    }

    /**
     * Obtenir une description lisible de l'action
     */
    public function getReadableActionAttribute(): string
    {
        $actions = [
            self::ACTION_CREATED => 'Création',
            self::ACTION_UPDATED => 'Modification',
            self::ACTION_DELETED => 'Suppression',
            self::ACTION_VALIDATED => 'Validation',
            self::ACTION_SENT => 'Envoi',
            self::ACTION_GENERATED => 'Génération',
            self::ACTION_EXPORTED => 'Export',
            self::ACTION_LOCKED => 'Verrouillage',
            self::ACTION_UNLOCKED => 'Déverrouillage',
        ];

        return $actions[$this->action] ?? $this->action;
    }

    /**
     * Obtenir les changements sous forme lisible
     */
    public function getChangesAttribute(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }
}
