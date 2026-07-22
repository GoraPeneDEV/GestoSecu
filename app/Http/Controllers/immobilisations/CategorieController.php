<?php

namespace App\Http\Controllers\immobilisations;

use App\Http\Controllers\Controller;
use App\Models\ImmobilisationCategorie;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CategorieController extends Controller
{
    public function index()
    {
        $this->authorize('immobilisation-categories-manage');

        return view('immobilisations.categories.index');
    }

    public function data()
    {
        $this->authorize('immobilisation-categories-manage');

        $query = ImmobilisationCategorie::query();

        return DataTables::of($query)
            ->addColumn('est_dotable_badge', function ($cat) {
                return $cat->est_dotable 
                    ? '<span class="badge bg-success">Oui</span>' 
                    : '<span class="badge bg-secondary">Non</span>';
            })
            ->addColumn('est_amortissable_badge', function ($cat) {
                return $cat->est_amortissable 
                    ? '<span class="badge bg-success">Oui</span>' 
                    : '<span class="badge bg-secondary">Non</span>';
            })
            ->addColumn('nb_biens', function ($cat) {
                return $cat->immobilisations()->count();
            })
            ->addColumn('actions', function ($cat) {
                return '<button class="btn btn-sm btn-icon btn-warning" onclick="editCategorie(' . $cat->id . ')">
                    <i class="ti ti-pencil"></i></button>
                    <button class="btn btn-sm btn-icon btn-danger" onclick="deleteCategorie(' . $cat->id . ')">
                    <i class="ti ti-trash"></i></button>';
            })
            ->rawColumns(['est_dotable_badge', 'est_amortissable_badge', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $this->authorize('immobilisation-categories-manage');

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:immobilisation_categories',
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_bien' => 'required|in:corporel,incorporel,financier',
            'est_dotable' => 'boolean',
            'est_amortissable' => 'boolean',
            'methode_amortissement_defaut' => 'required|in:lineaire,degressif,variable',
            'duree_amortissement_defaut' => 'nullable|integer|min:1',
            'taux_amortissement_defaut' => 'nullable|numeric|min:0|max:100',
        ]);

        ImmobilisationCategorie::create($validated);

        return response()->json(['success' => true, 'message' => 'Catégorie créée avec succès.']);
    }

    public function edit(ImmobilisationCategorie $categorie)
    {
        $this->authorize('immobilisation-categories-manage');

        return response()->json($categorie);
    }

    public function update(Request $request, ImmobilisationCategorie $categorie)
    {
        $this->authorize('immobilisation-categories-manage');

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:immobilisation_categories,code,' . $categorie->id,
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_bien' => 'required|in:corporel,incorporel,financier',
            'est_dotable' => 'boolean',
            'est_amortissable' => 'boolean',
            'methode_amortissement_defaut' => 'required|in:lineaire,degressif,variable',
            'duree_amortissement_defaut' => 'nullable|integer|min:1',
            'taux_amortissement_defaut' => 'nullable|numeric|min:0|max:100',
        ]);

        $categorie->update($validated);

        return response()->json(['success' => true, 'message' => 'Catégorie mise à jour avec succès.']);
    }

    public function destroy(ImmobilisationCategorie $categorie)
    {
        $this->authorize('immobilisation-categories-manage');

        if ($categorie->immobilisations()->exists()) {
            return response()->json([
                'success' => false, 
                'message' => 'Impossible de supprimer : des biens sont associés à cette catégorie.'
            ], 422);
        }

        $categorie->delete();

        return response()->json(['success' => true, 'message' => 'Catégorie supprimée avec succès.']);
    }
}
