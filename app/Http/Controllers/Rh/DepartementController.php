<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Departement;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    public function index()
    {
        $this->authorize('departement-view');

        $departements = Departement::with(['responsable'])->withTrashed()->orderBy('nom')->get();

        return view('rh.departements.index', compact('departements'));
    }

    public function create()
    {
        $this->authorize('departement-create');

        return view('rh.departements.create');
    }

    public function store(Request $request)
    {
        $this->authorize('departement-create');

        $request->validate([
            'nom' => 'required|string|max:255|unique:departements,nom',
            'responsable_id' => 'nullable|exists:employe,id',
        ]);

        Departement::create($request->only('nom', 'responsable_id'));

        return redirect()->route('departements.index')
            ->with('success', 'Le département a été créé avec succès.');
    }

    public function show(Departement $departement)
    {
        return view('rh.departements.show', compact('departement'));
    }

    public function edit(Departement $departement)
    {
        $this->authorize('departement-update');

        return view('rh.departements.edit', compact('departement'));
    }

    public function update(Request $request, Departement $departement)
    {
        $this->authorize('departement-update');

        $request->validate([
            'nom' => 'required|string|max:255|unique:departements,nom,' . $departement->id,
            'responsable_id' => 'nullable|exists:employe,id',
        ]);

        $departement->update($request->only('nom', 'responsable_id'));

        return redirect()->route('departements.index')
            ->with('success', 'Le département a été mis à jour avec succès.');
    }

    public function destroy(Departement $departement)
    {
        $this->authorize('departement-delete');

        $departement->delete();

        return redirect()->route('departements.index')
            ->with('success', 'Le département a été supprimé avec succès.');
    }

    public function restore($id)
    {
        $this->authorize('departement-delete');

        Departement::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('departements.index')
            ->with('success', 'Le département a été restauré avec succès.');
    }
}
