<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulletinLigne extends Model
{
    protected $table = 'bulletin_ligne';

    protected $fillable = [
        'bulletin_paie_id',
        'element_paie_id',
        'code_element',
        'libelle',
        'type',
        'base_calcul',
        'taux',
        'nombre',
        'montant',
        'ordre_affichage',
    ];

    protected $casts = [
        'base_calcul' => 'decimal:2',
        'taux' => 'decimal:2',
        'nombre' => 'decimal:2',
        'montant' => 'decimal:2',
        'ordre_affichage' => 'integer',
    ];

    /**
     * Relation avec le bulletin
     */
    public function bulletin(): BelongsTo
    {
        return $this->belongsTo(BulletinPaie::class, 'bulletin_paie_id');
    }

    /**
     * Relation avec l'élément de paie
     */
    public function elementPaie(): BelongsTo
    {
        return $this->belongsTo(ElementPaie::class, 'element_paie_id');
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour ordonner par ordre d'affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre_affichage');
    }
}
