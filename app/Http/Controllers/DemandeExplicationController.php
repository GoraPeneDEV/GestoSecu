<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\DemandeExplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class DemandeExplicationController extends Controller
{
    public function index()
    {
        $this->authorize('demande-explication-view');

        $currentMonth = date('n');
        $currentYear = date('Y');

        $stats = $this->getStatistics($currentMonth, $currentYear);

        return view('demandes-explications.index', compact('stats'));
    }

    public function getStatistics($mois = null, $annee = null)
    {
        $query = DemandeExplication::query();

        if ($annee) {
            $query->whereYear('date_incident', $annee);
        }

        if ($mois) {
            $query->whereMonth('date_incident', $mois);
        }

        return [
            'total' => (clone $query)->count(),
            'en_attente' => (clone $query)->enAttente()->count(),
            'repondues' => (clone $query)->repondues()->count(),
        ];
    }

    public function stats(Request $request)
    {
        $stats = $this->getStatistics($request->get('mois'), $request->get('annee'));

        return response()->json($stats);
    }

    public function create()
    {
        $this->authorize('demande-explication-create');

        $employes = $this->getEmployesActifs();

        return view('demandes-explications.create', compact('employes'));
    }

    public function edit(DemandeExplication $demande)
    {
        $this->authorize('demande-explication-update');

        if ($demande->statut !== 'en_attente') {
            return redirect()->route('demandes-explications.index')
                ->with('error', 'Seules les demandes en attente peuvent être modifiées');
        }

        $employes = $this->getEmployesActifs();

        return view('demandes-explications.edit', compact('demande', 'employes'));
    }

    private function getEmployesActifs()
    {
        return Employe::select('id', 'prenom', 'nom', 'matricule', 'id_departement')
            ->with(['departement:id,nom'])
            ->where('etat', 1)
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get();
    }

    public function getDemandesExplications(Request $request)
    {
        $query = DemandeExplication::with(['employe.departement', 'createur']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }

        if ($request->filled('annee')) {
            $query->whereYear('date_incident', $request->annee);
        }

        if ($request->filled('mois')) {
            $query->whereMonth('date_incident', $request->mois);
        }

        return DataTables::of($query)
            ->addColumn('employe_info', function ($demande) {
                if ($demande->employe) {
                    return $demande->employe->prenom . ' ' . $demande->employe->nom .
                        '<br><small class="text-muted">' . ($demande->employe->departement->nom ?? 'N/A') . '</small>';
                }
                return 'Employé non trouvé';
            })
            ->editColumn('date_incident', function ($demande) {
                return $demande->date_incident->format('d/m/Y');
            })
            ->addColumn('statut_badge', function ($demande) {
                $badges = [
                    'en_attente' => '<span class="badge bg-warning">En attente</span>',
                    'repondue' => '<span class="badge bg-success">Répondue</span>',
                ];
                return $badges[$demande->statut] ?? '<span class="badge bg-secondary">Inconnu</span>';
            })
            ->addColumn('document', function ($demande) {
                if ($demande->document_path) {
                    return '<a href="' . asset('storage/' . $demande->document_path) . '" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-file-text"></i>
                            </a>';
                }
                return '<span class="text-muted">Aucun</span>';
            })
            ->addColumn('actions', function ($demande) {
                $actions = '<div class="d-inline-block text-nowrap">';
                $actions .= '<button class="btn btn-sm btn-icon me-2 btn-view-demande" data-id="' . $demande->id . '" title="Voir détails">
                            <i class="ti ti-eye text-primary"></i>
                        </button>';
                if ($demande->isEnAttente()) {
                    $actions .= '<a href="' . route('demandes-explications.edit', $demande->id) . '" class="btn btn-sm btn-icon me-2" title="Modifier">
                                <i class="ti ti-pencil text-warning"></i>
                            </a>';
                    $actions .= '<button class="btn btn-sm btn-icon me-2 btn-respond-demande" data-id="' . $demande->id . '" title="Répondre">
                                <i class="ti ti-message-circle text-success"></i>
                            </button>';
                }
                $actions .= '<button class="btn btn-sm btn-icon btn-delete-demande" data-id="' . $demande->id . '" title="Supprimer">
                            <i class="ti ti-trash text-danger"></i>
                        </button>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['employe_info', 'statut_badge', 'document', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $this->authorize('demande-explication-create');

        $validated = $request->validate([
            'employe_id' => 'required|exists:employe,id',
            'motif' => 'required|string|max:255',
            'description' => 'required|string',
            'date_incident' => 'required|date',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:204800',
        ]);

        try {
            $demande = new DemandeExplication();
            $demande->employe_id = $validated['employe_id'];
            $demande->motif = $validated['motif'];
            $demande->description = $validated['description'];
            $demande->date_incident = $validated['date_incident'];
            $demande->cree_par = Auth::id();

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $uniqueName = uniqid() . '_' . $file->getClientOriginalName();
                $path = 'demandes-explications/' . $uniqueName;
                $file->move(public_path('storage/demandes-explications'), $uniqueName);
                $demande->document_path = $path;
            }

            $demande->save();

            return redirect()->route('demandes-explications.index')
                ->with('success', 'Demande d\'explication créée avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    public function show(DemandeExplication $demande)
    {
        $demande->load(['employe.departement', 'createur']);

        return view('demandes-explications.show', compact('demande'));
    }

    public function showAjax($id)
    {
        $demande = DemandeExplication::with(['employe.departement', 'createur'])->findOrFail($id);

        return response()->json($demande);
    }

    public function update(Request $request, DemandeExplication $demande)
    {
        $this->authorize('demande-explication-update');

        if ($demande->statut !== 'en_attente') {
            return redirect()->route('demandes-explications.index')
                ->with('error', 'Seules les demandes en attente peuvent être modifiées');
        }

        $validated = $request->validate([
            'employe_id' => 'required|exists:employe,id',
            'motif' => 'required|string|max:255',
            'description' => 'required|string',
            'date_incident' => 'required|date',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:204800',
        ]);

        try {
            $demande->employe_id = $validated['employe_id'];
            $demande->motif = $validated['motif'];
            $demande->description = $validated['description'];
            $demande->date_incident = $validated['date_incident'];

            if ($request->hasFile('document')) {
                if ($demande->document_path && file_exists(public_path('storage/' . $demande->document_path))) {
                    unlink(public_path('storage/' . $demande->document_path));
                }

                $file = $request->file('document');
                $uniqueName = uniqid() . '_' . $file->getClientOriginalName();
                $path = 'demandes-explications/' . $uniqueName;
                $file->move(public_path('storage/demandes-explications'), $uniqueName);
                $demande->document_path = $path;
            }

            $demande->save();

            return redirect()->route('demandes-explications.index')
                ->with('success', 'Demande d\'explication mise à jour avec succès');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    public function repondre(Request $request, $id)
    {
        $demande = DemandeExplication::findOrFail($id);

        if ($demande->statut !== 'en_attente') {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande ne peut plus être répondue',
            ], 403);
        }

        $validated = $request->validate([
            'date_reponse' => 'required|date|before_or_equal:today',
            'reponse_document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:204800',
        ]);

        try {
            $reponseDir = public_path('storage/demandes-explications/reponses');
            if (!file_exists($reponseDir)) {
                mkdir($reponseDir, 0755, true);
            }

            $file = $request->file('reponse_document');
            $uniqueName = 'reponse_' . uniqid() . '_' . $file->getClientOriginalName();
            $path = 'demandes-explications/reponses/' . $uniqueName;
            $file->move($reponseDir, $uniqueName);

            $demande->update([
                'statut' => 'repondue',
                'reponse_document_path' => $path,
                'date_reponse' => $validated['date_reponse'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Réponse enregistrée avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de la réponse : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $this->authorize('demande-explication-delete');

        try {
            $demande = DemandeExplication::findOrFail($id);

            if ($demande->document_path && file_exists(public_path('storage/' . $demande->document_path))) {
                unlink(public_path('storage/' . $demande->document_path));
            }

            $demande->delete();

            return response()->json([
                'success' => true,
                'message' => 'Demande supprimée avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getEmployes()
    {
        try {
            $employes = $this->getEmployesActifs();

            if ($employes->isEmpty()) {
                return response()->json([]);
            }

            $result = $employes->map(function ($employe) {
                $departementNom = $employe->departement ? $employe->departement->nom : 'N/A';
                $matricule = $employe->matricule ? ' (' . $employe->matricule . ')' : '';

                return [
                    'id' => $employe->id,
                    'text' => trim($employe->prenom . ' ' . $employe->nom) . $matricule . ' - ' . $departementNom,
                ];
            });

            return response()->json($result->values()->all());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du chargement des employés',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
