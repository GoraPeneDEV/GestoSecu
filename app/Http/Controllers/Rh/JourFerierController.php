<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\JourFerier;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class JourFerierController extends Controller
{
    public function index()
    {
        $this->authorize('jour-ferier-view');

        return view('rh.jours_ferier.index');
    }

    public function getJoursFerier(Request $request)
    {
        $data = JourFerier::select(['id', 'date_ferier', 'description']);

        return DataTables::of($data)
            ->editColumn('date_ferier', fn($row) => $row->date_ferier->format('d/m/Y'))
            ->addColumn('actions', function ($row) {
                $btn = '<div class="d-flex">';
                $btn .= '<button type="button" data-id="' . $row->id . '" class="btn btn-sm btn-icon btn-warning btn-edit-jour me-1"><i class="ti ti-pencil"></i></button>';
                $btn .= '<button type="button" data-id="' . $row->id . '" class="btn btn-sm btn-icon btn-danger btn-delete-jour"><i class="ti ti-trash"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('jour-ferier-create');

        return view('rh.jours_ferier.create');
    }

    public function store(Request $request)
    {
        $this->authorize('jour-ferier-create');

        $validated = $request->validate([
            'date_ferier' => 'required|date|unique:jours_ferier,date_ferier',
            'description' => 'nullable|string|max:255',
        ]);

        JourFerier::create($validated);

        return redirect()->route('jours_ferier.index')
            ->with('success', 'Jour férié ajouté avec succès.');
    }

    public function show(JourFerier $jourFerier)
    {
        return view('rh.jours_ferier.show', compact('jourFerier'));
    }

    public function edit(JourFerier $jourFerier)
    {
        $this->authorize('jour-ferier-update');

        return response()->json([
            'id' => $jourFerier->id,
            'date_ferier' => $jourFerier->date_ferier->format('Y-m-d'),
            'description' => $jourFerier->description,
        ]);
    }

    public function update(Request $request, JourFerier $jourFerier)
    {
        $this->authorize('jour-ferier-update');

        $validated = $request->validate([
            'date_ferier' => 'required|date|unique:jours_ferier,date_ferier,' . $jourFerier->id,
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $jourFerier->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Jour férié mis à jour avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(JourFerier $jourFerier)
    {
        $this->authorize('jour-ferier-delete');

        $jourFerier->delete();

        return redirect()->route('jours_ferier.index')
            ->with('success', 'Jour férié supprimé avec succès.');
    }

    public function trashed()
    {
        $joursFerierTrashed = JourFerier::onlyTrashed()->get();

        return view('rh.jours_ferier.trashed', compact('joursFerierTrashed'));
    }

    public function restore($id)
    {
        JourFerier::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('jours_ferier.trashed')
            ->with('success', 'Jour férié restauré avec succès.');
    }

    public function forceDelete($id)
    {
        JourFerier::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('jours_ferier.trashed')
            ->with('success', 'Jour férié supprimé définitivement.');
    }
}
