<?php

namespace App\Http\Controllers\API;

use App\Models\RondeSup;
use App\Models\ScanRondeSup;
use App\Models\PlanningRondeSup;
use App\Models\PointControleSup;
use App\Models\Employe;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RondeSupApiController extends Controller
{
    // =========================================================================
    // PLANNINGS
    // =========================================================================

    /**
     * GET /api/mobile/superviseur/plannings
     * Liste des plannings superviseur disponibles
     */
    public function plannings(Request $request)
    {
        if (!$request->user()->hasAnyPermission(['ronde-superviseur-view', 'ronde-superviseur-create'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $plannings = PlanningRondeSup::with('sites')
            ->withCount('pointsControle')
            ->orderBy('nom')
            ->get()
            ->map(fn($p) => [
                'id'            => $p->id,
                'nom'           => $p->nom,
                'frequence'     => $p->frequence,
                'heure_debut'   => $p->heure_debut?->format('H:i'),
                'duree_estimee' => $p->duree_estimee,
                'points_count'  => $p->points_controle_count,
                'sites'         => $p->sites->map(fn($s) => [
                    'id'  => $s->id,
                    'nom' => $s->nom_site,
                ])->values(),
            ]);

        return response()->json(['success' => true, 'data' => $plannings]);
    }

    /**
     * GET /api/mobile/superviseur/plannings/{id}/points
     * Points de contrôle d'un planning superviseur
     */
    public function planningPoints(Request $request, $id)
    {
        if (!$request->user()->hasAnyPermission(['ronde-superviseur-view', 'ronde-superviseur-create'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $planning = PlanningRondeSup::with([
            'sites',
            'pointsControle' => fn($q) => $q->orderBy('planning_ronde_sup_points.ordre'),
        ])->findOrFail($id);

        return response()->json([
            'success'  => true,
            'planning' => [
                'id'    => $planning->id,
                'nom'   => $planning->nom,
                'sites' => $planning->sites->pluck('nom_site'),
            ],
            'data' => $planning->pointsControle->map(fn($p) => [
                'id'          => $p->id,
                'nom'         => $p->nom,
                'emplacement' => $p->emplacement,
                'qr_code'     => $p->qr_code,
                'ordre'       => $p->pivot->ordre,
            ])->values(),
        ]);
    }

    // =========================================================================
    // RONDES
    // =========================================================================

    /**
     * GET /api/mobile/superviseur/rondes
     * Liste des rondes superviseur
     */
    public function index(Request $request)
    {
        if (!$request->user()->hasAnyPermission(['ronde-superviseur-view', 'ronde-superviseur-create'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $user  = $request->user();
        $query = RondeSup::with(['planningRonde', 'agent'])
            ->withCount([
                'scans',
                'scans as anomalies_count' => fn($q) => $q->where('anomalie', true),
            ]);

        // Un agent (sans droit "ronde-superviseur-view" global) ne voit que ses propres rondes
        if ($user->id_employe && !$user->can('ronde-superviseur-view')) {
            $query->where('agent_id', $user->id_employe);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('date')) {
            $query->whereDate('date_debut', $request->date);
        }

        $rondes = $query->orderBy('date_debut', 'desc')->limit(100)->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'statut'          => $r->statut,
                'date_debut'      => $r->date_debut?->toISOString(),
                'date_fin'        => $r->date_fin?->toISOString(),
                'agent'           => $r->agent
                    ? $r->agent->prenom . ' ' . $r->agent->nom
                    : null,
                'planning'        => $r->planningRonde ? [
                    'id'  => $r->planningRonde->id,
                    'nom' => $r->planningRonde->nom,
                ] : null,
                'scans_count'     => $r->scans_count,
                'anomalies_count' => $r->anomalies_count,
                'points_total'    => $r->planningRonde?->pointsControle()->count() ?? 0,
            ]);

        return response()->json(['success' => true, 'data' => $rondes]);
    }

    /**
     * POST /api/mobile/superviseur/rondes
     * Créer et démarrer une nouvelle ronde superviseur
     */
    public function store(Request $request)
    {
        if (!$request->user()->can('ronde-superviseur-create')) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'planning_ronde_sup_id' => 'required|exists:planning_ronde_sup,id',
            'agent_id'              => 'required|exists:employe,id',
        ]);

        try {
            DB::beginTransaction();

            $ronde = RondeSup::create([
                'planning_ronde_sup_id' => $validated['planning_ronde_sup_id'],
                'agent_id'              => $validated['agent_id'],
                'date_debut'            => now(),
                'statut'                => 'en_cours',
            ]);

            $ronde->load(['planningRonde.pointsControle', 'agent', 'scans']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ronde superviseur démarrée avec succès.',
                'data'    => $this->formatRonde($ronde),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API RondeSup store: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la création de la ronde.'], 500);
        }
    }

    /**
     * GET /api/mobile/superviseur/rondes/{id}
     * Détails complets d'une ronde superviseur
     */
    public function show(Request $request, $id)
    {
        if (!$request->user()->hasAnyPermission(['ronde-superviseur-view', 'ronde-superviseur-create'])) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $ronde = RondeSup::with([
            'planningRonde.pointsControle',
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
     * POST /api/mobile/superviseur/rondes/{id}/verify-qr
     * Vérifier un QR code pour les rondes superviseur
     */
    public function verifyQR(Request $request, $id)
    {
        $request->validate(['qr_code' => 'required|string']);

        $ronde = RondeSup::with('planningRonde.pointsControle')->findOrFail($id);

        if ($ronde->statut === 'terminee') {
            return response()->json(['success' => false, 'message' => 'Cette ronde est déjà terminée.'], 400);
        }

        $point = PointControleSup::where('qr_code', $request->qr_code)->first();

        if (!$point) {
            return response()->json(['success' => false, 'message' => 'QR code non reconnu.'], 404);
        }

        if (!$ronde->planningRonde->pointsControle->contains($point->id)) {
            return response()->json(['success' => false, 'message' => 'Ce point ne fait pas partie de cette ronde.'], 400);
        }

        if ($ronde->scans()->where('point_controle_sup_id', $point->id)->exists()) {
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
     * POST /api/mobile/superviseur/rondes/{id}/scan
     * Enregistrer le scan d'un point de contrôle superviseur
     */
    public function storeScan(Request $request, $id)
    {
        $ronde = RondeSup::with('planningRonde.pointsControle')->findOrFail($id);

        if ($ronde->statut === 'terminee') {
            return response()->json(['success' => false, 'message' => 'Cette ronde est déjà terminée.'], 400);
        }

        $validated = $request->validate([
            'point_controle_sup_id' => 'required|exists:points_controle_sup,id',
            'anomalie'              => 'required|boolean',
            'type_anomalie'         => 'required_if:anomalie,true|nullable|string|max:255',
            'urgence'               => 'nullable|in:faible,moyenne,haute,critique',
            'commentaire'           => 'nullable|string|max:1000',
            'photo'                 => 'nullable|image|max:5120',
            'gps_lat'               => 'nullable|numeric',
            'gps_lng'               => 'nullable|numeric',
        ]);

        if (!$ronde->planningRonde->pointsControle->contains($validated['point_controle_sup_id'])) {
            return response()->json(['success' => false, 'message' => 'Ce point ne fait pas partie de cette ronde.'], 400);
        }

        if ($ronde->scans()->where('point_controle_sup_id', $validated['point_controle_sup_id'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Ce point a déjà été scanné.'], 400);
        }

        $photoPath = null;

        try {
            DB::beginTransaction();

            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('rondes/superviseur/photos', 'public');
            }

            ScanRondeSup::create([
                'ronde_sup_id'          => $ronde->id,
                'point_controle_sup_id' => $validated['point_controle_sup_id'],
                'date_scan'             => now(),
                'anomalie'              => $validated['anomalie'],
                'type_anomalie'         => $validated['anomalie'] ? $validated['type_anomalie'] : null,
                'urgence'               => $validated['urgence'] ?? null,
                'commentaire'           => $validated['commentaire'] ?? null,
                'photo_url'             => $photoPath,
            ]);

            $ronde->refresh();
            $totalPoints   = $ronde->planningRonde->pointsControle->count();
            $scannedPoints = $ronde->scans()->count();
            $isComplete    = $scannedPoints >= $totalPoints;

            if ($isComplete) {
                $ronde->update(['statut' => 'terminee', 'date_fin' => now()]);
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
            Log::error('API RondeSup storeScan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.'], 500);
        }
    }

    // =========================================================================
    // TERMINER
    // =========================================================================

    /**
     * PUT /api/mobile/superviseur/rondes/{id}/terminer
     * Terminer manuellement une ronde superviseur
     */
    public function terminer(Request $request, $id)
    {
        $ronde = RondeSup::findOrFail($id);

        if ($ronde->statut === 'terminee') {
            return response()->json(['success' => false, 'message' => 'Cette ronde est déjà terminée.'], 400);
        }

        try {
            $ronde->update([
                'statut'      => 'terminee',
                'date_fin'    => now(),
                'commentaire' => $request->commentaire ?? null,
            ]);

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
            Log::error('API RondeSup terminer: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la terminaison.'], 500);
        }
    }

    // =========================================================================
    // STATS DASHBOARD
    // =========================================================================

    /**
     * GET /api/mobile/superviseur/stats
     */
    public function stats(Request $request)
    {
        $user  = $request->user();
        $query = RondeSup::query();

        if ($user->id_employe && !$user->can('ronde-superviseur-view')) {
            $query->where('agent_id', $user->id_employe);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'total'       => (clone $query)->count(),
                'en_cours'    => (clone $query)->where('statut', 'en_cours')->count(),
                'terminees'   => (clone $query)->where('statut', 'terminee')->count(),
                'anomalies'   => ScanRondeSup::whereIn('ronde_sup_id', (clone $query)->pluck('id'))
                    ->where('anomalie', true)->count(),
                'aujourd_hui' => (clone $query)->whereDate('date_debut', today())->count(),
            ],
        ]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function formatRonde(RondeSup $ronde): array
    {
        $totalPoints    = $ronde->planningRonde?->pointsControle->count() ?? 0;
        $scansCount     = $ronde->scans->count();
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
                'points' => $ronde->planningRonde->pointsControle->map(function ($p) use ($ronde) {
                    $scan = $ronde->scans->firstWhere('point_controle_sup_id', $p->id);
                    return [
                        'id'          => $p->id,
                        'nom'         => $p->nom,
                        'emplacement' => $p->emplacement,
                        'qr_code'     => $p->qr_code,
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
}
