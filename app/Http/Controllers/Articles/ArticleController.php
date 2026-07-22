<?php

namespace App\Http\Controllers\Articles;

use App\Models\Article;
use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ImmobilisationCategorie;

class ArticleController extends Controller
{
  public function index()
  {
    $this->authorize('article-view');
    $stats = [
      'totalArticles' => Article::count(),
      'articlesSousStock' => Article::whereRaw('stock_actuel <= stock_minimum')->count(),
      'valeurTotale' => Article::selectRaw('SUM(stock_actuel * prix_unitaire) as valeur')->value('valeur') ?? 0
    ];

    $departements = Departement::all();
    $immobilisationCategories = ImmobilisationCategorie::where('est_dotable', true)->get();

    return view('articles.index', compact('stats', 'departements', 'immobilisationCategories'));
  }

  public function getArticles(Request $request)
  {
    $query = Article::with('departement');

    // Application des filtres
    if ($request->has('departement_id') && $request->departement_id) {
      $query->where('departement_id', $request->departement_id);
    }

    if ($request->has('stock_status')) {
      switch ($request->stock_status) {
        case 'under_min':
          $query->whereRaw('stock_actuel <= stock_minimum');
          break;
        case 'out_of_stock':
          $query->where('stock_actuel', 0);
          break;
        case 'in_stock':
          $query->where('stock_actuel', '>', 0);
          break;
      }
    }

    return DataTables::of($query)
      ->addColumn('stock_status', function ($article) {
        if ($article->stock_actuel === 0) {
          return '<span class="badge bg-label-danger">Rupture</span>';
        } elseif ($article->stock_actuel <= $article->stock_minimum) {
          return '<span class="badge bg-label-warning">Sous minimum</span>';
        } else {
          return '<span class="badge bg-label-success">En stock</span>';
        }
      })
      ->addColumn('valeur_stock', function ($article) {
        return number_format($article->stock_actuel * $article->prix_unitaire, 0, ',', ' ') . ' FCFA';
      })
      // Modifiez la fonction addColumn('actions') dans le controleur ArticleController
      ->addColumn('actions', function ($article) {
        return '
      <div class="d-inline-block text-nowrap">
          <button class="btn btn-sm btn-icon btn-warning"
                  onclick="editArticle(' . $article->id . ')"
                  data-bs-toggle="tooltip"
                  data-bs-original-title="Modifier">
              <i class="ti ti-pencil"></i>
          </button>
      </div>';
      })
      ->editColumn('prix_unitaire', function ($article) {
        return number_format($article->prix_unitaire, 0, ',', ' ') . ' FCFA';
      })
      ->rawColumns(['stock_status', 'actions'])
      ->make(true);
  }

  public function store(Request $request)
  {
    $this->authorize('article-create');
    $request->validate([
      'reference' => 'required|string|max:50|unique:articles',
      'designation' => 'required|string|max:255',
      'description' => 'nullable|string',
      'unite' => 'required|string|max:50',
      'stock_minimum' => 'required|integer|min:0',
      'stock_actuel' => 'required|integer|min:0',
      'prix_unitaire' => 'required|numeric|min:0',
      'departement_id' => 'required|exists:departements,id',
      'est_immobilisable' => 'boolean',
      'immobilisation_categorie_id' => 'nullable|exists:immobilisation_categories,id'
    ]);

    try {
      DB::beginTransaction();

      Article::create(array_merge(
        $request->all(),
        ['created_by' => Auth::id()]
      ));

      DB::commit();
      return response()->json([
        'success' => true,
        'message' => 'Article créé avec succès'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la création de l\'article: ' . $e->getMessage()
      ], 500);
    }
  }

  public function update(Request $request, Article $article)
  {
    $this->authorize('article-update');
    $request->validate([
      'reference' => 'required|string|max:50|unique:articles,reference,' . $article->id,
      'designation' => 'required|string|max:255',
      'description' => 'nullable|string',
      'unite' => 'required|string|max:50',
      'stock_minimum' => 'required|integer|min:0',
      'stock_actuel' => 'required|integer|min:0',
      'prix_unitaire' => 'required|numeric|min:0',
      'departement_id' => 'required|exists:departements,id',
      'est_immobilisable' => 'boolean',
      'immobilisation_categorie_id' => 'nullable|exists:immobilisation_categories,id'
    ]);

    try {
      DB::beginTransaction();

      // Si le stock actuel a changé, créer une entrée dans l'historique
      if ($request->stock_actuel != $article->stock_actuel) {
        $this->logStockMovement(
          $article,
          $article->stock_actuel,
          $request->stock_actuel,
          'Ajustement manuel',
          'Modification directe du stock'
        );
      }

      $article->update($request->all());

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Article mis à jour avec succès',
        'redirect' => route('articles.index')
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour de l\'article: ' . $e->getMessage()
      ], 500);
    }
  }

  public function destroy(Article $article)
  {
    $this->authorize('article-delete');
    try {
      if ($article->stock_actuel > 0) {
        return response()->json([
          'success' => false,
          'message' => 'Impossible de supprimer un article ayant du stock'
        ], 422);
      }

      $article->delete();
      return response()->json([
        'success' => true,
        'message' => 'Article supprimé avec succès'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la suppression de l\'article: ' . $e->getMessage()
      ], 500);
    }
  }

  public function show(Article $article)
  {
    $article->load('departement');
    return response()->json($article);
  }

  protected function logStockMovement($article, $oldQuantity, $newQuantity, $type, $description)
  {
    // Cette méthode sera implémentée plus tard pour gérer l'historique des mouvements
  }



  /**
   * Affiche la page de génération de rapport d'inventaire
   *
   * @return \Illuminate\View\View
   */
  public function inventaire()
  {
    // Récupérer les départements disponibles pour le filtre
    $departements = Departement::all();

    // Année et mois actuels par défaut
    $anneeActuelle = now()->year;
    $moisActuel = now()->month;

    return view('articles.inventaire', compact('departements', 'anneeActuelle', 'moisActuel'));
  }

  /**
   * Génère le rapport d'inventaire pour un mois et une année donnés
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function genererRapportInventaire(Request $request)
  {
    $request->validate([
      'mois' => 'required|integer|between:1,12',
      'annee' => 'required|integer|min:2020|max:' . (now()->year + 1),
      'departement_id' => 'nullable|exists:departements,id'
    ]);

    try {
      $mois = $request->mois;
      $annee = $request->annee;
      $departementId = $request->departement_id;

      // Construction de la requête
      $query = Article::with('departement');

      // Filtre par département si spécifié
      if ($departementId) {
        $query->where('departement_id', $departementId);
      }

      // Récupération des articles
      $articles = $query->orderBy('designation')->get();

      // Génération des données avec observations automatiques
      $donneesInventaire = $articles->map(function ($article) {
        return [
          'reference' => $article->reference,
          'designation' => $article->designation,
          'unite' => $article->unite,
          'stock_actuel' => $article->stock_actuel,
          'stock_minimum' => $article->stock_minimum,
          'prix_unitaire' => $article->prix_unitaire,
          'valeur_stock' => $article->stock_actuel * $article->prix_unitaire,
          'departement' => $article->departement->nom,
          'observation' => $this->genererObservation($article->stock_actuel, $article->stock_minimum),
          'statut_classe' => $this->obtenirClasseStatut($article->stock_actuel, $article->stock_minimum)
        ];
      });

      // Calcul des statistiques globales
      $statistiques = [
        'total_articles' => $articles->count(),
        'valeur_totale' => $donneesInventaire->sum('valeur_stock'),
        'articles_rupture' => $donneesInventaire->where('stock_actuel', 0)->count(),
        'articles_alerte' => $donneesInventaire->where('stock_actuel', '<=', function ($item) {
          return $item['stock_minimum'];
        })->count(),
        'articles_bon_stock' => $donneesInventaire->where('stock_actuel', '>', 20)->count()
      ];

      return response()->json([
        'success' => true,
        'data' => $donneesInventaire,
        'statistiques' => $statistiques,
        'periode' => [
          'mois' => $mois,
          'annee' => $annee,
          'mois_nom' => $this->getNomMois($mois),
          'date_generation' => now()->format('d/m/Y à H:i')
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Génère automatiquement l'observation selon le niveau de stock
   *
   * @param int $stockActuel
   * @param int $stockMinimum
   * @return string
   */
  private function genererObservation($stockActuel, $stockMinimum)
  {
    if ($stockActuel === 0) {
      return "🔴 ALERTE CRITIQUE : Stock épuisé. Réapprovisionnement urgent requis.";
    } elseif ($stockActuel < 10) {
      return "⚠️ ALERTE : Stock très bas (" . $stockActuel . " unités). Réapprovisionnement nécessaire dans les plus brefs délais.";
    } elseif ($stockActuel >= 10 && $stockActuel <= 20) {
      return "⚡ ATTENTION : Stock limité (" . $stockActuel . " unités). Prévoir un réapprovisionnement prochainement.";
    } elseif ($stockActuel > 20 && $stockActuel <= 50) {
      return "✓ Stock acceptable (" . $stockActuel . " unités). Niveau de stock satisfaisant.";
    } elseif ($stockActuel > 50 && $stockActuel <= 100) {
      return "✓✓ Stock suffisant (" . $stockActuel . " unités). Bon niveau de disponibilité.";
    } else { // > 100
      return "✓✓✓ Stock optimal (" . $stockActuel . " unités). Excellente disponibilité.";
    }
  }

  /**
   * Détermine la classe CSS selon le statut du stock
   *
   * @param int $stockActuel
   * @param int $stockMinimum
   * @return string
   */
  private function obtenirClasseStatut($stockActuel, $stockMinimum)
  {
    if ($stockActuel === 0) {
      return 'danger';
    } elseif ($stockActuel < 10) {
      return 'danger';
    } elseif ($stockActuel >= 10 && $stockActuel <= 20) {
      return 'warning';
    } elseif ($stockActuel > 20 && $stockActuel <= 50) {
      return 'info';
    } elseif ($stockActuel > 50 && $stockActuel <= 100) {
      return 'primary';
    } else {
      return 'success';
    }
  }

  /**
   * Retourne le nom du mois en français
   *
   * @param int $mois
   * @return string
   */
  private function getNomMois($mois)
  {
    $moisNoms = [
      1 => 'Janvier',
      2 => 'Février',
      3 => 'Mars',
      4 => 'Avril',
      5 => 'Mai',
      6 => 'Juin',
      7 => 'Juillet',
      8 => 'Août',
      9 => 'Septembre',
      10 => 'Octobre',
      11 => 'Novembre',
      12 => 'Décembre'
    ];

    return $moisNoms[$mois] ?? '';
  }

  /**
   * Exporte le rapport d'inventaire en PDF
   *
   * @param Request $request
   * @return \Illuminate\Http\Response
   */
  public function exporterInventairePDF(Request $request)
  {
    $request->validate([
      'mois' => 'required|integer|between:1,12',
      'annee' => 'required|integer',
      'departement_id' => 'nullable|exists:departements,id'
    ]);

    try {
      // Récupération des données (même logique que genererRapportInventaire)
      $response = $this->genererRapportInventaire($request);
      $data = json_decode($response->getContent(), true);

      if (!$data['success']) {
        return back()->with('error', 'Erreur lors de la génération du rapport');
      }

      $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('articles.inventaire-pdf', [
        'donnees' => $data['data'],
        'statistiques' => $data['statistiques'],
        'periode' => $data['periode']
      ]);

      $filename = 'inventaire_' . $data['periode']['mois_nom'] . '_' . $data['periode']['annee'] . '.pdf';

      return $pdf->download($filename);
    } catch (\Exception $e) {
      return back()->with('error', 'Erreur lors de l\'export PDF: ' . $e->getMessage());
    }
  }







  /**
   * Récupère les données de l'article pour modification (Modal)
   */
  public function edit(Article $article)
  {
    $this->authorize('article-update');
    return response()->json($article);
  }
}
