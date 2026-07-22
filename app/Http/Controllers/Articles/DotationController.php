<?php

namespace App\Http\Controllers\Articles;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\Article;
use App\Models\Employe;
use App\Models\Dotation;
use Illuminate\Http\Request;
use App\Models\DotationDetail;
use App\Models\Immobilisation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ImmobilisationAffectation;
use App\Models\ImmobilisationSite;
use Yajra\DataTables\Facades\DataTables;
use App\Services\AmortissementService;

class DotationController extends Controller
{
    public function index()
    {
        $this->authorize('dotation-view');

        $anneeActuelle = Carbon::now()->year;
        $moisActuel = Carbon::now()->month;

        $annees = Dotation::selectRaw('YEAR(date_dotation) as annee')->distinct()->orderBy('annee', 'desc')->pluck('annee');
        if ($annees->isEmpty()) {
            $annees = collect([$anneeActuelle]);
        }

        $stats = $this->getStatistiques($anneeActuelle, $moisActuel);

        $sites = Site::orderBy('nom_site')->get();
        $employes = Employe::where('etat', 1)->get();

        return view('dotations.index', compact('stats', 'sites', 'employes', 'annees', 'anneeActuelle', 'moisActuel'));
    }

    private function getStatistiques($annee = null, $mois = null)
    {
        $query = Dotation::query();
        $detailsQuery = DotationDetail::query()->join('dotations', 'dotation_details.dotation_id', '=', 'dotations.id');

        if ($annee) {
            $query->whereYear('date_dotation', $annee);
            $detailsQuery->whereYear('dotations.date_dotation', $annee);
        }
        if ($mois) {
            $query->whereMonth('date_dotation', $mois);
            $detailsQuery->whereMonth('dotations.date_dotation', $mois);
        }

        return [
            'totalDotations' => $query->count(),
            'dotationsSites' => $query->clone()->whereNotNull('site_id')->count(),
            'dotationsEmployes' => $query->clone()->whereNotNull('employe_id')->count(),
            'articlesDistribues' => $detailsQuery->sum('dotation_details.quantite'),
        ];
    }

    public function data(Request $request)
    {
        $this->authorize('dotation-view');

        $query = Dotation::with(['site', 'employe', 'details'])->select('dotations.*');

        if ($request->filled('type')) {
            $query->where('type_dotation', $request->type);
        }
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('annee')) {
            $query->whereYear('date_dotation', $request->annee);
        }
        if ($request->filled('mois')) {
            $query->whereMonth('date_dotation', $request->mois);
        }

        return DataTables::of($query)
            ->editColumn('date_dotation', fn($d) => $d->date_dotation->format('d/m/Y'))
            ->addColumn('beneficiaire', function ($dotation) {
                if ($dotation->site_id && $dotation->site) {
                    return '<span class="badge bg-label-primary">' . e($dotation->site->nom_site) . '</span>';
                }
                if ($dotation->employe_id && $dotation->employe) {
                    return '<span class="badge bg-label-warning">' . e($dotation->employe->matricule ?? 'N/A') . ' - ' . e($dotation->employe->prenom . ' ' . $dotation->employe->nom) . '</span>';
                }
                return '<span class="badge bg-label-secondary">Non défini</span>';
            })
            ->addColumn('articles_count', fn($d) => $d->details->count() . ' article(s)')
            ->addColumn('actions', fn($d) => '<div class="d-inline-block">
                <a href="' . route('dotations.show', $d->id) . '" class="btn btn-sm btn-icon btn-primary me-1"><i class="ti ti-eye"></i></a>
                <a href="' . route('dotations.edit', $d->id) . '" class="btn btn-sm btn-icon btn-warning me-1"><i class="ti ti-pencil"></i></a>
                <button type="button" class="btn btn-sm btn-icon btn-danger delete-dotation" data-id="' . $d->id . '"><i class="ti ti-trash"></i></button></div>')
            ->rawColumns(['beneficiaire', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('dotation-create');

        $sites = Site::orderBy('nom_site')->get();
        $employes = Employe::orderBy('matricule')->get();
        $articles = Article::orderBy('designation')->get();

        return view('dotations.create', compact('sites', 'employes', 'articles'));
    }

    public function store(Request $request)
    {
        $this->authorize('dotation-create');

        $rules = [
            'date_dotation' => 'required|date_format:Y-m-d',
            'type_dotation' => 'required|in:INITIALE,RENOUVELLEMENT',
            'cible' => 'required|in:site,employe',
            'motif' => 'nullable|string|max:255',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantite' => 'required|integer|min:1',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:204800',
        ];
        $rules[$request->cible === 'site' ? 'site_id' : 'employe_id'] = $request->cible === 'site' ? 'required|exists:sites,id' : 'required|exists:employe,id';

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $documentPath = null;
            if ($request->hasFile('document')) {
                $documentPath = $request->file('document')->store('dotations', 'public');
            }

            $dotation = Dotation::create([
                'reference' => 'DOT-' . date('YmdHis'),
                'date_dotation' => $validated['date_dotation'],
                'type_dotation' => strtoupper($validated['type_dotation']),
                'site_id' => $request->site_id ?? null,
                'employe_id' => $request->employe_id ?? null,
                'motif' => $validated['motif'] ?? null,
                'document_path' => $documentPath,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['articles'] as $article) {
                $articleModel = Article::with('immobilisationCategorie')->findOrFail($article['article_id']);

                if ($articleModel->stock_actuel < $article['quantite']) {
                    throw new \Exception("Stock insuffisant pour l'article: {$articleModel->designation}");
                }

                $dotationDetail = DotationDetail::create([
                    'dotation_id' => $dotation->id,
                    'article_id' => $article['article_id'],
                    'quantite' => $article['quantite'],
                ]);

                $articleModel->decrement('stock_actuel', $article['quantite']);

                if ($articleModel->est_immobilisable && $articleModel->immobilisation_categorie_id) {
                    $this->creerImmobilisationsFromDotation(
                        $articleModel,
                        $article['quantite'],
                        $dotation,
                        $request->site_id ?? null,
                        $request->employe_id ?? null
                    );
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Dotation créée avec succès']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur lors de la création de la dotation: ' . $e->getMessage()], 500);
        }
    }

    public function show(Dotation $dotation)
    {
        $this->authorize('dotation-view');

        $dotation->load(['site', 'employe', 'details.article', 'createur']);

        return view('dotations.show', compact('dotation'));
    }

    public function edit(Dotation $dotation)
    {
        $this->authorize('dotation-update');

        $sites = Site::orderBy('nom_site')->get();
        $employes = Employe::orderBy('matricule')->get();
        $articles = Article::orderBy('designation')->get();
        $dotation->load('details');

        return view('dotations.edit', compact('dotation', 'sites', 'employes', 'articles'));
    }

    public function update(Request $request, Dotation $dotation)
    {
        $this->authorize('dotation-update');

        $request->validate([
            'date_dotation' => 'required|date',
            'type_dotation' => 'required|in:INITIALE,RENOUVELLEMENT',
            'motif' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:204800',
            'articles' => 'required|array',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantite' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $dotation->date_dotation = $request->date_dotation;
            $dotation->type_dotation = strtoupper($request->type_dotation);
            $dotation->motif = $request->motif;

            if ($request->hasFile('document')) {
                if ($dotation->document_path) {
                    Storage::disk('public')->delete($dotation->document_path);
                }
                $dotation->document_path = $request->file('document')->store('dotations', 'public');
            }

            $dotation->save();

            $dotation->details()->delete();

            foreach ($request->articles as $article) {
                $dotation->details()->create(['article_id' => $article['article_id'], 'quantite' => $article['quantite']]);
            }

            DB::commit();

            return redirect()->route('dotations.index')->with('success', 'La dotation a été mise à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Une erreur est survenue lors de la mise à jour de la dotation : ' . $e->getMessage());
        }
    }

    public function destroy(Dotation $dotation)
    {
        $this->authorize('dotation-delete');

        try {
            if ($dotation->details()->whereNull('date_retour')->exists()) {
                return response()->json(['success' => false, 'message' => 'Impossible de supprimer la dotation car certains articles n\'ont pas été retournés.'], 422);
            }

            if ($dotation->document_path) {
                Storage::disk('public')->delete($dotation->document_path);
            }

            $dotation->details()->delete();
            $dotation->delete();

            return response()->json(['success' => true, 'message' => 'La dotation a été supprimée avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de la suppression de la dotation : ' . $e->getMessage()], 500);
        }
    }

    public function returnDetail(Request $request)
    {
        $this->authorize('dotation-update');

        $request->validate([
            'detail_id' => 'required|exists:dotation_details,id',
            'statut_retour' => 'required|in:recyclable,non_recyclable',
            'observation' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $detail = DotationDetail::with('article')->findOrFail($request->detail_id);

            if ($detail->is_returned) {
                throw new \Exception('Cet article a déjà été retourné.');
            }

            $detail->update([
                'is_returned' => true,
                'date_retour' => now(),
                'statut_retour' => $request->statut_retour,
                'observation' => $request->observation,
            ]);

            if ($request->statut_retour === 'recyclable') {
                $detail->article->increment('stock_actuel', $detail->quantite);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Article retourné avec succès']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function publicIndex()
    {
        return view('dotations.public');
    }

    public function publicData()
    {
        $user = Auth::user();
        if (!$user->employe) {
            return response()->json(['data' => []]);
        }

        $dotations = Dotation::with(['details.article', 'site'])
            ->where('employe_id', $user->employe->id)
            ->orderBy('date_dotation', 'desc')
            ->get();

        return DataTables::of($dotations)
            ->editColumn('date_dotation', fn($d) => $d->date_dotation->format('d/m/Y'))
            ->addColumn('articles', fn($d) => $d->details->map(fn($det) => $det->quantite . 'x ' . $det->article->designation)->implode('<br>'))
            ->rawColumns(['articles'])
            ->make(true);
    }

    /**
     * Crée une immobilisation par unité dotée si l'article est immobilisable,
     * avec affectation à l'employé et calcul de l'amortissement si applicable.
     */
    private function creerImmobilisationsFromDotation(Article $article, int $quantite, Dotation $dotation, ?int $siteId, ?int $employeId): void
    {
        $categorie = $article->immobilisationCategorie;
        if (!$categorie) {
            return;
        }

        $siteAffectation = ImmobilisationSite::first();
        if (!$siteAffectation) {
            Log::warning('Aucun ImmobilisationSite disponible pour créer les immobilisations de la dotation ' . $dotation->id);
            return;
        }

        for ($i = 0; $i < $quantite; $i++) {
            $immobilisation = Immobilisation::create([
                'designation' => $article->designation,
                'description' => $article->description,
                'categorie_id' => $categorie->id,
                'site_id' => $siteAffectation->id,
                'date_acquisition' => $dotation->date_dotation,
                'valeur_acquisition' => $article->prix_unitaire ?? 0,
                'article_id' => $article->id,
                'statut' => $employeId ? 'affecte' : 'en_stock',
                'employe_id' => $employeId,
                'date_affectation' => $employeId ? $dotation->date_dotation : null,
                'methode_amortissement' => $categorie->methode_amortissement_defaut ?? 'lineaire',
                'duree_amortissement_annees' => $categorie->duree_amortissement_defaut ?? 3,
                'taux_amortissement' => $categorie->taux_calcule,
                'date_debut_amortissement' => $dotation->date_dotation,
                'valeur_residuelle' => 0,
                'created_by' => Auth::id(),
            ]);

            if ($employeId) {
                ImmobilisationAffectation::create([
                    'immobilisation_id' => $immobilisation->id,
                    'employe_id' => $employeId,
                    'date_affectation' => $dotation->date_dotation,
                    'type_affectation' => 'dotation',
                    'dotation_id' => $dotation->id,
                    'created_by' => Auth::id(),
                ]);
            }

            if ($categorie->est_amortissable) {
                (new AmortissementService())->genererLignes($immobilisation);
            }
        }
    }
}
