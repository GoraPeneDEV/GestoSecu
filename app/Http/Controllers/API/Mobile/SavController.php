<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\SAV\FicheProgres;
use App\Models\SAV\FicheProgresAction;
use App\Models\SAV\Intervention;
use App\Models\SAV\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Mobile SAV : mes interventions (compte-rendu terrain), fiches de progrès,
 * maintenances à venir. Réutilise App\Models\SAV\Intervention/FicheProgres/
 * FicheProgresAction/Maintenance sans réimplémenter leur logique (numérotation
 * auto, verrou peutEvaluer(), etc.).
 */
class SavController extends Controller
{
    // ------------------------------------------------------------------
    // Mes interventions assignées (compte-rendu terrain, offline)
    // ------------------------------------------------------------------

    public function mesInterventions(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('sav-intervention-view')) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            $interventions = Intervention::with(['site:id,nom_site', 'contrat:id,numero_contrat', 'maintenance:id,description'])
                ->where('technicien_id', $user->id)
                ->orderByDesc('date_intervention')
                ->get();

            return response()->json(['success' => true, 'data' => $interventions]);
        } catch (\Exception $e) {
            Log::error('SavController::mesInterventions', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du chargement.'], 500);
        }
    }

    /**
     * PUT /api/mobile/sav/interventions/{intervention}
     * Compte-rendu terrain (recommandations + statut). Permission
     * sav-intervention-edit, réservé au technicien assigné.
     */
    public function updateIntervention(Request $request, Intervention $intervention)
    {
        try {
            $user = $request->user();

            if (!$user->can('sav-intervention-edit') || $intervention->technicien_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'recommandations_generales' => 'nullable|string',
                'statut' => 'required|in:brouillon,termine,annule',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $intervention->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Compte-rendu enregistré.',
                'data' => $intervention->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('SavController::updateIntervention', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour.'], 500);
        }
    }

    /**
     * POST /api/mobile/sav/interventions/{intervention}/photos
     */
    public function storePhotos(Request $request, Intervention $intervention)
    {
        try {
            $user = $request->user();

            if (!$user->can('sav-intervention-edit') || $intervention->technicien_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'photos.*' => 'required|image|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier invalide',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $existing = $intervention->photos ?? [];
            foreach ($request->file('photos', []) as $file) {
                $existing[] = $file->store('sav/interventions/' . $intervention->id, 'public');
            }

            $intervention->update(['photos' => $existing]);

            return response()->json([
                'success' => true,
                'message' => count($request->file('photos', [])) . ' photo(s) ajoutée(s).',
                'data' => $intervention->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('SavController::storePhotos', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'envoi des photos.'], 500);
        }
    }

    // ------------------------------------------------------------------
    // Fiches de progrès (profils qualité)
    // ------------------------------------------------------------------

    public function mesFichesProgres(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('sav-fiche-progres-view')) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            $fiches = FicheProgres::with(['client:id,nomClient', 'actions'])
                ->where(function ($q) use ($user) {
                    $q->where('cree_par', $user->id)
                      ->orWhere('pilote_processus_id', $user->id)
                      ->orWhere('responsable_qualite_id', $user->id);
                })
                ->orderByDesc('created_at')
                ->get()
                ->each->append('type_label');

            return response()->json(['success' => true, 'data' => $fiches]);
        } catch (\Exception $e) {
            Log::error('SavController::mesFichesProgres', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du chargement.'], 500);
        }
    }

    public function showFicheProgres(Request $request, FicheProgres $fiche)
    {
        $user = $request->user();

        if (!$user->can('sav-fiche-progres-view')) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $fiche->load(['client:id,nomClient', 'actions', 'piloteProcessus:id,prenom,nom', 'responsableQualite:id,prenom,nom']);
        $fiche->append('type_label');

        return response()->json([
            'success' => true,
            'data' => array_merge($fiche->toArray(), [
                'pourcentage_avancement' => $fiche->pourcentageAvancement(),
                'peut_evaluer' => $fiche->peutEvaluer(),
            ]),
        ]);
    }

    /**
     * PATCH /api/mobile/sav/fiches-progres/{fiche}/analyse
     */
    public function updateAnalyse5M(Request $request, FicheProgres $fiche)
    {
        try {
            $user = $request->user();

            if (!$user->can('sav-fiche-progres-analyse')) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'matiere' => 'nullable|string',
                'main_oeuvre' => 'nullable|string',
                'methode' => 'nullable|string',
                'milieu' => 'nullable|string',
                'materiel' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $fiche->update([
                'analyse_5m' => $validator->validated(),
                'statut' => 'analyse_en_cours',
            ]);

            return response()->json(['success' => true, 'message' => 'Analyse 5M enregistrée.', 'data' => $fiche->fresh()]);
        } catch (\Exception $e) {
            Log::error('SavController::updateAnalyse5M', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour.'], 500);
        }
    }

    /**
     * POST /api/mobile/sav/fiches-progres/{fiche}/actions
     */
    public function storeAction(Request $request, FicheProgres $fiche)
    {
        try {
            $user = $request->user();

            if (!$user->can('sav-fiche-progres-view')) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'description' => 'required|string',
                'date_echeance' => 'nullable|date',
                'responsable_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $action = $fiche->actions()->create(array_merge($validator->validated(), ['statut' => 'a_faire']));

            if ($fiche->statut === 'analyse_en_cours') {
                $fiche->update(['statut' => 'plan_action_etabli']);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Action ajoutée.', 'data' => $action]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SavController::storeAction', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'ajout.'], 500);
        }
    }

    /**
     * PATCH /api/mobile/sav/fiches-progres/actions/{action}/realiser
     */
    public function realiserAction(Request $request, FicheProgresAction $action)
    {
        try {
            $user = $request->user();

            if (!$user->can('sav-fiche-progres-view')) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            DB::beginTransaction();

            $action->update(['statut' => 'realisee', 'date_realisation' => now()]);

            $fiche = $action->ficheProgres;
            if ($fiche->statut === 'plan_action_etabli') {
                $fiche->update(['statut' => 'actions_en_cours']);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Action marquée réalisée.', 'data' => $action->fresh()]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SavController::realiserAction', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur.'], 500);
        }
    }

    /**
     * POST /api/mobile/sav/fiches-progres/{fiche}/evaluer
     * Verrou métier peutEvaluer() respecté (cf. FicheProgres::peutEvaluer()).
     */
    public function evaluer(Request $request, FicheProgres $fiche)
    {
        try {
            $user = $request->user();

            if (!$user->can('sav-fiche-progres-evaluer')) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            if (!$fiche->peutEvaluer()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toutes les actions du plan doivent être réalisées avant évaluation.',
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'efficacite_actions' => 'required|boolean',
                'commentaire_efficacite' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $fiche->update([
                'efficacite_actions' => $request->boolean('efficacite_actions'),
                'commentaire_efficacite' => $request->input('commentaire_efficacite'),
                'statut' => $request->boolean('efficacite_actions') ? 'cloture' : 'evaluation',
                'date_cloture' => $request->boolean('efficacite_actions') ? now() : null,
            ]);

            return response()->json(['success' => true, 'message' => 'Évaluation enregistrée.', 'data' => $fiche->fresh()]);
        } catch (\Exception $e) {
            Log::error('SavController::evaluer', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'évaluation.'], 500);
        }
    }

    // ------------------------------------------------------------------
    // Mes maintenances à venir
    // ------------------------------------------------------------------

    public function mesMaintenances(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('sav-maintenance-view')) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            $maintenances = Maintenance::with(['site:id,nom_site', 'contrat:id,numero_contrat'])
                ->aVenir()
                ->orderBy('date_prevue')
                ->get();

            return response()->json(['success' => true, 'data' => $maintenances]);
        } catch (\Exception $e) {
            Log::error('SavController::mesMaintenances', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du chargement.'], 500);
        }
    }
}
