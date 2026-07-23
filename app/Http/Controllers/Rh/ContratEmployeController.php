<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Employe;
use App\Models\Departement;
use Illuminate\Http\Request;
use App\Models\ContratEmploye;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ContratEmployeController extends Controller
{
    public function index()
    {
        $this->authorize('contrat-view');

        $stats = [
            'totalCDI' => ContratEmploye::where('type_contrat', 'CDI')->where('etat', 1)->count(),
            'totalCDD' => ContratEmploye::where('type_contrat', 'CDD')->where('etat', 1)
                ->where(fn($q) => $q->whereNull('date_fin')->orWhere('date_fin', '>=', now()))->count(),
            'totalStage' => ContratEmploye::where('type_contrat', 'Stage')->where('etat', 1)
                ->where(fn($q) => $q->whereNull('date_fin')->orWhere('date_fin', '>=', now()))->count(),
            'totalPrestationService' => ContratEmploye::where('type_contrat', 'Prestation de service')->where('etat', 1)
                ->where(fn($q) => $q->whereNull('date_fin')->orWhere('date_fin', '>=', now()))->count(),
        ];

        $departements = Departement::orderBy('nom')->get();

        return view('rh.contrats.index', array_merge($stats, ['departements' => $departements]));
    }

    public function getContrats(Request $request)
    {
        $this->authorize('contrat-view');

        $query = ContratEmploye::with(['employe.departement', 'user']);

        if ($request->filled('type_contrat')) {
            $query->where('type_contrat', $request->type_contrat);
        }
        if ($request->filled('departement')) {
            $query->whereHas('employe.departement', fn($q) => $q->where('id', $request->departement));
        }
        if ($request->filled('annee')) {
            $query->whereYear('date_fin', $request->annee);
        }
        if ($request->filled('mois')) {
            $query->whereMonth('date_fin', $request->mois);
        }
        if ($request->filled('statut') && $request->statut === 'echus') {
            $query->where(fn($q) => $q->where(fn($sq) => $sq->whereNotNull('date_fin')->where('date_fin', '<', now()))->orWhere('etat', 0));
        } else {
            $query->where(fn($q) => $q->where('date_fin', '>=', now())->orWhereNull('date_fin'));
        }

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->filled('search.value')) {
                    $searchValue = $request->search['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->whereHas('employe', fn($sq) => $sq->where(DB::raw("CONCAT(prenom, ' ', nom)"), 'LIKE', "%{$searchValue}%"))
                            ->orWhere('type_contrat', 'LIKE', "%{$searchValue}%");
                    });
                }
            })
            ->addColumn('employe_info', fn($contrat) => $contrat->employe ? $contrat->employe->prenom . ' ' . $contrat->employe->nom . ' (' . ($contrat->employe->matricule ?? 'N/A') . ')' : 'Employé non disponible')
            ->addColumn('montant_format', fn($contrat) => number_format($contrat->montant, 0, ',', ' ') . ' FCFA')
            ->editColumn('date_debut', fn($contrat) => $contrat->date_debut->format('d/m/Y'))
            ->editColumn('date_fin', fn($contrat) => $contrat->date_fin ? $contrat->date_fin->format('d/m/Y') : 'N/A')
            ->addColumn('document_link', fn($contrat) => $contrat->document
                ? '<a href="' . asset('storage/' . $contrat->document) . '" target="_blank" class="btn btn-sm btn-icon"><i class="ti ti-file-text text-primary"></i></a>'
                : '-')
            ->addColumn('actions', fn($contrat) => '<div class="d-flex align-items-center">
                <a href="' . route('contrats.edit', ['employe' => $contrat->id_employe, 'contrat' => $contrat->id]) . '" class="btn btn-icon btn-label-primary me-1"><i class="ti ti-pencil"></i></a>
                <button type="button" class="btn btn-icon btn-label-danger delete-contrat" data-id="' . $contrat->id . '" data-employe="' . $contrat->id_employe . '"><i class="ti ti-trash"></i></button></div>')
            ->rawColumns(['document_link', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('contrat-create');

        $employes = Employe::where('etat', 1)->orderBy('prenom')->orderBy('nom')->get();

        return view('rh.contrats.create', compact('employes'));
    }

    public function store(Request $request)
    {
        $this->authorize('contrat-create');

        $rules = [
            'id_employe' => 'required|exists:employe,id',
            'type_contrat' => 'required|in:CDI,CDD,Stage,Prestation de service',
            'date_debut' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'motif' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf|max:204800',
        ];
        $rules['date_fin'] = $request->type_contrat !== 'CDI' ? 'required|date|after:date_debut' : 'nullable|date|after:date_debut';

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $contratActif = ContratEmploye::where('id_employe', $validated['id_employe'])->where('etat', 1)->first();
            if ($contratActif) {
                $contratActif->update(['etat' => 0, 'date_fin' => Carbon::parse($validated['date_debut'])->subDay()->format('Y-m-d')]);
            }

            $contrat = new ContratEmploye();
            $contrat->id_employe = $validated['id_employe'];
            $contrat->type_contrat = $validated['type_contrat'];
            $contrat->date_debut = $validated['date_debut'];
            if ($validated['type_contrat'] !== 'CDI' && !empty($validated['date_fin'])) {
                $contrat->date_prevu_fin = $validated['date_fin'];
            }
            $contrat->motif = $validated['motif'] ?? null;
            $contrat->montant = $validated['montant'];

            if ($request->hasFile('document')) {
                $contrat->document = $this->storeContratDocument($request->file('document'));
            }

            $contrat->id_user = Auth::id();
            $contrat->etat = 1;
            $contrat->created_at = now();
            $contrat->save();

            DB::commit();

            return redirect()->route('contrats.index')->with('success', 'Le contrat a été créé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la création du contrat : ' . $e->getMessage());
        }
    }

    public function edit(Employe $employe, ContratEmploye $contrat)
    {
        $this->authorize('contrat-update');

        if ($contrat->id_employe !== $employe->id) {
            abort(403);
        }

        return view('rh.contrats.edit', compact('employe', 'contrat'));
    }

    public function update(Request $request, Employe $employe, ContratEmploye $contrat)
    {
        $this->authorize('contrat-update');

        if ($contrat->id_employe !== $employe->id) {
            abort(403);
        }

        $rules = [
            'type_contrat' => 'required|in:CDI,CDD,Stage,Prestation de service',
            'date_debut' => 'required|date',
            'montant' => 'required|numeric|min:0',
            'motif' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf|max:204800',
        ];
        $rules['date_fin'] = $request->type_contrat !== 'CDI' ? 'required|date|after:date_debut' : 'nullable|date|after:date_debut';

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $updateData = [
                'type_contrat' => $validated['type_contrat'],
                'date_debut' => $validated['date_debut'],
                'montant' => $validated['montant'],
                'motif' => $validated['motif'] ?? null,
                'date_prevu_fin' => ($validated['type_contrat'] !== 'CDI' && !empty($validated['date_fin'])) ? $validated['date_fin'] : null,
            ];

            if ($request->hasFile('document')) {
                if ($contrat->document) {
                    $this->deleteContratDocument($contrat->document);
                }
                $updateData['document'] = $this->storeContratDocument($request->file('document'));
            }

            $contrat->update($updateData);

            DB::commit();

            return redirect()->route('contrats.index')->with('success', 'Le contrat a été mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour du contrat : ' . $e->getMessage());
        }
    }

    public function getPreviousContrat(Request $request)
    {
        $this->authorize('contrat-view');

        $previousContrat = ContratEmploye::where('id_employe', $request->input('id_employe'))
            ->where(fn($q) => $q->where('date_fin', '<', now())->orWhereNull('date_fin'))
            ->orderByDesc('date_debut')
            ->first();

        if ($previousContrat) {
            $previousContrat->date_debut = $previousContrat->date_debut->format('d/m/Y');
            $previousContrat->date_fin = $previousContrat->date_fin ? $previousContrat->date_fin->format('d/m/Y') : null;
        }

        return response()->json(['contrat' => $previousContrat]);
    }

    public function updateStatut(Request $request)
    {
        $this->authorize('contrat-statut-edit');

        $validated = $request->validate([
            'contrat_id' => 'required|exists:contrat_employe,id',
            'statut' => 'required|in:0,1',
        ]);

        try {
            $contrat = ContratEmploye::findOrFail($validated['contrat_id']);
            $contrat->update(['etat' => $validated['statut']]);

            return response()->json(['success' => true, 'message' => 'Statut du contrat mis à jour avec succès']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut : ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Employe $employe, ContratEmploye $contrat)
    {
        $this->authorize('contrat-delete');

        if ($contrat->id_employe !== $employe->id) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            if ($contrat->document) {
                $this->deleteContratDocument($contrat->document);
            }

            $contrat->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Le contrat a été supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue lors de la suppression : ' . $e->getMessage()]);
        }
    }

    public function deleteDocument(Request $request, $employeId, $contratId)
    {
        $this->authorize('contrat-document-manage');

        try {
            $employe = Employe::findOrFail($employeId);
            $contrat = ContratEmploye::where('id', $contratId)->where('id_employe', $employe->id)->first();

            if (!$contrat) {
                return response()->json(['success' => false, 'message' => 'Contrat non trouvé'], 404);
            }
            if (!$contrat->document) {
                return response()->json(['success' => false, 'message' => 'Aucun document associé à ce contrat'], 404);
            }

            $this->deleteContratDocument($contrat->document);
            $contrat->update(['document' => null]);

            return response()->json(['success' => true, 'message' => 'Document supprimé avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur suppression document contrat:', ['contrat_id' => $contratId, 'message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression du document: ' . $e->getMessage()], 500);
        }
    }

    private function storeContratDocument(\Illuminate\Http\UploadedFile $file): string
    {
        $directory = public_path('storage/contrats');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        $uniqueName = uniqid() . '_' . $file->getClientOriginalName();
        $file->move($directory, $uniqueName);

        return 'contrats/' . $uniqueName;
    }

    private function deleteContratDocument(string $path): void
    {
        $filePath = public_path('storage/' . $path);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
