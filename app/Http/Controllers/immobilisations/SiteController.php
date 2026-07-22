<?php

namespace App\Http\Controllers\immobilisations;

use App\Http\Controllers\Controller;
use App\Models\ImmobilisationSite;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SiteController extends Controller
{
    public function index()
    {
        $this->authorize('immobilisation-sites-manage');

        return view('immobilisations.sites.index');
    }

    public function data()
    {
        $this->authorize('immobilisation-sites-manage');

        $query = ImmobilisationSite::query();

        return DataTables::of($query)
            ->addColumn('type_libelle', function ($site) {
                $labels = [
                    'siege' => 'Siège',
                    'annexe' => 'Annexe',
                    'depot' => 'Dépôt',
                    'agence' => 'Agence',
                    'autre' => 'Autre',
                ];
                return $labels[$site->type] ?? $site->type;
            })
            ->addColumn('statut_badge', function ($site) {
                return $site->statut === 'actif'
                    ? '<span class="badge bg-success">Actif</span>'
                    : '<span class="badge bg-secondary">Inactif</span>';
            })
            ->addColumn('nb_biens', function ($site) {
                return $site->immobilisations()->count();
            })
            ->addColumn('valeur_totale', function ($site) {
                return number_format($site->immobilisations()->sum('valeur_acquisition'), 0, ',', ' ') . ' FCFA';
            })
            ->addColumn('actions', function ($site) {
                $actions = '<button class="btn btn-sm btn-icon btn-warning" onclick="editSite(' . $site->id . ')">
                    <i class="ti ti-pencil"></i></button>';
                
                if ($site->statut === 'actif') {
                    $actions .= '<button class="btn btn-sm btn-icon btn-secondary" onclick="toggleSiteStatus(' . $site->id . ')">
                        <i class="ti ti-ban"></i></button>';
                } else {
                    $actions .= '<button class="btn btn-sm btn-icon btn-success" onclick="toggleSiteStatus(' . $site->id . ')">
                        <i class="ti ti-check"></i></button>';
                }
                
                return $actions;
            })
            ->rawColumns(['statut_badge', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $this->authorize('immobilisation-sites-manage');

        $validated = $request->validate([
            'code_site' => 'required|string|max:50|unique:immobilisation_sites',
            'libelle' => 'required|string|max:255',
            'type' => 'required|in:siege,annexe,depot,agence,autre',
            'adresse' => 'nullable|string',
        ]);

        ImmobilisationSite::create([
            ...$validated,
            'statut' => 'actif',
        ]);

        return response()->json(['success' => true, 'message' => 'Site créé avec succès.']);
    }

    public function edit(ImmobilisationSite $site)
    {
        $this->authorize('immobilisation-sites-manage');

        return response()->json($site);
    }

    public function update(Request $request, ImmobilisationSite $site)
    {
        $this->authorize('immobilisation-sites-manage');

        $validated = $request->validate([
            'code_site' => 'required|string|max:50|unique:immobilisation_sites,code_site,' . $site->id,
            'libelle' => 'required|string|max:255',
            'type' => 'required|in:siege,annexe,depot,agence,autre',
            'adresse' => 'nullable|string',
        ]);

        $site->update($validated);

        return response()->json(['success' => true, 'message' => 'Site mis à jour avec succès.']);
    }

    public function toggleStatus(ImmobilisationSite $site)
    {
        $this->authorize('immobilisation-sites-manage');

        $site->statut = $site->statut === 'actif' ? 'inactif' : 'actif';
        $site->save();

        $message = $site->statut === 'actif' 
            ? 'Site activé avec succès.' 
            : 'Site désactivé avec succès.';

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function getEmplacements(ImmobilisationSite $site)
    {
        $emplacements = $site->emplacements()->actifs()->get(['id', 'code', 'libelle']);
        return response()->json($emplacements);
    }
}
