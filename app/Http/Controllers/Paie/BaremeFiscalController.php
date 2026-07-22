<?php

namespace App\Http\Controllers\Paie;

use App\Http\Controllers\Controller;
use App\Models\BaremeFiscal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BaremeFiscalController extends Controller
{
    /**
     * Afficher la liste des barèmes fiscaux
     */
    public function index()
    {
        $this->authorize('baremes-fiscaux-view');

        $baremes = BaremeFiscal::all();

        return view('paie.baremes.index', compact('baremes'));
    }

    /**
     * Récupérer les barèmes pour Datatables
     */
    public function getBaremes(Request $request)
    {
        $annee = $request->input('annee', now()->year);
        $type = $request->input('type');

        $query = BaremeFiscal::forYear($annee);

        if ($type) {
            $query->ofType($type);
        }

        $baremes = $query->select('bareme_fiscal.*');

        return datatables()->of($baremes)
            ->addColumn('type_label', function ($bareme) {
                $types = [
                    BaremeFiscal::TYPE_IPRES_RG => 'IPRES RG',
                    BaremeFiscal::TYPE_IPRES_CADRE => 'IPRES Cadre',
                    BaremeFiscal::TYPE_CSS => 'CSS',
                    BaremeFiscal::TYPE_IPM => 'IPM',
                    BaremeFiscal::TYPE_IR => 'IR',
                    BaremeFiscal::TYPE_TRIMF => 'TRIMF',
                    BaremeFiscal::TYPE_CFCE => 'CFCE',
                ];
                return '<span class="badge bg-label-primary">' . ($types[$bareme->type] ?? $bareme->type) . '</span>';
            })
            ->addColumn('taux_info', function ($bareme) {
                if (in_array($bareme->type, [BaremeFiscal::TYPE_IPRES_RG, BaremeFiscal::TYPE_IPRES_CADRE, BaremeFiscal::TYPE_CSS, BaremeFiscal::TYPE_IPM])) {
                    return '<span class="badge bg-info">Sal: ' . number_format($bareme->taux_salarial, 2) . '%</span> ' .
                        '<span class="badge bg-success">Pat: ' . number_format($bareme->taux_patronal, 2) . '%</span>';
                } elseif ($bareme->type === BaremeFiscal::TYPE_IR) {
                    return '<span class="badge bg-warning">' . number_format($bareme->taux_ir, 2) . '%</span>';
                } else {
                    return '<span class="badge bg-secondary">' . number_format($bareme->taux_salarial, 2) . '%</span>';
                }
            })
            ->addColumn('plafond_info', function ($bareme) {
                if ($bareme->plafond) {
                    return number_format($bareme->plafond, 0, ',', ' ') . ' FCFA';
                } elseif ($bareme->tranche_min !== null) {
                    return number_format($bareme->tranche_min, 0, ',', ' ') . ' - ' . number_format($bareme->tranche_max, 0, ',', ' ');
                }
                return '-';
            })
            ->editColumn('actif', function ($bareme) {
                return $bareme->actif
                    ? '<span class="badge bg-success">Actif</span>'
                    : '<span class="badge bg-secondary">Inactif</span>';
            })
            ->addColumn('actions', function ($bareme) {
                return '
                    <button class="btn btn-sm btn-outline-warning btn-edit-bareme" data-id="' . $bareme->id . '">
                        <i class="ti ti-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-delete-bareme" data-id="' . $bareme->id . '">
                        <i class="ti ti-trash"></i>
                    </button>
                ';
            })
            ->rawColumns(['type_label', 'taux_info', 'plafond_info', 'actif', 'actions'])
            ->make(true);
    }

    /**
     * Stocker un nouveau barème
     */
    public function store(Request $request)
    {
        $this->authorize('baremes-fiscaux-create');

        try {
            $validated = $this->validateBareme($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Erreur de validation : ' . $e->getMessage());
        }

        DB::beginTransaction();

        try {
            $bareme = BaremeFiscal::create($validated);

            // Invalider le cache
            BaremeFiscal::clearCache($validated['annee']);

            DB::commit();

            return redirect()->route('paie.baremes-fiscaux.index')
                ->with('success', 'Barème fiscal créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Récupérer un barème pour modification
     */
    public function edit(BaremeFiscal $bareme)
    {
        $this->authorize('baremes-fiscaux-edit');

        return response()->json($bareme);
    }

    /**
     * Mettre à jour un barème
     */
    public function update(Request $request, BaremeFiscal $bareme)
    {
        $this->authorize('baremes-fiscaux-edit');

        $validated = $this->validateBareme($request, $bareme->id);

        DB::beginTransaction();

        try {
            $oldAnnee = $bareme->annee;
            $bareme->update($validated);

            // Invalider le cache
            BaremeFiscal::clearCache($oldAnnee);
            if ($validated['annee'] != $oldAnnee) {
                BaremeFiscal::clearCache($validated['annee']);
            }

            DB::commit();

            return redirect()->route('paie.baremes-fiscaux.index')
                ->with('success', 'Barème fiscal mis à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un barème
     */
    public function destroy($baremeId)
    {
        $this->authorize('baremes-fiscaux-delete');

        $bareme = BaremeFiscal::findOrFail($baremeId);

        try {
            $annee = $bareme->annee;
            $bareme->delete();

            // Invalider le cache
            BaremeFiscal::clearCache($annee);

            return redirect()->route('paie.baremes-fiscaux.index')
                ->with('success', 'Barème fiscal supprimé avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Validation des données de barème
     */
    private function validateBareme(Request $request, $baremeId = null)
    {
        $type = $request->input('type');

        $rules = [
            'type' => ['required', 'string', Rule::in([
                BaremeFiscal::TYPE_IPRES_RG,
                BaremeFiscal::TYPE_IPRES_CADRE,
                BaremeFiscal::TYPE_CSS,
                BaremeFiscal::TYPE_IPM,
                BaremeFiscal::TYPE_IR,
                BaremeFiscal::TYPE_TRIMF,
                BaremeFiscal::TYPE_CFCE,
            ])],
            'annee' => 'required|integer|min:2020|max:2100',
            'description' => 'nullable|string',
            'reference_legale' => 'nullable|string|max:255',
        ];

        // Règles spécifiques selon le type
        if (in_array($type, [BaremeFiscal::TYPE_IPRES_RG, BaremeFiscal::TYPE_IPRES_CADRE, BaremeFiscal::TYPE_CSS, BaremeFiscal::TYPE_IPM])) {
            $rules['taux_salarial'] = 'required|numeric|min:0|max:100';
            $rules['taux_patronal'] = 'required|numeric|min:0|max:100';
            $rules['plafond'] = 'nullable|numeric|min:0';
        } elseif ($type === BaremeFiscal::TYPE_IR) {
            $rules['tranche_min'] = 'required|numeric|min:0';
            $rules['tranche_max'] = 'required|numeric|gt:tranche_min';
            $rules['taux_ir'] = 'required|numeric|min:0|max:100';
        } elseif (in_array($type, [BaremeFiscal::TYPE_TRIMF, BaremeFiscal::TYPE_CFCE])) {
            $rules['taux_salarial'] = 'required|numeric|min:0|max:100';
        }

        $validated = $request->validate($rules);

        // Convertir actif en boolean
        $validated['actif'] = $request->has('actif') ? true : false;

        // Vérifier le chevauchement des tranches IR
        if ($type === BaremeFiscal::TYPE_IR) {
            $this->validateTrancheIR($validated, $baremeId);
        }

        return $validated;
    }

    /**
     * Valider qu'il n'y a pas de chevauchement de tranches IR
     */
    private function validateTrancheIR(array $data, $baremeId = null)
    {
        $query = BaremeFiscal::ofType(BaremeFiscal::TYPE_IR)
            ->forYear($data['annee'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('tranche_min', [$data['tranche_min'], $data['tranche_max']])
                    ->orWhereBetween('tranche_max', [$data['tranche_min'], $data['tranche_max']])
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('tranche_min', '<=', $data['tranche_min'])
                            ->where('tranche_max', '>=', $data['tranche_max']);
                    });
            });

        if ($baremeId) {
            $query->where('id', '!=', $baremeId);
        }

        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'tranche_min' => ['Cette tranche chevauche une tranche existante'],
            ]);
        }
    }
}
