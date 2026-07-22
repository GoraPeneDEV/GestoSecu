<?php

namespace App\Notifications;

use App\Models\SupervisorVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SupervisionAlertNotification extends Notification
{
    use Queueable;

    protected $visit;

    public function __construct(SupervisorVisit $visit)
    {
        $this->visit = $visit;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $visit = $this->visit;

        $mail = (new MailMessage)
            ->subject('Alerte supervision [' . $visit->status . '] - ' . ($visit->site->nom_site ?? 'Site inconnu'))
            ->greeting('Bonjour,')
            ->line('Une visite de supervision a été enregistrée avec le statut "' . $visit->status . '" le ' . $visit->created_at->format('d/m/Y à H:i') . '.')
            ->line('Site: ' . ($visit->site->nom_site ?? 'Inconnu'))
            ->line('Superviseur: ' . ($visit->supervisor ? $visit->supervisor->prenom . ' ' . $visit->supervisor->nom : 'Inconnu'))
            ->line('Agents attendus: ' . $visit->expected_agents_count . ' / présents: ' . $visit->actual_agents_count);

        if ($visit->actual_agents_count < $visit->expected_agents_count) {
            $mail->line('⚠️ Agents manquants détectés.' . ($visit->missing_agents_details ? ' Motif: ' . $visit->missing_agents_details : ''));
        }

        if ($visit->notes) {
            $mail->line('Notes du superviseur: ' . $visit->notes);
        }

        if ($visit->photo_path) {
            $mail->attach(storage_path('app/public/' . $visit->photo_path));
        }

        $mail->action('Voir les rapports de supervision', route('supervision.visites.index'));

        return $mail;
    }
}
