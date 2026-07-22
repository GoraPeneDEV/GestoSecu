<?php

namespace App\Models\SAV;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class FicheProgresPieceJointe extends Model
{
    use HasFactory;

    protected $table = 'fiche_progres_pieces_jointes';

    protected $fillable = [
        'fiche_progres_id',
        'filename',
        'chemin_fichier',
        'type',
        'description',
        'uploaded_by'
    ];

    /**
     * Fiche de progrès parente
     */
    public function ficheProgres()
    {
        return $this->belongsTo(FicheProgres::class, 'fiche_progres_id');
    }

    /**
     * Utilisateur ayant uploadé
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * URL de téléchargement
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->chemin_fichier);
    }

    /**
     * Icône selon le type
     */
    public function getIconAttribute()
    {
        $icons = [
            'photo' => 'ti-camera',
            'document' => 'ti-file-text',
            'capture_ecran' => 'ti-device-desktop',
            'autre' => 'ti-paperclip'
        ];
        return $icons[$this->type] ?? 'ti-paperclip';
    }
}
