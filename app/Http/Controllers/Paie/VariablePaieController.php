<?php

namespace App\Http\Controllers\Paie;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\VariablePaie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VariablePaieController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('paie-variables-read');

        $mois = (int) $request->input('mois', now()->month);
        $annee = (int) $request->input('annee', now()->year);

        $employes = Employe::with(['paieData', 'variablesPaie' => function ($query) use ($mois, $annee) {
            $query->where('mois', $mois)->where('annee', $annee);
        }])
            ->whereHas('paieData', fn($query) => $query->where('actif', true))
            ->orderBy('nom')
            ->get();

        return view('paie.variables.index', compact('employes', 'mois', 'annee'));
    }

    public function store(Request $request)
    {
        $this->authorize('paie-variables-create');

        $validated = $request->validate([
            'employe_id' => 'required|exists:employe,id',
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020|max:2100',
            'jours_travailles' => 'nullable|numeric|min:0|max:31',
            'jours_absence_non_payee' => 'nullable|numeric|min:0|max:31',
            'heures_sup_15' => 'nullable|numeric|min:0|max:200',
            'heures_sup_40' => 'nullable|numeric|min:0|max:200',
            'heures_sup_60' => 'nullable|numeric|min:0|max:200',
            'heures_sup_100' => 'nullable|numeric|min:0|max:200',
            'prime_exceptionnelle' => 'nullable|numeric|min:0',
            'motif_prime_exceptionnelle' => 'nullable|string',
            'retenue_exceptionnelle' => 'nullable|numeric|min:0',
            'motif_retenue_exceptionnelle' => 'nullable|string',
            'montant_acompte' => 'nullable|numeric|min:0',
            'montant_avance' => 'nullable|numeric|min:0',
            'commentaire' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $variable = VariablePaie::updateOrCreate(
                ['employe_id' => $validated['employe_id'], 'mois' => $validated['mois'], 'annee' => $validated['annee']],
                array_merge($validated, ['saisi_par' => auth()->id(), 'date_saisie' => now()])
            );

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Variables enregistrées avec succès', 'data' => $variable]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    public function validate(Request $request)
    {
        $this->authorize('paie-variables-validate');

        $validated = $request->validate([
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020|max:2100',
            'employe_ids' => 'nullable|array',
            'employe_ids.*' => 'exists:employe,id',
        ]);

        DB::beginTransaction();

        try {
            $query = VariablePaie::where('mois', $validated['mois'])->where('annee', $validated['annee'])->where('validee', false);

            if (isset($validated['employe_ids'])) {
                $query->whereIn('employe_id', $validated['employe_ids']);
            }

            $count = $query->update(['validee' => true, 'validee_par' => auth()->id(), 'date_validation' => now()]);

            DB::commit();

            return response()->json(['success' => true, 'message' => "$count variable(s) validée(s) avec succès", 'count' => $count]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        $this->authorize('paie-variables-create');

        $validated = $request->validate([
            'employe_id' => 'required|exists:employe,id',
            'mois' => 'required|integer|min:1|max:12',
            'annee' => 'required|integer|min:2020|max:2100',
        ]);

        $variable = VariablePaie::where('employe_id', $validated['employe_id'])
            ->where('mois', $validated['mois'])
            ->where('annee', $validated['annee'])
            ->first();

        if (!$variable) {
            return response()->json(['success' => false, 'message' => 'Variables non trouvées'], 404);
        }
        if ($variable->verrouillee) {
            return response()->json(['success' => false, 'message' => 'Impossible de supprimer : variables verrouillées'], 403);
        }

        $variable->delete();

        return response()->json(['success' => true, 'message' => 'Variables supprimées avec succès']);
    }
}
