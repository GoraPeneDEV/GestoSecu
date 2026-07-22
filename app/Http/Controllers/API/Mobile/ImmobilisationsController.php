<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Immobilisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImmobilisationsController extends Controller
{
    /**
     * GET /api/mobile/immobilisations/mes-biens
     * Accès à SES PROPRES biens affectés — aucune permission Spatie
     * additionnelle (self-service).
     */
    public function mesBiens(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->id_employe) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $biens = Immobilisation::with(['categorie:id,libelle', 'site:id,libelle'])
                ->where('employe_id', $user->id_employe)
                ->where('statut', 'affecte')
                ->get();

            return response()->json(['success' => true, 'data' => $biens]);
        } catch (\Exception $e) {
            Log::error('ImmobilisationsController::mesBiens', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du chargement.'], 500);
        }
    }

    /**
     * GET /api/mobile/immobilisations/scan/{token}
     * Permission immobilisations-view (même contrôle que le web), même si
     * le bien scanné n'est pas affecté à l'utilisateur (usage inventaire
     * général, cohérent avec le comportement web).
     */
    public function scan(Request $request, string $token)
    {
        try {
            $user = $request->user();

            if (!$user->can('immobilisations-view')) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            $immobilisation = Immobilisation::where('qr_token', $token)
                ->with(['categorie', 'site', 'emplacement', 'employe'])
                ->first();

            if (!$immobilisation) {
                return response()->json(['success' => false, 'message' => 'Aucun bien associé à ce code.'], 404);
            }

            $immobilisation->mouvements()->create([
                'type_mouvement' => 'inventaire',
                'motif' => 'Scan QR Code via app mobile',
                'created_by' => $user->id,
            ]);

            return response()->json(['success' => true, 'data' => $immobilisation]);
        } catch (\Exception $e) {
            Log::error('ImmobilisationsController::scan', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du scan.'], 500);
        }
    }
}
