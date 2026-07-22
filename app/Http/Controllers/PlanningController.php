<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use App\Models\Employe;
use App\Models\Site;
use App\Models\HorairePlanning;
use App\Models\DetailPlanningHorizontal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PlanningController extends Controller
{
    public function index()
    {
        $this->authorize('planning-view');

        $employes = Employe::where('etat', 1)->get();
        $sites = Site::all();
        $planningsActifs = Planning::whereNull('date_fin')->count();

        return view('plannings.index', compact('employes', 'sites', 'planningsActifs'));
    }

    public function create()
    {
        $this->authorize('planning-create');

        $employes = Employe::where('etat', 1)->get();
        $sites = Site::all();
        $horaires = HorairePlanning::all();

        return view('plannings.create', compact('employes', 'sites', 'horaires'));
    }

    public function getPlannings(Request $request)
    {
        $this->authorize('planning-view');

        $query = Planning::with(['employe.departement', 'site.client'])->whereNull('date_fin');

        if ($request->filled('departement')) {
            $query->whereHas('employe.departement', fn($q) => $q->where('nom', $request->departement));
        }
        if ($request->filled('site')) {
            $query->where('site_id', $request->site);
        }
        if ($request->filled('search_agent')) {
            $searchAgent = $request->search_agent;
            $query->whereHas('employe', function ($q) use ($searchAgent) {
                $q->where('prenom', 'LIKE', "%{$searchAgent}%")
                    ->orWhere('nom', 'LIKE', "%{$searchAgent}%")
                    ->orWhere('matricule', 'LIKE', "%{$searchAgent}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('employe_info', fn($p) => '<div class="d-flex flex-column"><span class="fw-medium">' . e($p->employe->prenom) . ' ' . e($p->employe->nom) . '</span><small class="text-muted">Matricule: ' . e($p->employe->matricule ?? 'N/A') . '</small></div>')
            ->addColumn('site_info', function ($p) {
                if (!$p->site) {
                    return '<span class="text-danger fw-medium">Site supprimé</span>';
                }
                return '<div class="d-flex flex-column"><span class="fw-medium">' . e($p->site->nom_site) . '</span><small class="text-muted">Client: ' . e($p->site->client->nomClient ?? 'N/A') . '</small></div>';
            })
            ->addColumn('departement_name', fn($p) => $p->employe->departement->nom ?? 'N/A')
            ->addColumn('actions', fn($p) => '<div class="d-flex align-items-center">
                <a href="' . route('plannings.show', $p->id) . '" class="btn btn-icon btn-outline-primary me-2"><i class="ti ti-eye"></i></a>
                <a href="' . route('plannings.edit', $p->id) . '" class="btn btn-icon btn-outline-warning me-2"><i class="ti ti-pencil"></i></a>
                <button type="button" class="btn btn-icon btn-outline-danger delete-planning" data-id="' . $p->id . '"><i class="ti ti-trash"></i></button></div>')
            ->editColumn('date_debut', fn($p) => $p->date_debut ? $p->date_debut->format('d/m/Y') : '')
            ->rawColumns(['employe_info', 'site_info', 'actions'])
            ->make(true);
    }

    public function show(Planning $planning)
    {
        $this->authorize('planning-view');

        $planning->load(['employe.departement', 'site', 'detailsHorizontal.horaire', 'createur']);

        return view('plannings.show', compact('planning'));
    }

    public function store(Request $request)
    {
        $this->authorize('planning-create');

        $request->validate([
            'employe_id' => 'required|exists:employe,id',
            'site_id' => 'required|exists:sites,id',
            'date_debut' => 'required|date_format:d/m/Y',
            'horaires' => 'nullable|array',
            'horaires.*' => 'nullable|exists:horaires_planning,id',
        ]);

        try {
            DB::beginTransaction();

            $planningActif = Planning::where('employe_id', $request->employe_id)->whereNull('date_fin')->first();
            if ($planningActif) {
                $planningActif->terminer(Carbon::createFromFormat('d/m/Y', $request->date_debut)->subDay());
            }

            $planning = Planning::create([
                'employe_id' => $request->employe_id,
                'site_id' => $request->site_id,
                'date_debut' => Carbon::createFromFormat('d/m/Y', $request->date_debut),
                'type_planning' => 'horizontal',
                'created_by' => Auth::id(),
            ]);

            $this->syncHoraires($planning, $request);

            DB::commit();

            return redirect()->route('plannings.index')->with('success', 'Planning créé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la création du planning: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $this->authorize('planning-update');

        $planning = Planning::with(['employe', 'site', 'detailsHorizontal.horaire'])->findOrFail($id);
        $sites = Site::all();
        $horaires = HorairePlanning::all();

        return view('plannings.edit', compact('planning', 'sites', 'horaires'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('planning-update');

        $planning = Planning::findOrFail($id);

        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'horaires' => 'nullable|array',
            'horaires.*' => 'nullable|exists:horaires_planning,id',
        ]);

        try {
            DB::beginTransaction();

            $planning->update(['site_id' => $request->site_id]);
            $planning->detailsHorizontal()->delete();
            $this->syncHoraires($planning, $request);

            DB::commit();

            return redirect()->route('plannings.index')->with('success', 'Planning mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $this->authorize('planning-delete');

        try {
            Planning::findOrFail($id)->delete();

            return response()->json(['success' => true, 'message' => 'Planning supprimé avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function syncHoraires(Planning $planning, Request $request): void
    {
        if (!$request->has('horaires') || !is_array($request->horaires)) {
            return;
        }

        foreach (DetailPlanningHorizontal::JOURS as $jour) {
            if (!empty($request->horaires[$jour])) {
                $planning->detailsHorizontal()->create([
                    'jour_semaine' => $jour,
                    'horaire_id' => $request->horaires[$jour],
                ]);
            }
        }
    }
}
