<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Immobilisation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'immobilisations';

    protected $fillable = [
        'code_interne',
        'designation',
        'description',
        'numero_serie',
        'categorie_id',
        'site_id',
        'emplacement_id',
        'date_acquisition',
        'valeur_acquisition',
        'numero_facture',
        'article_id',
        'statut',
        'employe_id',
        'date_affectation',
        'methode_amortissement',
        'taux_amortissement',
        'duree_amortissement_annees',
        'date_debut_amortissement',
        'valeur_residuelle',
        'valeur_nette_comptable',
        'qr_token',
        'qr_code_path',
        'created_by',
    ];

    protected $casts = [
        'date_acquisition' => 'date',
        'date_affectation' => 'date',
        'date_debut_amortissement' => 'date',
        'valeur_acquisition' => 'decimal:2',
        'valeur_residuelle' => 'decimal:2',
        'valeur_nette_comptable' => 'decimal:2',
        'taux_amortissement' => 'decimal:2',
        'duree_amortissement_annees' => 'integer',
    ];

    public function categorie()
    {
        return $this->belongsTo(ImmobilisationCategorie::class, 'categorie_id');
    }

    public function site()
    {
        return $this->belongsTo(ImmobilisationSite::class, 'site_id');
    }

    public function emplacement()
    {
        return $this->belongsTo(ImmobilisationEmplacement::class, 'emplacement_id');
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    public function affectations()
    {
        return $this->hasMany(ImmobilisationAffectation::class, 'immobilisation_id');
    }

    public function mouvements()
    {
        return $this->hasMany(ImmobilisationMouvement::class, 'immobilisation_id');
    }

    public function amortissementLignes()
    {
        return $this->hasMany(AmortissementLigne::class, 'immobilisation_id');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeParStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopeEnStock($query)
    {
        return $query->where('statut', 'en_stock');
    }

    public function scopeAffectes($query)
    {
        return $query->where('statut', 'affecte');
    }

    public function scopeParEmploye($query, $employeId)
    {
        return $query->where('employe_id', $employeId);
    }

    public function scopeParSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    public function scopeDotables($query)
    {
        return $query->whereHas('categorie', fn($q) => $q->where('est_dotable', true));
    }

    public function getEstDotableAttribute()
    {
        return $this->categorie && $this->categorie->est_dotable;
    }

    public function getEstAmortissableAttribute()
    {
        return $this->categorie && $this->categorie->est_amortissable;
    }

    public function getAgeAnneesAttribute()
    {
        return $this->date_acquisition->diffInYears(now());
    }

    public function getValeurActuelleAttribute()
    {
        return $this->valeur_nette_comptable ?? $this->calculerValeurNette();
    }

    public function getAffectationActuelleAttribute()
    {
        return $this->affectations()->whereNull('date_fin_reelle')->latest('date_affectation')->first();
    }

    public function calculerValeurNette()
    {
        if (!$this->est_amortissable) {
            return $this->valeur_acquisition;
        }

        $cumul = $this->amortissementLignes()->sum('montant_amortissement');
        return max(0, $this->valeur_acquisition - $cumul - $this->valeur_residuelle);
    }

    public static function genererCode(?int $categorieId = null)
    {
        $prefix = 'IMM';

        if ($categorieId) {
            $categorie = ImmobilisationCategorie::find($categorieId);
            if ($categorie && !empty($categorie->code)) {
                $prefix = strtoupper($categorie->code);
            }
        }

        $derniere = static::where('code_interne', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(code_interne, "-", -1) AS UNSIGNED) DESC')
            ->value('code_interne');

        $prochain = 1;
        if ($derniere) {
            $parts = explode('-', $derniere);
            $prochain = ((int) end($parts)) + 1;
        }

        return $prefix . '-' . str_pad($prochain, 3, '0', STR_PAD_LEFT);
    }

    public static function genererQrToken()
    {
        return Str::random(32);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($immobilisation) {
            if (empty($immobilisation->code_interne)) {
                $immobilisation->code_interne = static::genererCode($immobilisation->categorie_id);
            }
            if (empty($immobilisation->qr_token)) {
                $immobilisation->qr_token = static::genererQrToken();
            }
            if (empty($immobilisation->date_debut_amortissement)) {
                $immobilisation->date_debut_amortissement = $immobilisation->date_acquisition;
            }
        });

        static::created(function ($immobilisation) {
            $immobilisation->mouvements()->create([
                'type_mouvement' => 'creation',
                'motif' => 'Création de l\'immobilisation',
                'created_by' => $immobilisation->created_by,
            ]);
        });
    }
}
