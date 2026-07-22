<?php

namespace App\Http\Controllers;

use App\Models\SupervisorVisit;
use App\Models\Site;
use App\Models\User;
use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class SupervisionController extends Controller
{
    private const CHECK_FIELDS = [
        'check_agent_presence',
        'check_respect_planning',
        'check_strict_consignes',
        'check_port_vestimentaire',
        'check_proprete',
        'check_talk_box',
        'check_registre_garde',
        'ras',
    ];

    /**
     * Affiche la liste des visites superviseurs.
     */
    public function index()
    {
        $this->authorize('supervision-view');
        $sites = Site::orderBy('nom_site')->get();
        // Filtré par permission plutôt que par nom de rôle : les rôles "Superviseur" en base sont
        // incohérents (Supperviseur, superviseur_sie...) alors que supervision-create est la source
        // de vérité pour savoir qui peut réellement soumettre une visite (web + app mobile dédiée).
        $superviseurs = User::permission('supervision-create')->orderBy('prenom')->get();
        return view('supervision.index', compact('sites', 'superviseurs'));
    }

    /**
     * Agents attendus sur un site (planning actif du jour) — alimente le formulaire de création/édition.
     */
    public function getSiteAgents(Site $site)
    {
        $this->authorize('supervision-create');

        $agents = Planning::where('site_id', $site->id)
            ->whereNull('date_fin')
            ->with('employe')
            ->get()
            ->filter(fn($p) => $p->employe)
            ->map(fn($p) => [
                'id' => $p->employe->id,
                'nom_complet' => $p->employe->prenom . ' ' . $p->employe->nom,
            ])
            ->values();

        return response()->json(['agents' => $agents]);
    }

    /**
     * Enregistre une visite saisie manuellement depuis le back-office.
     */
    public function store(Request $request)
    {
        $this->authorize('supervision-create');

        $validated = $this->validateVisit($request);

        $photoPath = $request->hasFile('photo') ? $request->file('photo')->store('supervisor_visits', 'public') : null;
        $videoPath = $request->hasFile('video') ? $request->file('video')->store('supervisor_visits/videos', 'public') : null;

        $visit = SupervisorVisit::create($validated + [
            'scan_mode' => 'manual',
            'photo_path' => $photoPath,
            'video_path' => $videoPath,
        ]);

        $visit->notifyIfAlert();

        return response()->json(['success' => true, 'message' => 'Visite enregistrée avec succès.']);
    }

    /**
     * Retourne les données d'une visite pour préremplir le formulaire d'édition.
     */
    public function edit(SupervisorVisit $visite)
    {
        $this->authorize('supervision-create');

        return response()->json($visite->load('site', 'supervisor'));
    }

    /**
     * Met à jour une visite existante.
     */
    public function update(Request $request, SupervisorVisit $visite)
    {
        $this->authorize('supervision-create');

        $validated = $this->validateVisit($request);
        $wasAlert = in_array($visite->status, SupervisorVisit::ALERT_STATUSES, true);

        if ($request->hasFile('photo')) {
            if ($visite->photo_path) {
                Storage::disk('public')->delete($visite->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('supervisor_visits', 'public');
        }

        if ($request->hasFile('video')) {
            if ($visite->video_path) {
                Storage::disk('public')->delete($visite->video_path);
            }
            $validated['video_path'] = $request->file('video')->store('supervisor_visits/videos', 'public');
        }

        $visite->update($validated);

        // On ne renotifie que sur une transition vers un statut d'alerte, pour éviter le spam sur les corrections mineures.
        $isAlert = in_array($visite->status, SupervisorVisit::ALERT_STATUSES, true);
        if ($isAlert && !$wasAlert) {
            $visite->notifyIfAlert();
        }

        return response()->json(['success' => true, 'message' => 'Visite mise à jour avec succès.']);
    }

    /**
     * Supprime une visite (et ses fichiers média associés).
     */
    public function destroy(SupervisorVisit $visite)
    {
        $this->authorize('supervision-create');

        if ($visite->photo_path) {
            Storage::disk('public')->delete($visite->photo_path);
        }
        if ($visite->video_path) {
            Storage::disk('public')->delete($visite->video_path);
        }

        $visite->delete();

        return response()->json(['success' => true, 'message' => 'Visite supprimée avec succès.']);
    }

    /**
     * Règles de validation communes à store() et update().
     */
    private function validateVisit(Request $request): array
    {
        $rules = [
            'site_id' => 'required|exists:sites,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string',
            'expected_agents_count' => 'required|integer|min:0',
            'actual_agents_count' => 'required|integer|min:0',
            'missing_agents' => 'nullable|array',
            'missing_agents_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
            'video' => 'nullable|file|mimes:mp4,mov,avi,webm|max:20480',
        ];

        foreach (self::CHECK_FIELDS as $field) {
            $rules[$field] = 'required|boolean';
        }

        $validated = $request->validate($rules);

        // photo/video sont gérés séparément (upload), pas des colonnes à assigner telles quelles
        unset($validated['photo'], $validated['video']);

        return $validated;
    }

    /**
     * Récupère les données pour DataTables.
     */
    public function getVisits(Request $request)
    {
        $this->authorize('supervision-view');
        
        $visits = SupervisorVisit::with(['site', 'supervisor']);

        if ($request->has('site_id') && $request->site_id != '') {
            $visits->where('site_id', $request->site_id);
        }

        if ($request->has('supervisor_id') && $request->supervisor_id != '') {
            $visits->where('user_id', $request->supervisor_id);
        }
        
        if ($request->has('status') && $request->status != '') {
            $visits->where('status', $request->status);
        }

        return DataTables::of($visits)
            ->addColumn('site_nom', function ($visit) {
                return $visit->site ? $visit->site->nom_site : 'N/A';
            })
            ->addColumn('superviseur', function ($visit) {
                return $visit->supervisor ? $visit->supervisor->prenom . ' ' . $visit->supervisor->nom : 'N/A';
            })
            ->editColumn('created_at', function ($visit) {
                return $visit->created_at ? $visit->created_at->format('d/m/Y H:i') : '-';
            })
            ->editColumn('status', function ($visit) {
                $badges = [
                    'RAS' => 'bg-label-success',
                    'Alerte' => 'bg-label-danger',
                    'Incident' => 'bg-label-warning',
                    'Normal' => 'bg-label-primary'
                ];
                $badge = $badges[$visit->status] ?? 'bg-label-secondary';
                return '<span class="badge ' . $badge . '">' . $visit->status . '</span>';
            })
            ->editColumn('scan_mode', function ($visit) {
                $badges = [
                    'qr' => 'bg-label-info',
                    'nfc' => 'bg-label-primary',
                    'manual' => 'bg-label-secondary',
                ];
                $badge = $badges[$visit->scan_mode] ?? 'bg-label-secondary';
                return '<span class="badge ' . $badge . '"><i class="ti ti-scan me-1"></i> ' . strtoupper($visit->scan_mode) . '</span>';
            })
            ->addColumn('missing_agents_names', function ($visit) {
                if ($visit->missing_agents && is_array($visit->missing_agents)) {
                    $employees = \App\Models\Employe::whereIn('id', $visit->missing_agents)->get();
                    return $employees->map(fn($e) => $e->prenom . ' ' . $e->nom)->implode(', ');
                }
                return '-';
            })
            ->addColumn('video', function ($visit) {
                 if ($visit->video_path) {
                     $url = asset('storage/' . $visit->video_path);
                     return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-icon btn-label-warning"><i class="ti ti-video"></i></a>';
                 }
                 return '<span class="text-muted"><i class="ti ti-video-off"></i></span>';
            })
            ->addColumn('photo', function ($visit) {
                 if ($visit->photo_path) {
                     $url = asset('storage/' . $visit->photo_path);
                     return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-icon btn-label-info"><i class="ti ti-camera"></i></a>';
                 }
                 return '<span class="text-muted"><i class="ti ti-camera-off"></i></span>';
            })
            ->addColumn('actions', function ($visit) {
                 return '<div class="d-inline-flex gap-1">
                        <button class="btn btn-sm btn-icon btn-label-primary btn-view-details"
                            data-visit="' . htmlspecialchars(json_encode($visit)) . '"
                            data-missing="' . htmlspecialchars(json_encode($visit->missing_agents && is_array($visit->missing_agents) ? \App\Models\Employe::whereIn('id', $visit->missing_agents)->get()->map(fn($e) => ['id' => $e->id, 'nom_complet' => $e->prenom . ' ' . $e->nom]) : [])) . '"
                            data-bs-toggle="tooltip" title="Voir les détails">
                            <i class="ti ti-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-label-warning btn-edit-visit" data-id="' . $visit->id . '"
                            data-bs-toggle="tooltip" title="Modifier">
                            <i class="ti ti-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-label-danger btn-delete-visit" data-id="' . $visit->id . '"
                            data-bs-toggle="tooltip" title="Supprimer">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['status', 'scan_mode', 'photo', 'video', 'actions'])
            ->make(true);
    }
}
