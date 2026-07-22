<?php

namespace App\Http\Controllers\SAV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\SAV\FicheProgres;
use App\Models\SAV\FicheProgresAction;
use App\Models\SAV\FicheProgresPieceJointe;
use App\Models\SAV\ClientInteraction;
use App\Models\Client;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class FicheProgresController extends Controller
{
    private array $types = [
        'amelioration' => 'Amélioration',
        'reclamation' => 'Réclamation',
        'incident' => 'Incident',
        'dysfonctionnement' => 'Dysfonctionnement',
        'non_conformite' => 'Non-Conformité',
    ];

    private array $processus = [
        'gardiennage' => 'Gardiennage',
        'securite_electronique' => 'Sécurité Électronique',
        'securite_incendie' => 'Sécurité Incendie',
        'monetique' => 'Monétique',
        'nettoyage' => 'Nettoyage',
        'formation' => 'Formation',
        'solution_it' => 'Solution IT',
        'comptabilite' => 'Comptabilité',
        'commercial' => 'Commercial',
        'accueil' => 'Accueil',
    ];

    public function index(Request $request)
    {
        $this->authorize('sav-fiche-progres-view');

        if ($request->ajax()) {
            $query = FicheProgres::with(['client', 'createur', 'piloteProcessus']);

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }
            if ($request->filled('client_id')) {
                $query->where('client_id', $request->client_id);
            }
            if ($request->filled('processus')) {
                $query->where('processus_concerne', $request->processus);
            }

            return DataTables::of($query)
                ->addColumn('client_nom', fn($fiche) => $fiche->client ? $fiche->client->nomClient : '-')
                ->addColumn('type_label', fn($fiche) => $fiche->type_label)
                ->addColumn('processus_label', fn($fiche) => $fiche->processus_label)
                ->addColumn('statut_badge', fn($fiche) => $fiche->statut_badge)
                ->addColumn('avancement', fn($fiche) => '<div class="progress" style="height: 6px;">
                    <div class="progress-bar" role="progressbar" style="width: ' . $fiche->pourcentageAvancement() . '%"></div>
                </div><small>' . $fiche->pourcentageAvancement() . '%</small>')
                ->addColumn('actions', fn($fiche) => '<a href="' . route('sav.fiches-progres.show', $fiche->id) . '" class="btn btn-sm btn-info"><i class="ti ti-eye"></i></a>')
                ->rawColumns(['statut_badge', 'avancement', 'actions'])
                ->make(true);
        }

        $clients = Client::all();

        return view('sav.fiches-progres.index', ['clients' => $clients, 'types' => $this->types, 'processus' => $this->processus]);
    }

    public function create(Request $request)
    {
        $this->authorize('sav-fiche-progres-create');

        $clients = Client::all();
        $clientPreselected = $request->filled('client_id') ? Client::find($request->client_id) : null;

        return view('sav.fiches-progres.create', [
            'clients' => $clients,
            'clientPreselected' => $clientPreselected,
            'types' => $this->types,
            'processus' => $this->processus,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('sav-fiche-progres-create');

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contact_id' => 'nullable|exists:client_contacts,id',
            'type' => 'required|in:amelioration,reclamation,incident,dysfonctionnement,non_conformite',
            'processus_concerne' => 'required|in:gardiennage,securite_electronique,securite_incendie,monetique,nettoyage,formation,solution_it,comptabilite,commercial,accueil',
            'objet' => 'required|string|max:255',
            'constat_client' => 'required|string',
            'contrat_id' => 'nullable|exists:contrats,id',
        ]);

        DB::beginTransaction();

        try {
            $fiche = FicheProgres::create([
                'client_id' => $validated['client_id'],
                'contact_id' => $validated['contact_id'] ?? null,
                'contrat_id' => $validated['contrat_id'] ?? null,
                'type' => $validated['type'],
                'processus_concerne' => $validated['processus_concerne'],
                'objet' => $validated['objet'],
                'constat_client' => $validated['constat_client'],
                'cree_par' => Auth::id(),
                'statut' => 'nouveau',
            ]);

            ClientInteraction::create([
                'client_id' => $validated['client_id'],
                'type' => 'ticket_sav',
                'sujet' => 'Nouvelle fiche de progrès : ' . $validated['objet'],
                'contenu' => $validated['constat_client'],
                'canal' => 'portail',
                'sens' => 'entrant',
                'user_id' => Auth::id(),
                'relatable_type' => FicheProgres::class,
                'relatable_id' => $fiche->id,
            ]);

            DB::commit();

            return redirect()->route('sav.fiches-progres.show', $fiche->id)
                ->with('success', 'Fiche de progrès créée avec succès : ' . $fiche->numero_fiche);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    public function show(FicheProgres $ficheProgres)
    {
        $this->authorize('sav-fiche-progres-view');

        $ficheProgres->load([
            'client.contacts',
            'contact',
            'contrat',
            'createur',
            'piloteProcessus',
            'responsableQualite',
            'actions.responsable',
            'piecesJointes.uploadedBy',
        ]);

        $users = User::where('status', 'active')->get();

        return view('sav.fiches-progres.show', compact('ficheProgres', 'users'));
    }

    public function updateAnalyse5M(Request $request, FicheProgres $ficheProgres)
    {
        $this->authorize('sav-fiche-progres-analyse');

        $validated = $request->validate([
            'matiere' => 'nullable|string',
            'milieu' => 'nullable|string',
            'methodes' => 'nullable|string',
            'materiel' => 'nullable|string',
            'main_oeuvre' => 'nullable|string',
        ]);

        $ficheProgres->update([
            'analyse_5m' => $validated,
            'cause_analyse' => implode("\n", array_filter($validated)),
            'statut' => 'plan_action_etabli',
        ]);

        return back()->with('success', 'Analyse 5M enregistrée');
    }

    public function addAction(Request $request, FicheProgres $ficheProgres)
    {
        $this->authorize('sav-fiche-progres-edit');

        $validated = $request->validate([
            'description' => 'required|string',
            'responsable_id' => 'required|exists:users,id',
            'date_echeance' => 'required|date|after_or_equal:today',
        ]);

        FicheProgresAction::create([
            'fiche_progres_id' => $ficheProgres->id,
            'description' => $validated['description'],
            'responsable_id' => $validated['responsable_id'],
            'date_echeance' => $validated['date_echeance'],
        ]);

        if ($ficheProgres->statut === 'plan_action_etabli') {
            $ficheProgres->update(['statut' => 'actions_en_cours']);
        }

        return back()->with('success', 'Action ajoutée au plan');
    }

    public function realiserAction(Request $request, FicheProgres $ficheProgres, FicheProgresAction $action)
    {
        $this->authorize('sav-fiche-progres-edit');

        if ($action->fiche_progres_id !== $ficheProgres->id) {
            abort(404);
        }

        $action->marquerRealisee($request->commentaire);

        return back()->with('success', 'Action marquée comme réalisée');
    }

    public function evaluer(Request $request, FicheProgres $ficheProgres)
    {
        $this->authorize('sav-fiche-progres-evaluer');

        $validated = $request->validate([
            'efficacite_actions' => 'required|boolean',
            'commentaire_efficacite' => 'required|string',
            'redemarrage_analyse' => 'nullable|boolean',
        ]);

        if (!$ficheProgres->peutEvaluer()) {
            return back()->with('error', 'Toutes les actions doivent être réalisées avant évaluation.');
        }

        if ($validated['efficacite_actions'] && empty($validated['redemarrage_analyse'])) {
            $ficheProgres->update([
                'efficacite_actions' => true,
                'commentaire_efficacite' => $validated['commentaire_efficacite'],
                'statut' => 'cloture',
                'date_cloture' => now(),
                'responsable_qualite_id' => Auth::id(),
            ]);

            return back()->with('success', 'Fiche clôturée avec succès');
        }

        $ficheProgres->update([
            'efficacite_actions' => false,
            'commentaire_efficacite' => $validated['commentaire_efficacite'],
            'redemarrage_analyse' => true,
            'statut' => 'analyse_en_cours',
        ]);

        return back()->with('warning', 'Fiche renvoyée en analyse (actions non efficaces)');
    }

    public function uploadPieceJointe(Request $request, FicheProgres $ficheProgres)
    {
        $this->authorize('sav-fiche-progres-edit');

        $request->validate([
            'piece_jointe' => 'required|file|max:204800',
            'type_piece' => 'required|in:photo,document,capture_ecran,autre',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('piece_jointe');
        $path = $file->storeAs('fiches-progres/' . $ficheProgres->id, $file->hashName(), 'public');

        FicheProgresPieceJointe::create([
            'fiche_progres_id' => $ficheProgres->id,
            'filename' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'type' => $request->type_piece,
            'description' => $request->description,
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Pièce jointe ajoutée');
    }

    public function destroy(FicheProgres $ficheProgres)
    {
        $this->authorize('sav-fiche-progres-delete');

        foreach ($ficheProgres->piecesJointes as $pj) {
            Storage::disk('public')->delete($pj->chemin_fichier);
        }

        $ficheProgres->delete();

        return redirect()->route('sav.fiches-progres.index')->with('success', 'Fiche supprimée');
    }
}
