<?php

namespace App\Notifications;

use App\Models\DemandeAbsenceAdmin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DemandeAbsenceAdminNotification extends Notification
{
    use Queueable;

    protected DemandeAbsenceAdmin $demande;
    protected string $type;

    public function __construct(DemandeAbsenceAdmin $demande, string $type)
    {
        $this->demande = $demande;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $employe = $this->demande->employe;
        $nomEmploye = $employe ? trim($employe->prenom . ' ' . $employe->nom) : 'Employé';

        return (new MailMessage())
            ->subject($this->titre())
            ->line($this->message())
            ->line('Employé : ' . $nomEmploye)
            ->line('Période : ' . $this->demande->date_debut?->format('d/m/Y') . ' au ' . $this->demande->date_fin?->format('d/m/Y'))
            ->action('Voir la demande', route('absences-admin.show', $this->demande));
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'demande_absence_admin',
            'action' => $this->type,
            'demande_id' => $this->demande->id,
            'titre' => $this->titre(),
            'message' => $this->message(),
        ];
    }

    protected function titre(): string
    {
        return match ($this->type) {
            'creation' => 'Nouvelle demande d\'absence',
            'validation_superieur' => 'Demande d\'absence traitée (niveau 1)',
            'validation_rh' => 'Demande d\'absence traitée (RH)',
            'annulation' => 'Demande d\'absence annulée',
            default => 'Mise à jour d\'une demande d\'absence',
        };
    }

    protected function message(): string
    {
        return match ($this->type) {
            'creation' => 'Une nouvelle demande d\'absence a été soumise.',
            'validation_superieur' => 'La demande a été traitée par le responsable hiérarchique : ' . $this->demande->statut_libelle . '.',
            'validation_rh' => 'La demande a été traitée par les RH : ' . $this->demande->statut_libelle . '.',
            'annulation' => 'La demande a été annulée : ' . $this->demande->motif_annulation_rh,
            default => 'Statut mis à jour : ' . $this->demande->statut_libelle,
        };
    }
}
