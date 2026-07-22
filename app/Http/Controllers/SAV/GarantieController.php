<?php

namespace App\Http\Controllers\SAV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SAV\Garantie;
use App\Models\SAV\Contrat;
use App\Models\Client;
use Yajra\DataTables\Facades\DataTables;

class GarantieController extends Controller
{
    private array $types = ['main_oeuvre' => "Main d'œuvre", 'pieces' => 'Pièces', 'totale' => 'Totale'];

    private array $statuts = ['active' => 'Active', 'expiree' => 'Expirée', 'resiliee' => 'Résiliée', 'en_reclamation' => 'En réclamation'];

    public function index(Request $request)
    {
        $this->authorize('sav-garantie-view');

        if ($request->ajax()) {
            $query = Garantie::with(['client', 'contrat']);

            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            return DataTables::of($query)
                ->addColumn('client_nom', fn($g) => $g->client ? $g->client->nomClient : '-')
                ->addColumn('type_label', fn($g) => $g->type_label)
                ->addColumn('statut_badge', fn($g) => $g->statut_badge)
                ->addColumn('date_fin_fmt', fn($g) => $g->date_fin->format('d/m/Y'))
                ->addColumn('jours_restants', function ($g) {
                    $jours = $g->joursRestants();
                    if ($jours <= 0) {
                        return '<span class="badge bg-secondary">Expirée</span>';
                    } elseif ($jours <= 30) {
                        return '<span class="badge bg-warning text-dark">' . $jours . ' j</span>';
                    }
                    return '<span class="badge bg-success">' . $jours . ' j</span>';
                })
                ->addColumn('actions', fn($g) => '
                    <a href="' . route('sav.garanties.show', $g->id) . '" class="btn btn-sm btn-info me-1"><i class="ti ti-eye"></i></a>
                    <a href="' . route('sav.garanties.edit', $g->id) . '" class="btn btn-sm btn-warning me-1"><i class="ti ti-pencil"></i></a>
                ')
                ->rawColumns(['statut_badge', 'jours_restants', 'actions'])
                ->make(true);
        }

        $stats = [
            'actives' => Garantie::actives()->count(),
            'expirant_30' => Garantie::expirant(30)->count(),
            'total' => Garantie::count(),
        ];

        $clients = Client::orderBy('nomClient')->get();

        return view('sav.garanties.index', compact('stats', 'clients'));
    }

    public function create()
    {
        $this->authorize('sav-garantie-create');

        $clients = Client::orderBy('nomClient')->get();
        $contrats = Contrat::where('statut', 'actif')->with('client')->orderBy('numero_contrat')->get();

        return view('sav.garanties.create', ['clients' => $clients, 'contrats' => $contrats, 'types' => $this->types]);
    }

    public function store(Request $request)
    {
        $this->authorize('sav-garantie-create');

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contrat_id' => 'nullable|exists:contrats,id',
            'type' => 'required|in:main_oeuvre,pieces,totale',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'duree_mois' => 'required|integer|min:1',
            'conditions' => 'nullable|string',
            'exclusions' => 'nullable|string',
        ]);

        Garantie::create($validated);

        return redirect()->route('sav.garanties.index')->with('success', 'Garantie créée avec succès.');
    }

    public function show(Garantie $garantie)
    {
        $this->authorize('sav-garantie-view');

        $garantie->load(['client', 'contrat']);

        return view('sav.garanties.show', compact('garantie'));
    }

    public function edit(Garantie $garantie)
    {
        $this->authorize('sav-garantie-edit');

        $clients = Client::orderBy('nomClient')->get();
        $contrats = Contrat::where('statut', 'actif')->with('client')->orderBy('numero_contrat')->get();

        return view('sav.garanties.edit', [
            'garantie' => $garantie,
            'clients' => $clients,
            'contrats' => $contrats,
            'types' => $this->types,
            'statuts' => $this->statuts,
        ]);
    }

    public function update(Request $request, Garantie $garantie)
    {
        $this->authorize('sav-garantie-edit');

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contrat_id' => 'nullable|exists:contrats,id',
            'type' => 'required|in:main_oeuvre,pieces,totale',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'duree_mois' => 'required|integer|min:1',
            'statut' => 'required|in:active,expiree,resiliee,en_reclamation',
            'conditions' => 'nullable|string',
            'exclusions' => 'nullable|string',
        ]);

        $garantie->update($validated);

        return redirect()->route('sav.garanties.show', $garantie)->with('success', 'Garantie mise à jour avec succès.');
    }

    public function destroy(Garantie $garantie)
    {
        $this->authorize('sav-garantie-delete');

        $garantie->delete();

        return redirect()->route('sav.garanties.index')->with('success', 'Garantie supprimée.');
    }
}
