<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Dotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * "Mes dotations" — lecture seule des articles dotés à l'employé connecté.
 */
class AchatsController extends Controller
{
    public function mesDotations(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->id_employe) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $dotations = Dotation::with('details.article:id,designation')
                ->where('employe_id', $user->id_employe)
                ->orderByDesc('date_dotation')
                ->get();

            return response()->json(['success' => true, 'data' => $dotations]);
        } catch (\Exception $e) {
            Log::error('AchatsController::mesDotations', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors du chargement.'], 500);
        }
    }
}
