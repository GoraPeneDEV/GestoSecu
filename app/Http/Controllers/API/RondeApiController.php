<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Ronde;
use App\Models\RondeScan;
use App\Models\PlanningRonde;
use App\Models\PointControle;
use App\Models\Employe;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RondeAnomaliesNotification;
use App\Models\RondeGpsTrack;

class RondeApiController extends Controller
{
    // =========================================================================
    // PLANNINGS
    // =========================================================================

    /**
     * GET /api/mobile/rondes/plannings
     * Liste des plannings disponibles pour démarrer une ronde
     */
    public function plannings(Request $request)
    {
        if (!$request->user()->hasAnyPermission(['ronde-view', 'ronde-create'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        // Déterminer le site actif de l'agent via son planning RH actif
        $user = $request->user();
        $siteId = null;

        if ($user->id_employe) {
            $planningRh = \App\Models\Planning::where('employe_id', $user->id_employe)
                ->whereNull('date_fin')
                ->latest('date_debut')
                ->value('site_id');
            $siteId = $planningRh;
        }

        if (!$siteId) {
            return response()->json([
                'success' => true,
                'data'    => [],
                'message' => 'Aucun site actif trouvé pour votre compte. Contactez votre responsable.',
            ]);
        }

        $query = PlanningRonde::with('site')
            ->withCount('pointsControle')
            ->where('site_id', $siteId)
            ->orderBy('nom');

        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        $plannings = $query->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'nom'            => $p->nom,
                'frequence'      => $p->frequence,
                'heure_debut'    => $p->heure_debut?->format('H:i'),
                'duree_estimee'  => $p->duree_estimee,
                'points_count'   => $p->points_controle_count,
                'site'           => $p->site ? [
                    'id'  => $p->site->id,
                    'nom' => $p->site->nom_site,
                ] : null,
            ]);

        return response()->json(['success' => true, 'data' => $plannings]);
    }

    /**
     * GET /api/mobile/rondes/plannings/{id}/points
     * Points de contrôle d'un planning avec leur QR code
     */
    public function planningPoints(Request $request, $id)
    {
        if (!$request->user()->hasAnyPermission(['ronde-view', 'ronde-create'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $planning = PlanningRonde::with([
            'site',
            'pointsControle' => fn($q) => $q->orderBy('planning_ronde_points.ordre'),
        ])->findOrFail($id);

        return response()->json([
            'success'  => true,
            'planning' => [
                'id'   => $planning->id,
                'nom'  => $planning->nom,
                'site' => $planning->site?->nom_site,
            ],
            'data' => $planning->pointsControle->map(fn($p) => [
                'id'          => $p->id,
                'nom'         => $p->nom,
                'emplacement' => $p->emplacement,
                'qr_code'     => $p->qr_code,
                'nfc_tag'     => $p->nfc_tag,
                'ordre'       => $p->pivot->ordre,
            ])->values(),
        ]);
    }

    // =========================================================================
    // RONDES
    // =========================================================================

    /**
     * GET /api/mobile/rondes
     * Liste des rondes (filtrées selon le rôle de l'utilisateur)
     */
    public function index(Request $request)
    {
        if (!$request->user()->hasAnyPermission(['ronde-view', 'ronde-create'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $user  = $request->user();
        $query = Ronde::with(['planningRonde.site', 'planningRonde.pointsControle', 'agent', 'scans'])
            ->orderBy('date_debut', 'desc');

        // Un agent (sans droit "ronde-view" global) ne voit que ses propres rondes
        if ($user->id_employe && !$user->can('ronde-view')) {
            $query->where('agent_id', $user->id_employe);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date')) {
            $query->whereDate('date_debut', $request->date);
        }

        $rondes = $query->limit(100)->get()
            ->map(fn($r) => $this->formatRonde($r));

        return response()->json(['success' => true, 'data' => $rondes]);
    }

    /**
     * POST /api/mobile/rondes
     * Créer et démarrer une nouvelle ronde
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('ronde-create')) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'planning_ronde_id' => 'required|exists:plannings_ronde,id',
            'agent_id'          => 'required|exists:employe,id',
        ]);

        try {
            DB::beginTransaction();

            $ronde = Ronde::create([
                'planning_ronde_id' => $validated['planning_ronde_id'],
                'agent_id'          => $validated['agent_id'],
                'date_debut'        => now(),
                'statut'            => 'en_cours',
            ]);

            $ronde->load(['planningRonde.pointsControle', 'planningRonde.site', 'agent', 'scans']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ronde démarrée avec succès.',
                'data'    => $this->formatRonde($ronde),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Ronde store: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la création de la ronde.'], 500);
        }
    }

    /**
     * GET /api/mobile/rondes/{id}
     * Détails complets d'une ronde (points + scans déjà effectués)
     */
    public function show(Request $request, $id)
    {
        if (!$request->user()->hasAnyPermission(['ronde-view', 'ronde-create'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $ronde = Ronde::with([
            'planningRonde.pointsControle',
            'planningRonde.site',
            'agent',
            'scans.pointControle',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatRonde($ronde),
        ]);
    }

    // =========================================================================
    // SCANS
    // =========================================================================

    /**
     * POST /api/mobile/rondes/{id}/verify-qr
     * Vérifier qu'un QR code appartient à la ronde et n'a pas encore été scanné
     */
    public function verifyQR(Request $request, $id)
    {
        $request->validate([
            'qr_code' => 'nullable|string',
            'nfc_tag' => 'nullable|string',
            'code'    => 'nullable|string',
        ]);

        $code = $request->code ?? $request->qr_code ?? $request->nfc_tag;

        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Code (QR ou NFC) requis.'], 422);
        }

        $ronde = Ronde::with('planningRonde.pointsControle')->findOrFail($id);

        if ($ronde->statut === 'terminee') {
            return response()->json(['success' => false, 'message' => 'Cette ronde est déjà terminée.'], 400);
        }

        $point = PointControle::where('qr_code', $code)
            ->orWhere('nfc_tag', $code)
            ->first();

        if (!$point) {
            return response()->json(['success' => false, 'message' => 'Point de contrôle non reconnu (QR/NFC).'], 404);
        }

        if (!$ronde->planningRonde->pointsControle->contains($point->id)) {
            return response()->json(['success' => false, 'message' => 'Ce point ne fait pas partie de cette ronde.'], 400);
        }

        if ($ronde->scans()->where('point_controle_id', $point->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Ce point a déjà été scanné.'], 400);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'point_controle_id' => $point->id,
                'nom'               => $point->nom,
                'emplacement'       => $point->emplacement,
            ],
        ]);
    }

    /**
     * POST /api/mobile/rondes/{id}/scan
     * Enregistrer le scan d'un point de contrôle (normal ou anomalie)
     */
    public function storeScan(Request $request, $id)
    {
        $ronde = Ronde::with('planningRonde.pointsControle')->findOrFail($id);

        if ($ronde->statut === 'terminee') {
            return response()->json(['success' => false, 'message' => 'Cette ronde est déjà terminée.'], 400);
        }

        $validated = $request->validate([
            'point_controle_id' => 'required|exists:point_controles,id',
            'anomalie'          => 'required|boolean',
            'type_anomalie'     => 'nullable|string|max:255',
            'urgence'           => 'nullable|in:faible,moyen,eleve',
            'commentaire'       => 'nullable|string|max:1000',
            'photo'             => 'nullable|image|max:5120',
            'photos.*'          => 'nullable|file|max:204800',
            'gps_lat'           => 'nullable|numeric|between:-90,90',
            'gps_lng'           => 'nullable|numeric|between:-180,180',
            'steps'             => 'nullable|integer|min:0',
        ]);

        if (!$ronde->planningRonde->pointsControle->contains($validated['point_controle_id'])) {
            return response()->json(['success' => false, 'message' => 'Ce point ne fait pas partie de cette ronde.'], 400);
        }

        if ($ronde->scans()->where('point_controle_id', $validated['point_controle_id'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Ce point a déjà été scanné.'], 400);
        }

        $photoPath = null;

        try {
            DB::beginTransaction();

            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('rondes/photos', 'public');
            }

            $mediaPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $file) {
                    $mediaPaths[] = $file->store('rondes/medias', 'public');
                }
            }

            RondeScan::create([
                'ronde_id'          => $ronde->id,
                'point_controle_id' => $validated['point_controle_id'],
                'date_scan'         => now(),
                'anomalie'          => $validated['anomalie'],
                'type_anomalie'     => $validated['anomalie'] ? $validated['type_anomalie'] : null,
                'urgence'           => $validated['urgence'] ?? null,
                'commentaire'       => $validated['commentaire'] ?? null,
                'photo_url'         => $photoPath,
                'photos'            => !empty($mediaPaths) ? $mediaPaths : null,
                'gps_lat'           => $validated['gps_lat'] ?? null,
                'gps_lng'           => $validated['gps_lng'] ?? null,
            ]);

            if (!empty($validated['steps'])) {
                $ronde->update(['steps' => $validated['steps']]);
            }

            $ronde->refresh();
            $totalPoints   = $ronde->planningRonde->pointsControle->count();
            $scannedPoints = $ronde->scans()->count();
            $isComplete    = $scannedPoints >= $totalPoints;

            if ($isComplete) {
                $ronde->update(['statut' => 'terminee', 'date_fin' => now()]);
                $this->notifierAnomaliesSiPresentes($ronde);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Point enregistré avec succès.',
                'data'    => [
                    'scans_count'    => $scannedPoints,
                    'points_total'   => $totalPoints,
                    'progression'    => $totalPoints > 0 ? round(($scannedPoints / $totalPoints) * 100) : 0,
                    'ronde_terminee' => $isComplete,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            Log::error('API Ronde storeScan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.'], 500);
        }
    }

    /**
     * POST /api/mobile/rondes/{id}/gps-tracks
     * Enregistrer des traces GPS en lot
     */
    public function storeGpsTracks(Request $request, $id)
    {
        $ronde = Ronde::findOrFail($id);

        $validated = $request->validate([
            'tracks'              => 'required|array',
            'tracks.*.latitude'   => 'required|numeric',
            'tracks.*.longitude'  => 'required|numeric',
            'tracks.*.timestamp'  => 'required|string',
        ]);

        try {
            foreach ($validated['tracks'] as $track) {
                RondeGpsTrack::create([
                    'ronde_id'    => $ronde->id,
                    'latitude'    => $track['latitude'],
                    'longitude'   => $track['longitude'],
                    'recorded_at' => Carbon::parse($track['timestamp']),
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Traces GPS enregistrées.']);
        } catch (\Exception $e) {
            Log::error('API Ronde storeGpsTracks: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'enregistrement GPS.'], 500);
        }
    }

    // =========================================================================
    // TERMINER
    // =========================================================================

    /**
     * PATCH /api/mobile/rondes/{id}/steps
     * Mettre à jour le compteur de pas en cours de ronde (envoi périodique depuis le podomètre)
     */
    public function updateSteps(Request $request, $id)
    {
        $ronde = Ronde::findOrFail($id);

        $validated = $request->validate([
            'steps' => 'required|integer|min:0',
        ]);

        $ronde->update(['steps' => $validated['steps']]);

        return response()->json([
            'success' => true,
            'steps'   => $ronde->steps,
        ]);
    }

    /**
     * PUT /api/mobile/rondes/{id}/terminer
     * Terminer manuellement une ronde (même incomplète)
     */
    public function terminer(Request $request, $id)
    {
        $ronde = Ronde::findOrFail($id);

        if ($ronde->statut === 'terminee') {
            return response()->json([
                'success' => true,
                'message' => 'Ronde déjà terminée.',
                'data'    => [
                    'id'       => $ronde->id,
                    'statut'   => $ronde->statut,
                    'date_fin' => $ronde->date_fin?->toISOString(),
                ],
            ]);
        }

        try {
            $ronde->update([
                'statut'      => 'terminee',
                'date_fin'    => now(),
                'steps'       => $request->steps ?? $ronde->steps,
                'commentaire' => $request->commentaire ?? null,
            ]);

            $this->notifierAnomaliesSiPresentes($ronde);

            return response()->json([
                'success' => true,
                'message' => 'Ronde terminée avec succès.',
                'data'    => [
                    'id'       => $ronde->id,
                    'statut'   => $ronde->statut,
                    'date_fin' => $ronde->fresh()->date_fin?->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('API Ronde terminer: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la terminaison.'], 500);
        }
    }

    // =========================================================================
    // STATS DASHBOARD
    // =========================================================================

    /**
     * GET /api/mobile/rondes/stats
     * Statistiques pour le dashboard de l'agent
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $scopeToOwnRondes = $user->id_employe && !$user->can('ronde-view');

        $query = Ronde::query();

        if ($scopeToOwnRondes) {
            $query->where('agent_id', $user->id_employe);
        }

        $rondesToday = (clone $query)->whereDate('date_debut', today());

        return response()->json([
            'success' => true,
            'data'    => [
                'total'      => (clone $query)->count(),
                'en_cours'   => (clone $query)->where('statut', 'en_cours')->count(),
                'terminees'  => (clone $query)->where('statut', 'terminee')->count(),
                'anomalies'  => RondeScan::whereHas('ronde', function ($q) use ($scopeToOwnRondes, $user) {
                    if ($scopeToOwnRondes) {
                        $q->where('agent_id', $user->id_employe);
                    }
                    $q->whereDate('date_debut', today());
                })->where('anomalie', true)->count(),
                'aujourd_hui' => $rondesToday->count(),
                'total_anomalies' => RondeScan::whereHas('ronde', function ($q) use ($scopeToOwnRondes, $user) {
                    if ($scopeToOwnRondes) {
                        $q->where('agent_id', $user->id_employe);
                    }
                })->where('anomalie', true)->count(),
            ],
        ]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function formatRonde(Ronde $ronde): array
    {
        $totalPoints   = $ronde->planningRonde?->pointsControle->count() ?? 0;
        $scansCount    = $ronde->scans->count();
        $anomaliesCount = $ronde->scans->where('anomalie', true)->count();

        return [
            'id'          => $ronde->id,
            'statut'      => $ronde->statut,
            'date_debut'  => $ronde->date_debut?->toISOString(),
            'date_fin'    => $ronde->date_fin?->toISOString(),
            'commentaire' => $ronde->commentaire,
            'agent'       => $ronde->agent ? [
                'id'          => $ronde->agent->id,
                'nom_complet' => $ronde->agent->prenom . ' ' . $ronde->agent->nom,
            ] : null,
            'planning'    => $ronde->planningRonde ? [
                'id'     => $ronde->planningRonde->id,
                'nom'    => $ronde->planningRonde->nom,
                'site'   => $ronde->planningRonde->site?->nom_site,
                'points' => $ronde->planningRonde->pointsControle->map(function ($p) use ($ronde) {
                    $scan = $ronde->scans->firstWhere('point_controle_id', $p->id);
                    return [
                        'id'          => $p->id,
                        'nom'         => $p->nom,
                        'emplacement' => $p->emplacement,
                        'qr_code'     => $p->qr_code,
                        'nfc_tag'     => $p->nfc_tag,
                        'ordre'       => $p->pivot->ordre,
                        'scan'        => $scan ? [
                            'date'          => $scan->date_scan?->toISOString(),
                            'anomalie'      => $scan->anomalie,
                            'type_anomalie' => $scan->type_anomalie,
                            'urgence'       => $scan->urgence,
                            'photo_url'     => $scan->photo_url
                                ? asset('storage/' . $scan->photo_url)
                                : null,
                        ] : null,
                    ];
                })->values()->toArray(),
            ] : null,
            'progression' => [
                'scans'       => $scansCount,
                'total'       => $totalPoints,
                'pourcentage' => $totalPoints > 0 ? round(($scansCount / $totalPoints) * 100) : 0,
                'anomalies'   => $anomaliesCount,
            ],
        ];
    }

    /**
     * Notifie les super administrateurs si la ronde comporte au moins une anomalie.
     * N'échoue jamais l'appelant : les erreurs d'envoi sont journalisées, pas propagées.
     */
    private function notifierAnomaliesSiPresentes(Ronde $ronde): void
    {
        if (!$ronde->scans()->where('anomalie', true)->exists()) {
            return;
        }

        $destinataires = User::role('super_admin')->pluck('email')->filter()->all();

        if (empty($destinataires)) {
            return;
        }

        try {
            Notification::route('mail', $destinataires)->notify(new RondeAnomaliesNotification($ronde));
        } catch (\Exception $e) {
            Log::error('Notification ronde anomalie: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // AGENTS
    // =========================================================================

    /**
     * GET /api/mobile/agents
     * Liste des agents habilités à effectuer des rondes (permission ronde-create), pour la
     * sélection au démarrage de ronde.
     */
    public function agents(Request $request)
    {
        if (!$request->user()->can('ronde-create')) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $query = Employe::whereHas('user', function ($q) {
            $q->permission('ronde-create');
        })->where('etat', 1)->orderBy('nom');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', $search)
                    ->orWhere('prenom', 'like', $search);
            });
        }

        $agents = $query->with([
            'plannings' => fn($q) => $q->whereNull('date_fin')->with('site')->latest('date_debut')->limit(1),
            'rondes' => function ($q) {
                $q->where('statut', 'en_cours')->with('planningRonde');
            },
        ])->get()->map(function ($a) {
            $rondeActive = $a->rondes->first();
            return [
                'id'           => $a->id,
                'nom'          => $a->nom,
                'prenom'       => $a->prenom,
                'matricule'    => $a->matricule,
                'fonction'     => $a->fonction,
                'site_nom'     => $a->plannings->first()?->site?->nom_site,
                'ronde_active' => $rondeActive ? [
                    'id'           => $rondeActive->id,
                    'planning_nom' => $rondeActive->planningRonde?->nom,
                    'date_debut'   => $rondeActive->date_debut?->toISOString(),
                ] : null,
            ];
        });

        return response()->json(['success' => true, 'data' => $agents]);
    }

    /**
     * GET /api/mobile/agents/{id}/ronde-active
     * Vérifie si un agent a une ronde en_cours
     */
    public function agentRondeActive(Request $request, $id)
    {
        if (!$request->user()->can('ronde-create')) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $ronde = Ronde::where('agent_id', $id)
            ->where('statut', 'en_cours')
            ->with('planningRonde')
            ->first();

        return response()->json([
            'success' => true,
            'data'    => $ronde ? [
                'id'           => $ronde->id,
                'planning_nom' => $ronde->planningRonde?->nom,
                'date_debut'   => $ronde->date_debut?->toISOString(),
            ] : null,
        ]);
    }
}
