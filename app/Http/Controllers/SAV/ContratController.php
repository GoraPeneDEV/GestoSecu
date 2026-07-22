<?php

namespace App\Http\Controllers\SAV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\SAV\Contrat;
use App\Models\Client;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class ContratController extends Controller
{
    private array $types = [
        'maintenance' => 'Maintenance',
        'gardiennage' => 'Gardiennage',
        'securite_electronique' => 'Sécurité Électronique',
        'securite_incendie' => 'Sécurité Incendie',
        'monetique' => 'Monétique',
        'nettoyage' => 'Nettoyage',
        'it' => 'IT',
        'formation' => 'Formation',
        'prestation_ponctuelle' => 'Prestation Ponctuelle',
        'mixte' => 'Mixte',
    ];

    public function index(Request $request)
    {
        $this->authorize('sav-contrat-view');

        if ($request->ajax()) {
            $query = Contrat::with(['client', 'responsableSav']);

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
                ->addColumn('client_nom', fn($contrat) => $contrat->client ? $contrat->client->nomClient : '-')
                ->addColumn('type_label', fn($contrat) => $contrat->type_label)
                ->addColumn('statut_badge', fn($contrat) => $contrat->statut_badge)
                ->addColumn('periode', fn($contrat) => $contrat->date_debut->format('d/m/Y') . ' - ' . $contrat->date_fin->format('d/m/Y'))
                ->addColumn('jours_restant', function ($contrat) {
                    $jours = $contrat->joursRestants();
                    if ($jours <= 0) {
                        return '<span class="badge bg-danger">Expiré</span>';
                    } elseif ($jours <= 30) {
                        return '<span class="badge bg-warning">' . $jours . ' j</span>';
                    }
                    return '<span class="badge bg-success">' . $jours . ' j</span>';
                })
                ->addColumn('actions', fn($contrat) => '<a href="' . route('sav.contrats.show', $contrat->id) . '" class="btn btn-sm btn-info"><i class="ti ti-eye"></i></a>')
                ->rawColumns(['statut_badge', 'jours_restant', 'actions'])
                ->make(true);
        }

        $clients = Client::all();

        return view('sav.contrats.index', ['clients' => $clients, 'types' => $this->types]);
    }

    public function create(Request $request)
    {
        $this->authorize('sav-contrat-create');

        $clients = Client::all();
        $users = User::where('status', 'active')->get();
        $clientPreselected = $request->filled('client_id') ? Client::find($request->client_id) : null;

        return view('sav.contrats.create', [
            'clients' => $clients,
            'users' => $users,
            'clientPreselected' => $clientPreselected,
            'types' => $this->types,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('sav-contrat-create');

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|in:maintenance,gardiennage,securite_electronique,securite_incendie,monetique,nettoyage,it,formation,prestation_ponctuelle,mixte',
            'date_signature' => 'required|date',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'montant_total' => 'required|numeric|min:0',
            'frequence_paiement' => 'required|in:mensuel,trimestriel,semestriel,annuel,unique',
            'prestations_incluses' => 'nullable|string',
            'delai_intervention_heures' => 'nullable|integer|min:1',
            'garantie_incluse' => 'boolean',
            'duree_garantie_mois' => 'nullable|integer|min:1',
            'responsable_sav_id' => 'nullable|exists:users,id',
            'fichier_contrat' => 'nullable|file|mimes:pdf|max:204800',
        ]);

        DB::beginTransaction();

        try {
            $fichierPath = null;
            if ($request->hasFile('fichier_contrat')) {
                $fichierPath = $request->file('fichier_contrat')->store('contrats', 'public');
            }

            $contrat = Contrat::create([
                'client_id' => $validated['client_id'],
                'type' => $validated['type'],
                'date_signature' => $validated['date_signature'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'statut' => 'actif',
                'montant_total' => $validated['montant_total'],
                'frequence_paiement' => $validated['frequence_paiement'],
                'prestations_incluses' => $validated['prestations_incluses'] ?? null,
                'delai_intervention_heures' => $validated['delai_intervention_heures'] ?? 24,
                'garantie_incluse' => $validated['garantie_incluse'] ?? false,
                'duree_garantie_mois' => $validated['duree_garantie_mois'] ?? null,
                'responsable_sav_id' => $validated['responsable_sav_id'] ?? null,
                'signataire_id' => Auth::id(),
                'fichier_contrat' => $fichierPath,
            ]);

            DB::commit();

            return redirect()->route('sav.contrats.show', $contrat->id)
                ->with('success', 'Contrat créé avec succès : ' . $contrat->numero_contrat);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    public function show(Contrat $contrat)
    {
        $this->authorize('sav-contrat-view');

        $contrat->load(['client.contacts', 'responsableSav', 'signataire', 'fichesProgres', 'garanties']);

        return view('sav.contrats.show', compact('contrat'));
    }

    public function edit(Contrat $contrat)
    {
        $this->authorize('sav-contrat-edit');

        $users = User::where('status', 'active')->get();

        return view('sav.contrats.edit', compact('contrat', 'users'));
    }

    public function update(Request $request, Contrat $contrat)
    {
        $this->authorize('sav-contrat-edit');

        $validated = $request->validate([
            'statut' => 'required|in:brouillon,en_attente_signature,actif,suspendu,resilie,expire,renouvele',
            'date_signature' => 'required|date',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'montant_total' => 'required|numeric|min:0',
            'frequence_paiement' => 'required|in:mensuel,trimestriel,semestriel,annuel,unique',
            'prestations_incluses' => 'nullable|string',
            'delai_intervention_heures' => 'nullable|integer|min:1',
            'responsable_sav_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'fichier_contrat' => 'nullable|file|mimes:pdf|max:204800',
        ]);

        if ($request->hasFile('fichier_contrat')) {
            if ($contrat->fichier_contrat) {
                Storage::disk('public')->delete($contrat->fichier_contrat);
            }
            $validated['fichier_contrat'] = $request->file('fichier_contrat')->store('contrats', 'public');
        }

        $contrat->update($validated);

        return redirect()->route('sav.contrats.show', $contrat->id)->with('success', 'Contrat mis à jour avec succès');
    }

    public function download(Contrat $contrat)
    {
        $this->authorize('sav-contrat-view');

        if (!$contrat->fichier_contrat) {
            abort(404, 'Aucun fichier associé');
        }

        return Storage::disk('public')->download($contrat->fichier_contrat);
    }

    public function renouveler(Request $request, Contrat $contrat)
    {
        $this->authorize('sav-contrat-renouveler');

        $validated = $request->validate([
            'nouvelle_date_fin' => 'required|date|after:' . $contrat->date_fin,
            'nouveau_montant' => 'nullable|numeric|min:0',
        ]);

        $contrat->update(['statut' => 'renouvele']);

        $nouveauContrat = Contrat::create([
            'client_id' => $contrat->client_id,
            'type' => $contrat->type,
            'date_signature' => now(),
            'date_debut' => $contrat->date_fin->addDay(),
            'date_fin' => $validated['nouvelle_date_fin'],
            'statut' => 'actif',
            'montant_total' => $validated['nouveau_montant'] ?? $contrat->montant_total,
            'frequence_paiement' => $contrat->frequence_paiement,
            'prestations_incluses' => $contrat->prestations_incluses,
            'delai_intervention_heures' => $contrat->delai_intervention_heures,
            'garantie_incluse' => $contrat->garantie_incluse,
            'duree_garantie_mois' => $contrat->duree_garantie_mois,
            'responsable_sav_id' => $contrat->responsable_sav_id,
            'signataire_id' => Auth::id(),
        ]);

        return redirect()->route('sav.contrats.show', $nouveauContrat->id)
            ->with('success', 'Contrat renouvelé : ' . $nouveauContrat->numero_contrat);
    }

    public function destroy(Contrat $contrat)
    {
        $this->authorize('sav-contrat-delete');

        if ($contrat->fichier_contrat) {
            Storage::disk('public')->delete($contrat->fichier_contrat);
        }

        $contrat->delete();

        return redirect()->route('sav.contrats.index')->with('success', 'Contrat supprimé');
    }
}
