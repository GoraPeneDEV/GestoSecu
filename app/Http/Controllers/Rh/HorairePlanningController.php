<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\HorairePlanning;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class HorairePlanningController extends Controller
{
    public function index()
    {
        $this->authorize('horaire-planning-view');

        return view('rh.horaires.index');
    }

    public function getHoraires()
    {
        $horaires = HorairePlanning::query();

        return DataTables::of($horaires)
            ->addColumn('heures', function ($horaire) {
                if ($horaire->nombre_heures == 0) {
                    return '<span class="badge bg-secondary">Repos</span>';
                }

                $heureDebut = date('H\hi', strtotime($horaire->heure_debut));
                $heureFin = date('H\hi', strtotime($horaire->heure_fin));

                return '<span class="text-primary">' . $heureDebut . ' - ' . $heureFin . '</span>';
            })
            ->addColumn('actions', fn($horaire) => '<div class="d-flex align-items-center">
                <button type="button" class="btn btn-icon btn-outline-warning me-2 btn-edit-horaire" data-id="' . $horaire->id . '"><i class="ti ti-pencil"></i></button>
                <button type="button" class="btn btn-icon btn-outline-danger btn-delete-horaire" data-id="' . $horaire->id . '"><i class="ti ti-trash"></i></button></div>')
            ->rawColumns(['heures', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('horaire-planning-create');

        return view('rh.horaires.create');
    }

    public function store(Request $request)
    {
        $this->authorize('horaire-planning-create');

        $request->validate([
            'label' => 'required|string|max:255',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i',
        ]);

        try {
            HorairePlanning::create($request->all());
            return redirect()->route('horaires.index')->with('success', 'Horaire créé avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la création de l\'horaire : ' . $e->getMessage());
        }
    }

    public function show(HorairePlanning $horaire)
    {
        $this->authorize('horaire-planning-view');

        return view('rh.horaires.show', compact('horaire'));
    }

    public function edit($id)
    {
        $this->authorize('horaire-planning-update');

        return response()->json(HorairePlanning::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('horaire-planning-update');

        $horaire = HorairePlanning::findOrFail($id);

        $request->validate([
            'label' => 'required|string|max:255',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i',
        ]);

        try {
            $horaire->update($request->all());
            return redirect()->route('horaires.index')->with('success', 'Horaire mis à jour avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erreur lors de la mise à jour de l\'horaire : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize('horaire-planning-delete');

        try {
            $horaire = HorairePlanning::findOrFail($id);

            if ($horaire->detailsPlannings()->exists()) {
                return redirect()->route('horaires.index')->with('error', 'Impossible de supprimer cet horaire car il est utilisé dans des plannings');
            }

            $horaire->delete();
            return redirect()->route('horaires.index')->with('success', 'Horaire supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->route('horaires.index')->with('error', 'Erreur lors de la suppression de l\'horaire : ' . $e->getMessage());
        }
    }
}
