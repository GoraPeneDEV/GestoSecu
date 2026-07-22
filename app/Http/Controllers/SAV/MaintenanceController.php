<?php

namespace App\Http\Controllers\SAV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\SAV\Maintenance;
use App\Models\SAV\Contrat;
use App\Models\Site;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('sav-maintenance-view');

        $query = Maintenance::with(['contrat.client', 'site']);

        if ($request->filled('contrat_id')) {
            $query->where('contrat_id', $request->contrat_id);
        }
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        $maintenances = $query->latest()->get();

        if ($request->ajax()) {
            return response()->json($maintenances);
        }

        $sites = Site::with('client')->orderBy('nom_site')->get();
        $contratsActifs = Contrat::where('statut', 'actif')->with('client')->orderBy('numero_contrat')->get();
        $prochaines = Maintenance::where('status', 'planifiee')
            ->where('date_prevue', '>=', now())
            ->orderBy('date_prevue')
            ->with('site')
            ->limit(5)
            ->get();

        return view('sav.maintenances.index', compact('maintenances', 'sites', 'contratsActifs', 'prochaines'));
    }

    public function store(Request $request)
    {
        $this->authorize('sav-maintenance-create');

        $validated = $request->validate([
            'contrat_id' => 'required|exists:contrats,id',
            'site_id' => 'required|exists:sites,id',
            'date_prevue' => 'required|date|after_or_equal:today',
            'description' => 'nullable|string',
        ]);

        Maintenance::create($validated);

        return back()->with('success', 'Maintenance planifiée avec succès.');
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        $this->authorize('sav-maintenance-edit');

        $validated = $request->validate([
            'date_prevue' => 'required|date',
            'status' => 'required|in:planifiee,en_cours,realisee,annulee,reportee',
            'description' => 'nullable|string',
            'date_realisation' => 'nullable|date',
        ]);

        $maintenance->update($validated);

        return back()->with('success', 'Maintenance mise à jour.');
    }

    public function destroy(Maintenance $maintenance)
    {
        $this->authorize('sav-maintenance-delete');

        $maintenance->delete();

        return back()->with('success', 'Maintenance supprimée.');
    }

    public function show(Maintenance $maintenance)
    {
        $this->authorize('sav-maintenance-view');

        $maintenance->load(['contrat.client', 'site', 'interventions']);

        return view('sav.maintenances.show', compact('maintenance'));
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('sav-maintenance-view');

        $query = Maintenance::with(['contrat.client', 'site']);

        if ($request->filled('contrat_id')) {
            $query->where('contrat_id', $request->contrat_id);
        }
        if ($request->filled('statut')) {
            $query->where('status', $request->statut);
        }

        $maintenances = $query->orderBy('date_prevue')->get();

        $pdf = Pdf::loadView('sav.maintenances.pdf', compact('maintenances'))->setPaper('a4', 'landscape');

        return $pdf->download('maintenances-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Retourne les sites du client rattaché au contrat (AJAX)
     */
    public function sitesByContrat(Request $request)
    {
        $this->authorize('sav-maintenance-view');

        $contrat = Contrat::findOrFail($request->integer('contrat_id'));
        $sites = Site::where('client_id', $contrat->client_id)->orderBy('nom_site')->get();

        return response()->json($sites->map(fn($s) => ['id' => $s->id, 'nom_site' => $s->nom_site]));
    }
}
