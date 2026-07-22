<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\User;

class ClientInteraction extends Model
{
    use HasFactory;

    protected $table = 'client_interactions';

    protected $fillable = [
        'client_id',
        'type',
        'sujet',
        'contenu',
        'canal',
        'sens',
        'contact_client_id',
        'user_id',
        'relatable_type',
        'relatable_id',
        'statut',
        'rappel_le',
        'rappel_attribue_a',
        'pieces_jointes'
    ];

    protected $casts = [
        'rappel_le' => 'datetime',
        'pieces_jointes' => 'array'
    ];

    /**
     * Client associé
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Contact client
     */
    public function contact()
    {
        return $this->belongsTo(ClientContact::class, 'contact_client_id');
    }

    /**
     * Employé Vigilus
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Attribué à (pour les relances)
     */
    public function attribueA()
    {
        return $this->belongsTo(User::class, 'rappel_attribue_a');
    }

    /**
     * Relation polymorphique
     */
    public function relatable()
    {
        return $this->morphTo();
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeARappeler($query)
    {
        return $query->whereNotNull('rappel_le')
                     ->where('rappel_le', '<=', now())
                     ->where('statut', '!=', 'traite');
    }

    public function scopeUrgentes($query)
    {
        return $query->where('statut', 'urgent');
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeEntrants($query)
    {
        return $query->where('sens', 'entrant');
    }

    // ============================================
    // MÉTHODES MÉTIER
    // ============================================

    /**
     * Marquer comme traité
     */
    public function marquerTraite()
    {
        $this->update(['statut' => 'traite']);
    }

    /**
     * Programmer un rappel
     */
    public function programmerRappel($date, $userId)
    {
        $this->update([
            'rappel_le' => $date,
            'rappel_attribue_a' => $userId,
            'statut' => 'en_attente'
        ]);
    }

    /**
     * Libellé du type
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'appel_entrant' => 'Appel entrant',
            'appel_sortant' => 'Appel sortant',
            'email_recu' => 'Email reçu',
            'email_envoye' => 'Email envoyé',
            'reunion' => 'Réunion',
            'visite_site' => 'Visite sur site',
            'ticket_sav' => 'Ticket SAV',
            'contrat_signe' => 'Contrat signé',
            'facture' => 'Facture',
            'relance' => 'Relance',
            'autre' => 'Autre'
        ];
        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Icône du type
     */
    public function getTypeIconAttribute()
    {
        $icons = [
            'appel_entrant' => 'ti-phone-incoming',
            'appel_sortant' => 'ti-phone-outgoing',
            'email_recu' => 'ti-mail-opened',
            'email_envoye' => 'ti-mail-forward',
            'reunion' => 'ti-users',
            'visite_site' => 'ti-map-pin',
            'ticket_sav' => 'ti-ticket',
            'contrat_signe' => 'ti-file-signature',
            'facture' => 'ti-file-invoice',
            'relance' => 'ti-bell',
            'autre' => 'ti-more'
        ];
        return $icons[$this->type] ?? 'ti-more';
    }

    /**
     * Badge du statut
     */
    public function getStatutBadgeAttribute()
    {
        $badges = [
            'a_traiter' => '<span class="badge bg-warning">À traiter</span>',
            'en_attente' => '<span class="badge bg-info">En attente</span>',
            'traite' => '<span class="badge bg-success">Traité</span>',
            'urgent' => '<span class="badge bg-danger">Urgent</span>',
        ];
        return $badges[$this->statut] ?? '<span class="badge bg-light">Inconnu</span>';
    }

    /**
     * Couleur selon le canal
     */
    public function getCanalColorAttribute()
    {
        $colors = [
            'telephone' => 'primary',
            'email' => 'info',
            'reunion' => 'success',
            'portail' => 'warning',
            'courrier' => 'secondary',
            'autre' => 'light'
        ];
        return $colors[$this->canal] ?? 'light';
    }
}
