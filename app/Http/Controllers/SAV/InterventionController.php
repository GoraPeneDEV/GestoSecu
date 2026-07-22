<?php

namespace App\Http\Controllers\SAV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\SAV\Intervention;
use App\Models\SAV\Maintenance;
use App\Models\SAV\ClientAsset;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InterventionController extends Controller
{
    public function index()
    {
        $this->authorize('sav-intervention-view');

        $interventions = Intervention::with(['site', 'technicien', 'maintenance'])->latest()->paginate(20);

        return view('sav.interventions.index', compact('interventions'));
    }

    public function create(Request $request)
    {
        $this->authorize('sav-intervention-create');

        $site = null;
        $maintenance = null;
        $assets = collect();

        if ($request->filled('maintenance_id')) {
            $maintenance = Maintenance::with('site')->findOrFail($request->maintenance_id);
            $site = $maintenance->site;
        } elseif ($request->filled('site_id')) {
            $site = Site::findOrFail($request->site_id);
        }

        if ($site) {
            $assets = ClientAsset::where('site_id', $site->id)->get();
        }

        $sites = Site::orderBy('nom_site')->get();

        return view('sav.interventions.create', compact('site', 'maintenance', 'assets', 'sites'));
    }

    public function store(Request $request)
    {
        $this->authorize('sav-intervention-create');

        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'type' => 'required|in:ponctuelle,maintenance_prevue',
            'date_intervention' => 'required|date',
            'maintenance_id' => 'nullable|exists:maintenances,id',
            'recommandations_generales' => 'nullable|string',
            'assets' => 'nullable|array',
            'assets.*.id' => 'required|exists:client_assets,id',
            'assets.*.actions' => 'nullable|string',
            'assets.*.recommandation' => 'nullable|string',
            'assets.*.statut' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $intervention = Intervention::create([
                'type' => $request->type,
                'maintenance_id' => $request->maintenance_id,
                'contrat_id' => $request->maintenance_id ? Maintenance::find($request->maintenance_id)->contrat_id : $request->contrat_id,
                'site_id' => $request->site_id,
                'technicien_id' => Auth::id(),
                'date_intervention' => $request->date_intervention,
                'recommandations_generales' => $request->recommandations_generales,
                'statut' => 'termine',
            ]);

            if ($request->has('assets')) {
                foreach ($request->assets as $assetData) {
                    if (isset($assetData['checked'])) {
                        $intervention->assets()->attach($assetData['id'], [
                            'actions_faites' => $assetData['actions'] ?? null,
                            'recommandation_specifique' => $assetData['recommandation'] ?? null,
                            'statut_apres' => $assetData['statut'] ?? null,
                        ]);

                        if (isset($assetData['statut_global'])) {
                            ClientAsset::where('id', $assetData['id'])->update(['status' => $assetData['statut_global']]);
                        }
                    }
                }
            }

            if ($request->maintenance_id) {
                Maintenance::where('id', $request->maintenance_id)->update(['status' => 'realisee', 'date_realisation' => now()]);
            }

            return redirect()->route('sav.interventions.show', $intervention)
                ->with('success', 'Rapport d\'intervention enregistré avec succès.');
        });
    }

    public function show(Intervention $intervention)
    {
        $this->authorize('sav-intervention-view');

        $intervention->load(['site.client', 'technicien', 'assets', 'maintenance.contrat']);

        return view('sav.interventions.show', compact('intervention'));
    }

    public function edit(Intervention $intervention)
    {
        $this->authorize('sav-intervention-edit');

        $intervention->load(['site', 'assets']);

        return view('sav.interventions.edit', compact('intervention'));
    }

    public function update(Request $request, Intervention $intervention)
    {
        $this->authorize('sav-intervention-edit');

        $validated = $request->validate([
            'date_intervention' => 'required|date',
            'recommandations_generales' => 'nullable|string',
        ]);

        $intervention->update($validated);

        return redirect()->route('sav.interventions.show', $intervention)->with('success', 'Rapport mis à jour.');
    }

    public function downloadPdf(Intervention $intervention)
    {
        $this->authorize('sav-intervention-view');

        $intervention->load(['site.client', 'technicien', 'assets', 'maintenance.contrat']);

        $pdf = Pdf::loadView('sav.interventions.pdf', compact('intervention'))->setPaper('a4', 'portrait');

        $filename = sprintf('intervention-%s-%s.pdf', $intervention->numero_intervention, $intervention->date_intervention->format('Y-m-d'));

        return $pdf->download($filename);
    }
}
