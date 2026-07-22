<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\SupervisorVisit;
use Illuminate\Support\Facades\Storage;

class SupervisionApiController extends Controller
{
    /**
     * Liste l'historique des visites du superviseur connecté.
     */
    public function index(Request $request)
    {
        if (!$request->user()->can('supervision-view')) {
            return response()->json(['success' => false, 'message' => 'Accès interdit.'], 403);
        }

        $query = SupervisorVisit::with('site')
            ->where('user_id', $request->user()->id);

        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $visits = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $visits->items(),
            'meta' => [
                'current_page' => $visits->currentPage(),
                'last_page' => $visits->lastPage(),
                'total' => $visits->total(),
            ]
        ]);
    }

    /**
     * Recherche le site via son QR Code ou Tag NFC scanné.
     */
    public function scan(Request $request)
    {
        if (!$request->user()->can('supervision-create')) {
            return response()->json(['success' => false, 'message' => 'Accès interdit.'], 403);
        }

        $request->validate([
            'qr_code'  => 'nullable|string',
            'nfc_tag'  => 'nullable|string',
            'code'     => 'nullable|string',
            'mode'     => 'nullable|string|in:qr,nfc',
        ]);

        $code = $request->code ?: ($request->qr_code ?: $request->nfc_tag);
        $mode = $request->mode ?: ($request->nfc_tag ? 'nfc' : 'qr');

        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Aucun code fourni.'], 400);
        }

        $query = Site::query();

        if ($mode === 'nfc') {
            $query->whereRaw('TRIM(nfc_tag) = ?', [trim($code)]);
        } else {
            $query->whereRaw('TRIM(qr_code) = ?', [trim($code)]);
        }

        $site = $query->first();

        if (!$site) {
            return response()->json(['success' => false, 'message' => 'Aucun site trouvé pour ce code.'], 404);
        }

        // Récupérer le planning actif de la journée pour ce site
        $plannings = \App\Models\Planning::where('site_id', $site->id)
            ->whereNull('date_fin')
            ->with('employe')
            ->get();

        $agents = $plannings->map(function ($p) {
            return [
                'id' => $p->employe->id,
                'nom_complet' => $p->employe->prenom . ' ' . $p->employe->nom,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Site identifié avec succès.',
            'site' => [
                'id' => $site->id,
                'nom' => $site->nom_site,
                'client' => $site->client ? $site->client->nomClient : 'Inconnu',
                'scan_mode' => $mode,
                'expected_agents_count' => $agents->count(),
                'agents' => $agents
            ]
        ]);
    }

    /**
     * Enregistre le rapport de visite du superviseur.
     */
    public function submitReport(Request $request)
    {
        if (!$request->user()->can('supervision-create')) {
            return response()->json(['success' => false, 'message' => 'Accès interdit.'], 403);
        }

        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'scan_mode' => 'required|in:qr,nfc',
            'status' => 'required|string',
            'gps_lat' => 'nullable|numeric',
            'gps_lng' => 'nullable|numeric',
            'expected_agents_count' => 'required|integer',
            'actual_agents_count' => 'required|integer',
            'missing_agents' => 'nullable|array',
            'missing_agents_details' => 'nullable|string',
            'check_agent_presence' => 'required|boolean',
            'check_respect_planning' => 'required|boolean',
            'check_strict_consignes' => 'required|boolean',
            'check_port_vestimentaire' => 'required|boolean',
            'check_proprete' => 'required|boolean',
            'check_talk_box' => 'required|boolean',
            'check_registre_garde' => 'required|boolean',
            'ras' => 'required|boolean',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
            'video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:20480',
        ]);

        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('supervisor_visits', 'public');
            }

            $videoPath = null;
            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('supervisor_visits/videos', 'public');
            }

            $visit = SupervisorVisit::create([
                'site_id' => $request->site_id,
                'user_id' => $request->user()->id,
                'scan_mode' => $request->scan_mode,
                'gps_lat' => $request->gps_lat,
                'gps_lng' => $request->gps_lng,
                'status' => $request->status,
                'expected_agents_count' => $request->expected_agents_count,
                'actual_agents_count' => $request->actual_agents_count,
                'missing_agents' => $request->missing_agents,
                'missing_agents_details' => $request->missing_agents_details,
                'check_agent_presence' => $request->check_agent_presence,
                'check_respect_planning' => $request->check_respect_planning,
                'check_strict_consignes' => $request->check_strict_consignes,
                'check_port_vestimentaire' => $request->check_port_vestimentaire,
                'check_proprete' => $request->check_proprete,
                'check_talk_box' => $request->check_talk_box,
                'check_registre_garde' => $request->check_registre_garde,
                'ras' => $request->ras,
                'notes' => $request->notes,
                'photo_path' => $photoPath,
                'video_path' => $videoPath,
            ]);

            $visit->notifyIfAlert();

            return response()->json([
                'success' => true,
                'message' => 'Rapport de visite enregistré avec succès.',
                'visit_id' => $visit->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()
            ], 500);
        }
    }
}
