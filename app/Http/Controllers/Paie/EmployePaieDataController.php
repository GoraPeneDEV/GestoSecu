<?php

namespace App\Http\Controllers\Paie;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\EmployePaieData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployePaieDataController extends Controller
{
    public function edit(Employe $employe)
    {
        $this->authorize('paie-employe-manage');

        $paieData = $employe->paieData ?? $employe->creerPaieDataSiInexistant();

        return view('paie.employes.edit', compact('employe', 'paieData'));
    }

    public function update(Request $request, Employe $employe)
    {
        $this->authorize('paie-employe-manage');

        $validated = $request->validate([
            'salaire_base' => 'required|numeric|min:0',
            'sursalaire' => 'nullable|numeric|min:0',
            'categorie_professionnelle' => 'nullable|string|max:255',
            'classification' => 'nullable|string',
            'echelon' => 'nullable|integer|min:1',
            'coefficient' => 'nullable|numeric|min:0',
            'nombre_epouses' => 'required|integer|min:0|max:4',
            'nombre_enfants_a_charge' => 'required|integer|min:0|max:10',
            'numero_ipres' => 'nullable|string|unique:employe_paie_data,numero_ipres,' . ($employe->paieData->id ?? 'NULL'),
            'numero_css' => 'nullable|string|unique:employe_paie_data,numero_css,' . ($employe->paieData->id ?? 'NULL'),
            'numero_ipm' => 'nullable|string',
            'numero_contribuable' => 'nullable|string',
            'banque_nom' => 'nullable|string',
            'banque_code' => 'nullable|string|max:5',
            'banque_guichet' => 'nullable|string|max:5',
            'numero_compte' => 'nullable|string',
            'cle_rib' => 'nullable|string|max:2',
            'iban' => 'nullable|string|max:34',
            'domiciliation_bancaire' => 'nullable|string',
            'date_derniere_augmentation' => 'nullable|date',
            'commentaire_paie' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $paieData = $employe->paieData ?? new EmployePaieData(['employe_id' => $employe->id]);
            $paieData->fill($validated);
            $paieData->parts_fiscales = $paieData->calculerPartsFiscales();
            $paieData->save();

            DB::commit();

            return redirect()->route('employes.show', $employe)->with('success', 'Données de paie mises à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    public function show(Employe $employe)
    {
        $this->authorize('paie-employe-read');

        $paieData = $employe->paieData;

        if (!$paieData) {
            return redirect()->route('paie.employes.edit', $employe)->with('warning', 'Aucune donnée de paie configurée pour cet employé');
        }

        return view('paie.employes.show', compact('employe', 'paieData'));
    }
}
