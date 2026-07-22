<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BulletinPaie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * "Mon bulletin de paie" (lecture seule, read-own).
 */
class PaieController extends Controller
{
    public function mesBulletins(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('paie-bulletins-read-own')) {
                return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
            }

            if (!$user->id_employe) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $bulletins = BulletinPaie::where('employe_id', $user->id_employe)
                ->where('statut', '!=', BulletinPaie::STATUT_BROUILLON)
                ->orderByDesc('annee')
                ->orderByDesc('mois')
                ->get(['id', 'mois', 'annee', 'numero_bulletin', 'salaire_net_a_payer', 'statut']);

            return response()->json(['success' => true, 'data' => $bulletins]);
        } catch (\Exception $e) {
            Log::error('PaieController::mesBulletins', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du chargement.'], 500);
        }
    }
}
