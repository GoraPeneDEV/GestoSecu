<?php

namespace App\Notifications;

use App\Models\Ronde;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RondeAnomaliesNotification extends Notification
{
    use Queueable;

    protected $ronde;

    public function __construct(Ronde $ronde)
    {
        $this->ronde = $ronde;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $anomalies = $this->ronde->scans()->where('anomalie', true)->with('pointControle')->get();

        $mail = (new MailMessage)
            ->subject('Rapport d\'anomalies - Ronde #' . $this->ronde->id)
            ->greeting('Bonjour,')
            ->line('Des anomalies ont été détectées lors de la ronde effectuée le ' . $this->ronde->date_debut->format('d/m/Y à H:i'))
            ->line('Site: ' . $this->ronde->planningRonde->site->nom_site)
            ->line('Agent: ' . $this->ronde->agent->prenom . ' ' . $this->ronde->agent->nom)
            ->line('Détails des anomalies:');

        foreach ($anomalies as $anomalie) {
            $mail->line('- Point: ' . $anomalie->pointControle->nom)
                ->line('  Type: ' . $anomalie->type_anomalie)
                ->line('  Description: ' . $anomalie->commentaire);

            if ($anomalie->photo_url) {
                $mail->attach(storage_path('app/public/' . $anomalie->photo_url));
            }
        }

        $mail->action('Voir les détails', route('sie.rondes.show', $this->ronde->id))
            ->action('Exporter les anomalies', route('sie.rondes.export-anomalies', $this->ronde->id));

        return $mail;
    }
}
