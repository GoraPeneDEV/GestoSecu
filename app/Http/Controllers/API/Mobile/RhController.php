<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ContratEmploye;
use App\Models\DemandeExplication;
use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * "Ma fiche employé", "Mes contrats", "Mon planning", "Mes demandes
 * d'explication" — toutes en lecture/action limitées à SA PROPRE fiche, sans
 * permission Spatie additionnelle (self-service).
 */
class RhController extends Controller
{
    public function maFiche(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->employe) {
                return response()->json(['success' => false, 'message' => 'Aucun dossier employé associé.'], 404);
            }

            $employe = $user->employe()->with('departement:id,nom')->first();

            return response()->json(['success' => true, 'data' => $employe]);
        } catch (\Exception $e) {
            Log::error('RhController::maFiche', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du chargement.'], 500);
        }
    }

    public function mesContrats(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->id_employe) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $contrats = ContratEmploye::where('id_employe', $user->id_employe)
                ->orderByDesc('date_debut')
                ->get();

            return response()->json(['success' => true, 'data' => $contrats]);
        } catch (\Exception $e) {
            Log::error('RhController::mesContrats', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du chargement.'], 500);
        }
    }

    public function monPlanning(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->id_employe) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $planning = Planning::with('site:id,nom_site')
                ->where('employe_id', $user->id_employe)
                ->where(function ($q) {
                    $q->whereNull('date_fin')->orWhere('date_fin', '>=', now()->subDays(7));
                })
                ->orderBy('date_debut')
                ->get();

            return response()->json(['success' => true, 'data' => $planning]);
        } catch (\Exception $e) {
            Log::error('RhController::monPlanning', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du chargement.'], 500);
        }
    }

    public function mesDemandesExplication(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->id_employe) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $demandes = DemandeExplication::where('employe_id', $user->id_employe)
                ->orderByDesc('created_at')
                ->get();

            return response()->json(['success' => true, 'data' => $demandes]);
        } catch (\Exception $e) {
            Log::error('RhController::mesDemandesExplication', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du chargement.'], 500);
        }
    }

    /**
     * POST /api/mobile/rh/demandes-explication/{demande}/repondre
     */
    public function repondreDemandeExplication(Request $request, DemandeExplication $demande)
    {
        try {
            $user = $request->user();

            if ($demande->employe_id !== $user->id_employe) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier invalide',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $path = $request->file('document')->store('demandes-explications/reponses', 'public');

            $demande->update([
                'reponse_document_path' => $path,
                'date_reponse' => now(),
                'statut' => 'repondue',
            ]);

            return response()->json(['success' => true, 'message' => 'Réponse envoyée.', 'data' => $demande->fresh()]);
        } catch (\Exception $e) {
            Log::error('RhController::repondreDemandeExplication', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'envoi.'], 500);
        }
    }
}
