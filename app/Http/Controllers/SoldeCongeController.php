<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\AjustementSoldeConge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SoldeCongeController extends Controller
{
    public function index()
    {
        $this->authorize('conge-admin-solde-adjust');

        $employes = Employe::where('etat', 1)
            ->with('departement')
            ->orderBy('nom')
            ->get();

        return view('conge.soldes.index', compact('employes'));
    }

    public function ajuster(Request $request)
    {
        $this->authorize('conge-admin-solde-adjust');

        $request->validate([
            'employe_id' => 'required|exists:employe,id',
            'type' => 'required|in:ajout,retrait',
            'montant' => 'required|integer|min:1',
            'commentaire' => 'required|string|min:10',
        ]);

        try {
            DB::beginTransaction();

            $employe = Employe::findOrFail($request->employe_id);

            if ($request->type === 'retrait' && $employe->solde_conges < $request->montant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le solde actuel (' . $employe->solde_conges . ' jours) est insuffisant pour ce retrait.',
                ], 422);
            }

            if ($request->type === 'ajout') {
                $employe->solde_conges += $request->montant;
            } else {
                $employe->solde_conges -= $request->montant;
            }
            $employe->save();

            AjustementSoldeConge::create([
                'id_employe' => $employe->id,
                'type' => $request->type,
                'montant' => $request->montant,
                'commentaire' => $request->commentaire,
                'id_user' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solde mis à jour avec succès.',
                'nouveau_solde' => $employe->solde_conges,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function historique(Employe $employe)
    {
        $this->authorize('conge-admin-solde-adjust');

        $ajustements = AjustementSoldeConge::where('id_employe', $employe->id)
            ->with('utilisateur')
            ->orderByDesc('created_at')
            ->get();

        return view('conge.soldes.historique', compact('employe', 'ajustements'));
    }
}
