<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

class Garantie extends Model
{
    use HasFactory;

    protected $table = 'garanties';

    protected $fillable = [
        'numero_garantie',
        'contrat_id',
        'client_id',
        'type',
        'date_debut',
        'date_fin',
        'duree_mois',
        'conditions',
        'exclusions',
        'statut',
        'nombre_reclamations',
        'alerte_30_jours_envoyee',
        'alerte_7_jours_envoyee',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'duree_mois' => 'integer',
        'nombre_reclamations' => 'integer',
        'alerte_30_jours_envoyee' => 'boolean',
        'alerte_7_jours_envoyee' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($garantie) {
            if (empty($garantie->numero_garantie)) {
                $garantie->numero_garantie = self::genererNumero();
            }
        });
    }

    public static function genererNumero()
    {
        $annee = date('Y');
        $dernier = self::whereYear('created_at', $annee)->orderBy('id', 'desc')->first();
        $numero = $dernier ? intval(substr($dernier->numero_garantie, -4)) + 1 : 1;

        return sprintf('GA-%s-%04d', $annee, $numero);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function scopeActives($query)
    {
        return $query->where('statut', 'active');
    }

    public function scopeExpirant($query, $jours = 30)
    {
        return $query->where('date_fin', '<=', now()->addDays($jours))->where('date_fin', '>=', now())->where('statut', 'active');
    }

    public function estActive()
    {
        return $this->statut === 'active' && $this->date_debut <= now() && $this->date_fin >= now();
    }

    public function joursRestants()
    {
        if ($this->date_fin < now()) {
            return 0;
        }
        return now()->diffInDays($this->date_fin);
    }

    public function getTypeLabelAttribute()
    {
        $labels = ['main_oeuvre' => 'Main d\'œuvre', 'pieces' => 'Pièces', 'totale' => 'Totale'];
        return $labels[$this->type] ?? $this->type;
    }

    public function getStatutBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'expiree' => '<span class="badge bg-secondary">Expirée</span>',
            'resiliee' => '<span class="badge bg-dark">Résiliée</span>',
            'en_reclamation' => '<span class="badge bg-warning">En réclamation</span>',
        ];
        return $badges[$this->statut] ?? '<span class="badge bg-light">Inconnu</span>';
    }
}
