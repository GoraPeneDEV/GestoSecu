<?php

namespace App\Http\Controllers\immobilisations;

use App\Http\Controllers\Controller;
use App\Models\Immobilisation;
use App\Models\ImmobilisationSite;
use App\Models\ImmobilisationCategorie;
use App\Models\ImmobilisationEmplacement;
use App\Models\Employe;
use App\Services\AmortissementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\QRMarkupSVG;

class ImmobilisationController extends Controller
{
    protected $amortissementService;

    public function __construct(AmortissementService $amortissementService)
    {
        $this->amortissementService = $amortissementService;
    }

    public function index()
    {
        $this->authorize('immobilisations-view');

        $stats = [
            'total' => Immobilisation::count(),
            'en_stock' => Immobilisation::where('statut', 'en_stock')->count(),
            'affectes' => Immobilisation::where('statut', 'affecte')->count(),
            'en_reparation' => Immobilisation::where('statut', 'en_reparation')->count(),
        ];

        $sites = ImmobilisationSite::actifs()->get();
        $categories = ImmobilisationCategorie::all();

        return view('immobilisations.biens.index', compact('stats', 'sites', 'categories'));
    }

    public function data(Request $request)
    {
        $this->authorize('immobilisations-view');

        $query = Immobilisation::with(['categorie', 'site', 'employe']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        return DataTables::of($query)
            ->addColumn('categorie_libelle', function ($bien) {
                return $bien->categorie ? $bien->categorie->libelle : '-';
            })
            ->addColumn('site_libelle', function ($bien) {
                return $bien->site ? $bien->site->libelle : '-';
            })
            ->addColumn('detenteur', function ($bien) {
                if ($bien->employe) {
                    return $bien->employe->prenom . ' ' . $bien->employe->nom;
                }
                return '<span class="badge bg-secondary">En stock</span>';
            })
            ->addColumn('valeur_formattee', function ($bien) {
                return number_format($bien->valeur_acquisition, 0, ',', ' ') . ' FCFA';
            })
            ->addColumn('statut_badge', function ($bien) {
                $badges = [
                    'en_stock' => 'bg-secondary',
                    'affecte' => 'bg-success',
                    'en_reparation' => 'bg-warning',
                    'en_transit' => 'bg-info',
                    'cede' => 'bg-dark',
                    'reforme' => 'bg-danger',
                    'perdu' => 'bg-danger',
                ];
                $labels = [
                    'en_stock' => 'En stock',
                    'affecte' => 'Affecté',
                    'en_reparation' => 'En réparation',
                    'en_transit' => 'En transit',
                    'cede' => 'Cédé',
                    'reforme' => 'Réformé',
                    'perdu' => 'Perdu',
                ];
                return '<span class="badge ' . ($badges[$bien->statut] ?? 'bg-secondary') . '">'
                    . ($labels[$bien->statut] ?? $bien->statut) . '</span>';
            })
            ->addColumn('actions', function ($bien) {
                $actions = '<div class="d-inline-flex">';

                $actions .= '<a href="' . route('immobilisations.biens.show', $bien->id) . '"
                    class="btn btn-sm btn-icon btn-info me-1" title="Voir">
                    <i class="ti ti-eye"></i></a>';

                if (auth()->user()->can('immobilisations-edit')) {
                    $actions .= '<a href="' . route('immobilisations.biens.edit', $bien->id) . '"
                        class="btn btn-sm btn-icon btn-warning me-1" title="Modifier">
                        <i class="ti ti-pencil"></i></a>';
                }

                $actions .= '<a href="' . route('immobilisations.biens.qrcode', $bien->id) . '"
                    class="btn btn-sm btn-icon btn-secondary me-1" title="QR Code" target="_blank">
                    <i class="ti ti-qrcode"></i></a>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['detenteur', 'statut_badge', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('immobilisations-create');

        $sites = ImmobilisationSite::actifs()->get();
        $categories = ImmobilisationCategorie::all();
        $employes = Employe::orderBy('nom')->orderBy('prenom')
            ->select('id', 'nom', 'prenom', 'matricule', 'fonction')
            ->get();

        return view('immobilisations.biens.create', compact('sites', 'categories', 'employes'));
    }

    /**
     * Retourne le prochain code interne pour une catégorie donnée (pour preview AJAX)
     */
    public function previewCode(Request $request)
    {
        $categorieId = $request->integer('categorie_id');
        $code = Immobilisation::genererCode($categorieId ?: null);
        return response()->json(['code' => $code]);
    }

    public function store(Request $request)
    {
        $this->authorize('immobilisations-create');

        $validated = $request->validate([
            'designation' => 'required|string|max:255',
            'description' => 'nullable|string',
            'numero_serie' => 'nullable|string|max:255',
            'categorie_id' => 'required|exists:immobilisation_categories,id',
            'site_id' => 'required|exists:immobilisation_sites,id',
            'emplacement_id' => 'nullable|exists:immobilisation_emplacements,id',
            'date_acquisition' => 'required|date',
            'valeur_acquisition' => 'required|numeric|min:0',
            'numero_facture' => 'nullable|string|max:255',
            'methode_amortissement' => 'required|in:lineaire,degressif,variable',
            'duree_amortissement_annees' => 'required|integer|min:1',
            'taux_amortissement' => 'nullable|numeric|min:0|max:100',
            'date_debut_amortissement' => 'nullable|date',
            'valeur_residuelle' => 'nullable|numeric|min:0',
            'quantite' => 'required|integer|min:1|max:50',
            'action_apres' => 'required|in:liste,nouveau',
            'employe_id' => 'nullable|exists:employe,id',
            'date_affectation' => 'nullable|date',
        ]);

        $quantite = $validated['quantite'];
        $actionApres = $validated['action_apres'];
        $employeId = $validated['employe_id'] ?? null;
        $dateAffectation = $validated['date_affectation'] ?? now()->toDateString();
        unset($validated['quantite'], $validated['action_apres'], $validated['employe_id'], $validated['date_affectation']);

        DB::beginTransaction();

        try {
            for ($i = 0; $i < $quantite; $i++) {
                $statut = $employeId ? 'affecte' : 'en_stock';

                $immobilisation = Immobilisation::create([
                    ...$validated,
                    'statut' => $statut,
                    'employe_id' => $employeId,
                    'date_affectation' => $employeId ? $dateAffectation : null,
                    'created_by' => Auth::id(),
                ]);

                if ($immobilisation->est_amortissable) {
                    $this->amortissementService->genererLignes($immobilisation);
                }
            }

            DB::commit();

            $message = $quantite > 1
                ? "{$quantite} immobilisations créées avec succès."
                : "Immobilisation créée avec succès.";

            if ($actionApres === 'nouveau') {
                return redirect()->route('immobilisations.biens.create')
                    ->with('success', $message)
                    ->withInput($request->except(['numero_serie', 'quantite']));
            }

            return redirect()->route('immobilisations.biens.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage())->withInput();
        }
    }

    public function show(Immobilisation $bien)
    {
        $this->authorize('immobilisations-view');

        $bien->load(['categorie', 'site', 'emplacement', 'employe', 'affectations', 'mouvements', 'amortissementLignes']);

        $options = new QROptions(['outputType' => QRMarkupSVG::class, 'svgViewBoxSize' => 200]);
        $qrCode = (new QRCode($options))->render(route('immobilisations.scan', $bien->qr_token));

        $employes = Employe::orderBy('nom')->orderBy('prenom')
            ->select('id', 'nom', 'prenom', 'matricule')
            ->get();

        return view('immobilisations.biens.show', compact('bien', 'qrCode', 'employes'));
    }

    public function edit(Immobilisation $bien)
    {
        $this->authorize('immobilisations-edit');

        $sites = ImmobilisationSite::actifs()->get();
        $categories = ImmobilisationCategorie::all();
        $emplacements = ImmobilisationEmplacement::where('site_id', $bien->site_id)->actifs()->get();

        return view('immobilisations.biens.edit', compact('bien', 'sites', 'categories', 'emplacements'));
    }

    public function update(Request $request, Immobilisation $bien)
    {
        $this->authorize('immobilisations-edit');

        $validated = $request->validate([
            'designation' => 'required|string|max:255',
            'description' => 'nullable|string',
            'numero_serie' => 'nullable|string|max:255',
            'categorie_id' => 'required|exists:immobilisation_categories,id',
            'site_id' => 'required|exists:immobilisation_sites,id',
            'emplacement_id' => 'nullable|exists:immobilisation_emplacements,id',
            'date_acquisition' => 'required|date',
            'valeur_acquisition' => 'required|numeric|min:0',
            'numero_facture' => 'nullable|string|max:255',
            'methode_amortissement' => 'required|in:lineaire,degressif,variable',
            'duree_amortissement_annees' => 'required|integer|min:1',
            'taux_amortissement' => 'nullable|numeric|min:0|max:100',
            'date_debut_amortissement' => 'nullable|date',
            'valeur_residuelle' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $bien->update($validated);

            if ($bien->est_amortissable) {
                $this->amortissementService->genererLignes($bien);
            }

            DB::commit();

            return redirect()->route('immobilisations.biens.index')
                ->with('success', 'Immobilisation mise à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Immobilisation $bien)
    {
        $this->authorize('immobilisations-delete');

        DB::beginTransaction();

        try {
            if ($bien->affectations()->enCours()->exists()) {
                return back()->with('error', 'Impossible de supprimer : le bien est actuellement affecté.');
            }

            $bien->amortissementLignes()->delete();
            $bien->affectations()->delete();
            $bien->mouvements()->delete();
            $bien->delete();

            DB::commit();

            return redirect()->route('immobilisations.biens.index')
                ->with('success', 'Immobilisation supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    public function qrcode(Immobilisation $bien)
    {
        $this->authorize('immobilisations-view');

        $url = route('immobilisations.scan', $bien->qr_token);

        return view('immobilisations.biens.qrcode', compact('bien', 'url'));
    }

    public function amortissement(Immobilisation $bien)
    {
        $this->authorize('immobilisations-view');

        $lignes = $bien->amortissementLignes()->orderBy('annee_exercice')->get();

        return view('immobilisations.biens.amortissement', compact('bien', 'lignes'));
    }

    public function recalculerAmortissement(Immobilisation $bien)
    {
        $this->authorize('immobilisations-edit');

        $this->amortissementService->genererLignes($bien);

        return back()->with('success', 'Amortissement recalculé avec succès.');
    }
}
