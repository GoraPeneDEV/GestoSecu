<?php

namespace App\Models;

use App\Notifications\SupervisionAlertNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SupervisorVisit extends Model
{
    use HasFactory;

    // Statuts déclenchant une alerte automatique aux responsables (voir notifyIfAlert())
    public const ALERT_STATUSES = ['Alerte', 'Incident'];

    protected $fillable = [
        'site_id',
        'user_id',
        'scan_mode',
        'gps_lat',
        'gps_lng',
        'status',
        'expected_agents_count',
        'actual_agents_count',
        'missing_agents',
        'missing_agents_details',
        'check_agent_presence',
        'check_respect_planning',
        'check_strict_consignes',
        'check_port_vestimentaire',
        'check_proprete',
        'check_talk_box',
        'check_registre_garde',
        'ras',
        'notes',
        'photo_path',
        'video_path',
    ];

    protected $casts = [
        'missing_agents' => 'array',
        'check_agent_presence' => 'boolean',
        'check_respect_planning' => 'boolean',
        'check_strict_consignes' => 'boolean',
        'check_port_vestimentaire' => 'boolean',
        'check_proprete' => 'boolean',
        'check_talk_box' => 'boolean',
        'check_registre_garde' => 'boolean',
        'ras' => 'boolean',
        'gps_lat' => 'double',
        'gps_lng' => 'double',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Envoie l'alerte email aux responsables si le statut appartient à ALERT_STATUSES.
     * N'échoue jamais l'appelant : les erreurs d'envoi sont journalisées, pas propagées.
     */
    public function notifyIfAlert(): void
    {
        if (!in_array($this->status, self::ALERT_STATUSES, true)) {
            return;
        }

        $destinataires = User::role('super_admin')->pluck('email')->filter()->all();

        if (empty($destinataires)) {
            return;
        }

        try {
            Notification::route('mail', $destinataires)->notify(new SupervisionAlertNotification($this));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'alerte de supervision: ' . $e->getMessage());
        }
    }
}
