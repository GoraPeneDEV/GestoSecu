<?php

namespace App\Http\Controllers\Paie;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\BulletinPaie;
use App\Services\Payroll\PayrollCalculationService;
use App\Jobs\Payroll\GenerateBulletinsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulletinPaieController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('paie-bulletins-read-any');

        $mois = $request->input('mois', now()->month);
        $annee = $request->input('annee', now()->year);
        $statut = $request->input('statut');

        $query = BulletinPaie::with('employe')->where('mois', $mois)->where('annee', $annee);

        if ($statut) {
            $query->where('statut', $statut);
        }

        $bulletins = $query->orderBy('numero_bulletin')->paginate(50);

        return view('paie.bulletins.index', compact('bulletins', 'mois', 'annee', 'statut'));
    }

    public function show(BulletinPaie $bulletin)
    {
        $user = auth()->user();

        if ($user->can('paie-bulletin-read-any')) {
            // RH peut voir tous les bulletins
        } elseif ($user->can('paie-bulletin-read-own')) {
            if ($bulletin->employe->user?->id !== $user->id) {
                abort(403, 'Accès non autorisé');
            }
        } else {
            abort(403, 'Accès non autorisé');
        }

        $bulletin->load(['employe.paieData', 'lignes' => fn($query) => $query->ordered()]);

        return view('paie.bulletins.show', compact('bulletin'));
    }

    /**
     * Générer les bulletins du mois (en masse, job asynchrone)
     */
    public function generateBatch(Request $request)
    {
        $this->authorize('paie-bulletins-generate');

        $validated = $request->validate([
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020|max:2100',
            'employe_ids' => 'nullable|array',
            'employe_ids.*' => 'exists:employe,id',
        ]);

        GenerateBulletinsJob::dispatch($validated['mois'], $validated['annee'], $validated['employe_ids'] ?? null);

        return response()->json(['success' => true, 'message' => 'Génération des bulletins lancée en arrière-plan']);
    }

    public function generate(Request $request, Employe $employe)
    {
        $this->authorize('paie-bulletins-generate');

        $validated = $request->validate([
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020|max:2100',
        ]);

        try {
            $service = new PayrollCalculationService($validated['annee']);
            $bulletin = $service->calculateBulletin($employe, $validated['mois'], $validated['annee']);

            return response()->json(['success' => true, 'message' => 'Bulletin généré avec succès', 'bulletin_id' => $bulletin->id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    public function validate(BulletinPaie $bulletin)
    {
        $this->authorize('paie-bulletins-validate');

        if ($bulletin->statut !== BulletinPaie::STATUT_BROUILLON) {
            return response()->json(['success' => false, 'message' => 'Seuls les bulletins en brouillon peuvent être validés'], 400);
        }

        $bulletin->valider(auth()->id());

        return response()->json(['success' => true, 'message' => 'Bulletin validé avec succès']);
    }

    public function destroy(BulletinPaie $bulletin)
    {
        $this->authorize('paie-bulletins-delete');

        if ($bulletin->statut !== BulletinPaie::STATUT_BROUILLON) {
            return response()->json(['success' => false, 'message' => 'Seuls les bulletins en brouillon peuvent être supprimés'], 400);
        }

        DB::beginTransaction();

        try {
            $variables = $bulletin->employe->getVariablesPourPeriode($bulletin->mois, $bulletin->annee);
            if ($variables && $variables->verrouillee) {
                $variables->verrouillee = false;
                $variables->save();
            }

            $bulletin->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Bulletin supprimé avec succès']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
}
