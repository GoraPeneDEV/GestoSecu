<?php

namespace App\Http\Controllers\sie;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\Ronde;
use App\Models\User;
use App\Models\Employe;
use App\Models\RondeScan;
use Illuminate\Http\Request;
use App\Models\PlanningRonde;
use App\Models\PointControle;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Notification;
use App\Notifications\RondeAnomaliesNotification;
use Barryvdh\DomPDF\Facade\Pdf;

class RondeController extends Controller
{
    public function index()
    {
        $this->authorize('ronde-view');
        $stats = [
            'totalRondes' => Ronde::count(),
            'enCoursCount' => Ronde::where('statut', 'en_cours')->count(),
            'termineesCount' => Ronde::where('statut', 'terminee')->count(),
            'anomaliesCount' => RondeScan::where('anomalie', true)->count()
        ];

        $sites = Site::orderBy('nom_site')->get();

        return view('sie.rondes.index', compact('stats', 'sites'));
    }

    public function getRondes(Request $request)
    {
        $query = Ronde::with(['agent', 'planningRonde.site', 'scans'])->select('rondes.*');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('site_id')) {
            $query->whereHas('planningRonde', function ($q) use ($request) {
                $q->where('site_id', $request->site_id);
            });
        }

        if ($request->filled('date')) {
            $date = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
            $query->whereDate('date_debut', $date);
        }

        return DataTables::of($query)
            ->addColumn('agent', function ($ronde) {
                return $ronde->agent ? $ronde->agent->prenom . ' ' . $ronde->agent->nom : '-';
            })
            ->addColumn('site', function ($ronde) {
                return $ronde->planningRonde && $ronde->planningRonde->site
                    ? $ronde->planningRonde->site->nom_site
                    : '-';
            })
            ->editColumn('date_debut', function ($ronde) {
                return $ronde->date_debut ? Carbon::parse($ronde->date_debut)->format('d/m/Y H:i') : '-';
            })
            ->editColumn('statut', function ($ronde) {
                $badges = [
                    'en_cours' => 'bg-warning',
                    'terminee' => 'bg-success'
                ];
                $labels = [
                    'en_cours' => 'En cours',
                    'terminee' => 'Terminée'
                ];
                return '<span class="badge ' . ($badges[$ronde->statut] ?? 'bg-secondary') . '">' .
                    ($labels[$ronde->statut] ?? $ronde->statut) . '</span>';
            })
            ->addColumn('progression', function ($ronde) {
                if (!$ronde->planningRonde) {
                    return '<small class="text-muted">Planning non disponible</small>';
                }

                $total = $ronde->planningRonde->pointsControle->count();
                $scanned = $ronde->scans->count();
                $percentage = $total > 0 ? round(($scanned / $total) * 100) : 0;
                $hasAnomalies = $ronde->scans->where('anomalie', true)->count() > 0;

                $html = '<div class="progress" style="height: 6px;">
                                    <div class="progress-bar ' . ($hasAnomalies ? 'bg-danger' : 'bg-primary') . '"
                                         role="progressbar"
                                         style="width: ' . $percentage . '%"
                                         aria-valuenow="' . $percentage . '"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="' . ($hasAnomalies ? 'text-danger' : 'text-muted') . ' mt-1">
                                    ' . $scanned . '/' . $total . ' points (' . $percentage . '%)';

                if ($hasAnomalies) {
                    $html .= ' <span class="badge bg-danger">Anomalie(s) détectée(s)</span>';
                }

                $html .= '</small>';
                return $html;
            })
            ->addColumn('actions', function ($ronde) {
                $buttons = '<div class="d-inline-block text-nowrap">';

                $buttons .= '<a href="' . route('sie.rondes.show', $ronde->id) . '"
                                       class="btn btn-sm btn-icon btn-primary me-1"
                                       data-bs-toggle="tooltip"
                                       title="Voir les détails">
                                       <i class="ti ti-eye"></i>
                                    </a>';

                if ($ronde->statut === 'en_cours') {
                    $buttons .= '<button type="button"
                                            class="btn btn-sm btn-icon btn-warning me-1 btn-terminer-ronde"
                                            data-id="' . $ronde->id . '"
                                            data-bs-toggle="tooltip"
                                            title="Terminer la ronde">
                                            <i class="ti ti-square-check"></i>
                                        </button>';
                }

                if ($ronde->scans->where('anomalie', true)->count() > 0) {
                    $buttons .= '<a href="' . route('sie.rondes.export-anomalies', $ronde->id) . '"
                                          class="btn btn-sm btn-icon btn-danger"
                                          data-bs-toggle="tooltip"
                                          title="Exporter les anomalies (PDF)">
                                          <i class="ti ti-file-export"></i>
                                       </a>';
                }

                $buttons .= '</div>';
                return $buttons;
            })
            ->setRowClass(function ($ronde) {
                return $ronde->scans->where('anomalie', true)->count() > 0 ? 'text-danger' : '';
            })
            ->rawColumns(['statut', 'progression', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('ronde-create');
        $planningsRonde = PlanningRonde::with('site')->orderBy('nom')->get();
        $agents = Employe::whereHas('user', function ($query) {
            $query->permission('ronde-create');
        })->where('etat', 1)->get();

        return view('sie.rondes.create', compact('planningsRonde', 'agents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'planning_ronde_id' => 'required|exists:plannings_ronde,id',
            'agent_id' => 'required|exists:employe,id'
        ]);

        try {
            DB::beginTransaction();

            $ronde = Ronde::create([
                'planning_ronde_id' => $validated['planning_ronde_id'],
                'agent_id' => $validated['agent_id'],
                'date_debut' => now(),
                'statut' => 'en_cours'
            ]);

            DB::commit();

            return redirect()
                ->route('sie.rondes.show', $ronde->id)
                ->with('success', 'Ronde créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la ronde: ' . $e->getMessage());
        }
    }

    public function scan($id)
    {
        $ronde = Ronde::with(['planningRonde.pointsControle', 'scans.pointControle'])
            ->findOrFail($id);

        $pointsScanned = $ronde->scans->pluck('point_controle_id')->toArray();

        $pointsRestants = $ronde->planningRonde
            ->pointsControle()
            ->whereNotIn('point_controle_id', $pointsScanned)
            ->orderBy('planning_ronde_points.ordre')
            ->get();

        return view('sie.rondes.scan', compact('ronde', 'pointsRestants'));
    }

    public function verifyQRCode(Request $request, $id)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $ronde = Ronde::findOrFail($id);
        $pointControle = PointControle::where('qr_code', $request->qr_code)->first();

        if (!$pointControle) {
            return response()->json([
                'success' => false,
                'message' => 'QR code invalide ou non reconnu'
            ], 400);
        }

        if (!$ronde->planningRonde->pointsControle->contains($pointControle->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce point de contrôle ne fait pas partie de cette ronde'
            ], 400);
        }

        if ($ronde->scans()->where('point_controle_id', $pointControle->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce point a déjà été scanné'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'point_controle_id' => $pointControle->id,
            'message' => 'Point de contrôle valide'
        ]);
    }

    public function storeAnomalie(Request $request, $id)
    {
        $validated = $request->validate([
            'point_controle_id' => 'required|exists:point_controles,id',
            'anomalie' => 'required|boolean',
            'type_anomalie' => 'required_if:anomalie,1',
            'description' => 'required_if:anomalie,1',
            'photo' => 'nullable|image|max:5120'
        ]);

        $photoPath = null;

        try {
            DB::beginTransaction();

            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('rondes/photos', 'public');
            }

            RondeScan::create([
                'ronde_id' => $id,
                'point_controle_id' => $validated['point_controle_id'],
                'date_scan' => now(),
                'anomalie' => $validated['anomalie'],
                'type_anomalie' => $validated['anomalie'] ? $validated['type_anomalie'] : null,
                'commentaire' => $validated['anomalie'] ? $validated['description'] : null,
                'photo_url' => $photoPath
            ]);

            $ronde = Ronde::findOrFail($id);
            $isComplete = $ronde->scans->count() >= $ronde->planningRonde->pointsControle->count();

            if ($isComplete) {
                $ronde->update([
                    'statut' => 'terminee',
                    'date_fin' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Point de contrôle enregistré avec succès',
                'ronde_terminee' => $isComplete
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Ronde $ronde)
    {
        $ronde->load(['planningRonde.pointsControle', 'agent', 'scans.pointControle']);
        return view('sie.rondes.show', compact('ronde'));
    }

    public function terminer(Request $request, Ronde $ronde)
    {
        try {
            $ronde->update([
                'statut' => 'terminee',
                'date_fin' => now(),
                'commentaire' => $request->commentaire ?? null
            ]);

            $this->notifierAnomaliesSiPresentes($ronde);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ronde terminée avec succès'
                ]);
            }

            return redirect()->route('sie.rondes.show', $ronde)
                ->with('success', 'Ronde terminée avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur terminer ronde: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la terminaison de la ronde: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Erreur lors de la terminaison de la ronde: ' . $e->getMessage());
        }
    }

    public function exportAnomalies($id)
    {
        $ronde = Ronde::with(['scans.pointControle', 'planningRonde', 'agent', 'planningRonde.site'])
            ->findOrFail($id);

        $anomalies = $ronde->scans()
            ->with('pointControle')
            ->where('anomalie', true)
            ->orderBy('date_scan')
            ->get();

        $pdf = PDF::loadView('sie.rondes.pdf.anomalies', compact('ronde', 'anomalies'));

        return $pdf->download('anomalies-ronde-' . $ronde->id . '.pdf');
    }

    /**
     * API: Récupérer les infos d'une ronde (JSON)
     */
    public function getRondeInfo($rondeId)
    {
        $ronde = Ronde::with(['planningRonde.pointsControle', 'agent', 'scans.pointControle', 'scans' => function ($q) {
            $q->orderBy('date_scan', 'desc');
        }])->find($rondeId);

        if (!$ronde) {
            return response()->json(['error' => 'Ronde non trouvée'], 404);
        }

        return response()->json(['ronde' => $ronde]);
    }

    /**
     * API: Enregistrer un scan (Normal ou Anomalie)
     */
    public function storeScan(Request $request)
    {
        $validated = $request->validate([
            'ronde_id' => 'required|exists:rondes,id',
            'point_controle_id' => 'required|exists:point_controles,id',
            'qr_code' => 'nullable|string',
            'anomalie' => 'required|boolean',
            'type_anomalie' => 'required_if:anomalie,true',
            'commentaire' => 'nullable|string',
            'gps_lat' => 'nullable|numeric',
            'gps_lng' => 'nullable|numeric'
        ]);

        try {
            DB::beginTransaction();

            $ronde = Ronde::findOrFail($validated['ronde_id']);
            if ($ronde->statut === 'terminee') {
                return response()->json(['error' => 'Cette ronde est déjà terminée'], 400);
            }

            $scan = RondeScan::create([
                'ronde_id' => $validated['ronde_id'],
                'point_controle_id' => $validated['point_controle_id'],
                'date_scan' => now(),
                'anomalie' => $validated['anomalie'],
                'type_anomalie' => $validated['anomalie'] ? $validated['type_anomalie'] : null,
                'commentaire' => $validated['commentaire'],
                'gps_lat' => $validated['gps_lat'] ?? null,
                'gps_lng' => $validated['gps_lng'] ?? null,
            ]);

            $totalPoints = $ronde->planningRonde->pointsControle->count();
            $scannedPoints = $ronde->scans()->distinct('point_controle_id')->count();

            if ($scannedPoints >= $totalPoints) {
                $ronde->update(['statut' => 'terminee', 'date_fin' => now()]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'scan' => $scan,
                'ronde_terminee' => $scannedPoints >= $totalPoints
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur storeScan: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur enregistrement scan'], 500);
        }
    }

    /**
     * API: Upload photo pour un scan
     */
    public function storePhoto(Request $request, $scanId)
    {
        $scan = RondeScan::find($scanId);
        if (!$scan) {
            return response()->json(['error' => 'Scan introuvable'], 404);
        }

        if ($request->hasFile('photo')) {
            try {
                $path = $request->file('photo')->store('rondes/photos', 'public');
                $scan->update(['photo_url' => $path]);
                return response()->json(['success' => true, 'url' => $path]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Upload failed'], 500);
            }
        }
        return response()->json(['error' => 'No photo provided'], 400);
    }

    /**
     * API: Stats Dashboard Rondes
     */
    public function getStats()
    {
        $stats = [
            'totalRondes' => Ronde::count(),
            'enCoursCount' => Ronde::where('statut', 'en_cours')->count(),
            'termineesCount' => Ronde::where('statut', 'terminee')->count(),
            'anomaliesCount' => RondeScan::where('anomalie', true)->count()
        ];
        return response()->json($stats);
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
            Log::error('Erreur lors de l\'envoi de notification: ' . $e->getMessage());
        }
    }
}
