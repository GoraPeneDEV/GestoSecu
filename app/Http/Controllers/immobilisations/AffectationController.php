<?php

namespace App\Http\Controllers\immobilisations;

use App\Http\Controllers\Controller;
use App\Models\Immobilisation;
use App\Models\ImmobilisationAffectation;
use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AffectationController extends Controller
{
    /**
     * Affecter un bien à un employé
     */
    public function affecter(Request $request, Immobilisation $bien)
    {
        $this->authorize('immobilisations-edit');

        $validated = $request->validate([
            'employe_id' => 'required|exists:employe,id',
            'date_affectation' => 'required|date',
            'date_fin_prevue' => 'nullable|date|after:date_affectation',
            'type_affectation' => 'required|in:dotation,pret,service,gardien,mission',
            'motif' => 'nullable|string',
        ]);

        // Vérifier que le bien n'est pas déjà affecté
        if ($bien->statut === 'affecte' && $bien->employe_id) {
            return back()->with('error', 'Ce bien est déjà affecté. Veuillez d\'abord le retourner.');
        }

        DB::beginTransaction();

        try {
            // Créer l'affectation
            $affectation = $bien->affectations()->create([
                'employe_id' => $validated['employe_id'],
                'date_affectation' => $validated['date_affectation'],
                'date_fin_prevue' => $validated['date_fin_prevue'] ?? null,
                'type_affectation' => $validated['type_affectation'],
                'created_by' => Auth::id(),
            ]);

            // Mettre à jour l'immobilisation
            $bien->update([
                'statut' => 'affecte',
                'employe_id' => $validated['employe_id'],
                'date_affectation' => $validated['date_affectation'],
            ]);

            // Créer le mouvement
            $bien->mouvements()->create([
                'type_mouvement' => 'affectation',
                'nouvel_employe_id' => $validated['employe_id'],
                'motif' => $validated['motif'] ?? 'Affectation',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return back()->with('success', 'Bien affecté avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'affectation : ' . $e->getMessage());
        }
    }

    /**
     * Transférer un bien d'un employé à un autre
     */
    public function transferer(Request $request, Immobilisation $bien)
    {
        $this->authorize('immobilisations-edit');

        $validated = $request->validate([
            'nouvel_employe_id' => 'required|exists:employe,id',
            'date_transfert' => 'required|date',
            'motif' => 'nullable|string',
        ]);

        if ($bien->statut !== 'affecte' || !$bien->employe_id) {
            return back()->with('error', 'Ce bien n\'est pas actuellement affecté.');
        }

        DB::beginTransaction();

        try {
            $ancienEmployeId = $bien->employe_id;

            // Terminer l'affectation actuelle
            $affectationActuelle = $bien->affectation_actuelle;
            if ($affectationActuelle) {
                $affectationActuelle->update([
                    'date_fin_reelle' => $validated['date_transfert'],
                ]);
            }

            // Créer la nouvelle affectation
            $bien->affectations()->create([
                'employe_id' => $validated['nouvel_employe_id'],
                'date_affectation' => $validated['date_transfert'],
                'type_affectation' => 'dotation',
                'created_by' => Auth::id(),
            ]);

            // Mettre à jour l'immobilisation
            $bien->update([
                'employe_id' => $validated['nouvel_employe_id'],
                'date_affectation' => $validated['date_transfert'],
            ]);

            // Créer le mouvement
            $bien->mouvements()->create([
                'type_mouvement' => 'transfert_employe',
                'ancien_employe_id' => $ancienEmployeId,
                'nouvel_employe_id' => $validated['nouvel_employe_id'],
                'motif' => $validated['motif'] ?? 'Transfert',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return back()->with('success', 'Bien transféré avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du transfert : ' . $e->getMessage());
        }
    }

    /**
     * Retourner un bien en stock
     */
    public function retourner(Request $request, Immobilisation $bien)
    {
        $this->authorize('immobilisations-edit');

        $validated = $request->validate([
            'date_retour' => 'required|date',
            'etat_retour' => 'required|in:bon,abime,hors_service,perdu',
            'observation' => 'nullable|string',
        ]);

        if ($bien->statut !== 'affecte') {
            return back()->with('error', 'Ce bien n\'est pas actuellement affecté.');
        }

        DB::beginTransaction();

        try {
            // Terminer l'affectation actuelle
            $affectationActuelle = $bien->affectation_actuelle;
            if ($affectationActuelle) {
                $affectationActuelle->terminer(
                    $validated['date_retour'],
                    $validated['etat_retour'],
                    $validated['observation']
                );
            }

            // Le mouvement et la mise à jour de l'immobilisation sont gérés par la méthode terminer()
            // Mais on ajoute un mouvement spécifique pour le retour
            $bien->mouvements()->create([
                'type_mouvement' => 'retour_stock',
                'ancien_employe_id' => $bien->employe_id,
                'motif' => 'Retour en stock - État: ' . $validated['etat_retour'],
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return back()->with('success', 'Bien retourné en stock avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du retour : ' . $e->getMessage());
        }
    }

    /**
     * Afficher l'historique des affectations
     */
    public function historique(Immobilisation $bien)
    {
        $this->authorize('immobilisations-view');

        $affectations = $bien->affectations()
            ->with(['employe', 'createur'])
            ->orderBy('date_affectation', 'desc')
            ->get();

        return view('immobilisations.biens.historique', compact('bien', 'affectations'));
    }
}
