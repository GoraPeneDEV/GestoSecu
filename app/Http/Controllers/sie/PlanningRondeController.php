<?php

namespace App\Http\Controllers\sie;

use Carbon\Carbon;
use App\Models\Site;
use App\Models\PlanningRonde;
use Illuminate\Http\Request;
use App\Models\PointControle;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\{DB, Log};

class PlanningRondeController extends Controller
{
  public function index()
  {
    $this->authorize('planning-ronde-view');
    // Statistiques pour le dashboard
    $stats = [
      'total' => PlanningRonde::count(),
      'quotidiens' => PlanningRonde::where('frequence', 'quotidienne')->count(),
      'hebdomadaires' => PlanningRonde::where('frequence', 'hebdomadaire')->count(),
      'mensuels' => PlanningRonde::where('frequence', 'mensuelle')->count()
    ];

    $sites = Site::orderBy('nom_site')->get();

    return view('sie.plannings-ronde.index', compact('stats', 'sites'));
  }

  public function create()
  {
    $this->authorize('planning-ronde-create');
    $sites = Site::orderBy('nom_site')->get();
    // Nous ne chargeons plus tous les points de contrôle initialement
    // Les points seront chargés via AJAX quand un site sera sélectionné

    return view('sie.plannings-ronde.create', compact('sites'));
  }

  public function getPointsControleBySite($siteId)
  {
    $points = PointControle::where('site_id', $siteId)
      ->where('actif', true)
      ->orderBy('ordre')
      ->get();

    return response()->json($points);
  }


  public function store(Request $request)
  {
    try {
      DB::beginTransaction();

      $validated = $request->validate([
        'nom' => 'required|string|max:255',
        'site_id' => 'required|exists:sites,id',
        'frequence' => 'required|in:quotidienne,hebdomadaire,mensuelle',
        'heure_debut' => 'required',  // On simplifie la validation pour le champ time
        'duree_estimee' => 'required|integer|min:1',
        'points_controle' => 'required|array|min:1',
        'points_controle.*' => 'exists:point_controles,id'  // Correction du nom de la table
      ]);

      // Création du planning
      $planning = PlanningRonde::create([
        'nom' => $validated['nom'],
        'site_id' => $validated['site_id'],
        'frequence' => $validated['frequence'],
        'heure_debut' => $validated['heure_debut'],
        'duree_estimee' => $validated['duree_estimee']
      ]);

      // Association des points de contrôle avec leur ordre
      foreach ($request->points_controle as $ordre => $pointId) {
        $planning->pointsControle()->attach($pointId, [
          'ordre' => $ordre + 1,
          'created_at' => now(),
          'updated_at' => now()
        ]);
      }

      DB::commit();

      // Correction de la route de redirection
      return redirect()->route('sie.plannings-ronde.index')
        ->with('success', 'Planning de ronde créé avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur création planning: ' . $e->getMessage());
      return back()
        ->withInput()
        ->with('error', 'Erreur lors de la création du planning : ' . $e->getMessage());
    }
  }

  public function getPlannings(Request $request)
  {
    try {
      $query = PlanningRonde::with(['site', 'pointsControle'])
        ->select('plannings_ronde.*');

      // Application des filtres
      if ($request->filled('site_id')) {
        $query->where('site_id', $request->site_id);
      }

      if ($request->filled('frequence')) {
        $query->where('frequence', $request->frequence);
      }

      return DataTables::of($query)
        ->addColumn('points_count', function ($planning) {
          return $planning->pointsControle->count();
        })
        ->editColumn('heure_debut', function ($planning) {
          return Carbon::parse($planning->heure_debut)->format('H:i');
        })
        ->orderColumn('heure_debut', function ($query, $order) {
          $query->orderBy('heure_debut', $order);
        })
        ->toJson();
    } catch (\Exception $e) {
      Log::error('Erreur lors du chargement des plannings : ' . $e->getMessage());
      return response()->json([
        'error' => true,
        'message' => 'Erreur lors du chargement des données'
      ], 500);
    }
  }

  public function show(PlanningRonde $planningRonde)
  {
    // Charger les relations nécessaires
    $planningRonde->load(['site', 'pointsControle']);

    return view('sie.plannings-ronde.show', compact('planningRonde'));
  }

  public function edit(PlanningRonde $planningRonde)
  {
    $this->authorize('planning-ronde-update');
    // Charger le planning avec ses relations
    $planningRonde->load(['site', 'pointsControle']);

    // Charger les sites pour le select
    $sites = Site::orderBy('nom_site')->get();

    return view('sie.plannings-ronde.edit', compact('planningRonde', 'sites'));
  }

  public function update(Request $request, PlanningRonde $planningRonde)
  {
    try {
      DB::beginTransaction();

      $validated = $request->validate([
        'nom' => 'required|string|max:255',
        'site_id' => 'required|exists:sites,id',
        'frequence' => 'required|in:quotidienne,hebdomadaire,mensuelle',
        'heure_debut' => 'required',
        'duree_estimee' => 'required|integer|min:1',
        'points_controle' => 'required|array|min:1',
        'points_controle.*' => 'exists:point_controles,id'
      ]);

      // Mise à jour des informations du planning
      $planningRonde->update([
        'nom' => $validated['nom'],
        'site_id' => $validated['site_id'],
        'frequence' => $validated['frequence'],
        'heure_debut' => $validated['heure_debut'],
        'duree_estimee' => $validated['duree_estimee']
      ]);

      // Mise à jour des points de contrôle
      $planningRonde->pointsControle()->detach(); // Supprimer les anciennes associations
      foreach ($request->points_controle as $ordre => $pointId) {
        $planningRonde->pointsControle()->attach($pointId, [
          'ordre' => $ordre + 1,
          'created_at' => now(),
          'updated_at' => now()
        ]);
      }

      DB::commit();

      return redirect()->route('sie.plannings-ronde.index')
        ->with('success', 'Planning de ronde modifié avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Erreur modification planning: ' . $e->getMessage());
      return back()
        ->withInput()
        ->with('error', 'Erreur lors de la modification du planning : ' . $e->getMessage());
    }
  }

  /**
   * Supprime le planning de ronde
   */
  public function destroy(PlanningRonde $planningRonde)
  {
    $this->authorize('planning-ronde-delete');
    try {
      DB::beginTransaction();
      $planningRonde->pointsControle()->detach();
      $planningRonde->delete();
      DB::commit();

      return redirect()->back()->with('success', 'Planning de ronde supprimé avec succès');
    } catch (\Exception $e) {
      DB::rollBack();
      return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
    }
  }
}
