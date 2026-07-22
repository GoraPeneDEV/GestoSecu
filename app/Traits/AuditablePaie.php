<?php

namespace App\Traits;

use App\Models\PaieAuditLog;

trait AuditablePaie
{
    /**
     * Boot the trait
     */
    protected static function bootAuditablePaie()
    {
        // Log création
        static::created(function ($model) {
            $model->logAudit(
                PaieAuditLog::ACTION_CREATED,
                'Création de ' . class_basename($model),
                null,
                $model->getAuditableAttributes()
            );
        });

        // Log modification
        static::updated(function ($model) {
            if ($model->isDirty()) {
                $model->logAudit(
                    PaieAuditLog::ACTION_UPDATED,
                    'Modification de ' . class_basename($model),
                    $model->getOriginal(),
                    $model->getAuditableAttributes()
                );
            }
        });

        // Log suppression
        static::deleted(function ($model) {
            $model->logAudit(
                PaieAuditLog::ACTION_DELETED,
                'Suppression de ' . class_basename($model),
                $model->getAuditableAttributes(),
                null
            );
        });
    }

    /**
     * Créer un log d'audit pour ce modèle
     */
    public function logAudit(
        string $action,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $severity = PaieAuditLog::SEVERITY_INFO
    ): PaieAuditLog {
        return PaieAuditLog::log(
            $action,
            get_class($this),
            $this->id,
            $description,
            $oldValues,
            $newValues,
            $this->mois ?? null,
            $this->annee ?? null,
            $this->employe_id ?? null,
            $severity
        );
    }

    /**
     * Obtenir les attributs à auditer
     */
    protected function getAuditableAttributes(): array
    {
        $attributes = $this->getAttributes();

        // Exclure les timestamps et attributs sensibles
        $exclude = ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

        return array_diff_key($attributes, array_flip($exclude));
    }

    /**
     * Relation vers les logs d'audit
     */
    public function auditLogs()
    {
        return $this->morphMany(PaieAuditLog::class, 'entity', 'entity_type', 'entity_id');
    }
}
