<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Employe;
use App\Models\EmployeEnfant;
use App\Models\EmployeEpouse;
use App\Models\Departement;
use Illuminate\Http\Request;
use App\Models\ContratEmploye;
use App\Models\EmployeDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EmployeController extends Controller
{
    public function index()
    {
        $this->authorize('employe-view');

        $totalEmployes = Employe::where('etat', 1)->count();
        $cdiCount = Employe::whereHas('contrats', fn($q) => $q->where('type_contrat', 'CDI'))->count();
        $cddCount = Employe::whereHas('contrats', fn($q) => $q->where('type_contrat', 'CDD'))->count();
        $stageCount = Employe::whereHas('contrats', fn($q) => $q->where('type_contrat', 'Stage'))->count();

        return view('rh.employes.index', compact('totalEmployes', 'cdiCount', 'cddCount', 'stageCount'));
    }

    public function create()
    {
        $this->authorize('employe-create');

        $departements = Departement::all();

        return view('rh.employes.create', compact('departements'));
    }

    public function store(Request $request)
    {
        $this->authorize('employe-create');

        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'id_departement' => 'required|exists:departements,id',
                'matricule' => 'nullable|string|max:255',
                'prenom' => 'required|string|max:255',
                'nom' => 'required|string|max:255',
                'fonction' => 'required|string|max:255',
                'sexe' => 'required|in:Homme,Femme',
                'date_naissance' => 'nullable|date',
                'lieu_naissance' => 'nullable|string',
                'telephone' => 'nullable|string',
                'adresse' => 'nullable|string|max:255',
                'situation_matrimoniale' => 'required|string',
                'niveau_experience' => 'nullable|string',
                'nbr_femme' => 'nullable|integer|min:0',
                'nbr_enfants' => 'nullable|integer|min:0',
                'cni' => 'nullable|string',
                'arts_martiaux' => 'nullable|in:Oui,Non',
                'date_delivrance' => 'nullable|date',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:204800',
                'compte_bancaire' => 'nullable|string',
                'banque' => 'nullable|string',
                'permis' => 'nullable|in:Oui,Non',
                'langues_parlees' => 'nullable|array',
                'langues_lues' => 'nullable|array',
                'niveau_etude' => 'nullable|string|max:255',
                'diplome' => 'nullable|string|max:255',
                'type_contrat' => 'required|string',
                'montant' => 'required|numeric|min:0',
                'date_debut' => 'required|date',
                'date_fin' => 'nullable|date|after:date_debut',
                'document' => 'nullable|file|mimes:pdf|max:204800',
                'documents.*.type_document' => 'required_with:documents.*.fichier|string',
                'documents.*.fichier' => 'required_with:documents.*.type_document|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:204800',
                'service_militaire' => 'nullable|in:Oui,Non',
                'corps_militaire' => 'required_if:service_militaire,Oui|nullable|string',
                'date_debut_service' => 'required_if:service_militaire,Oui|nullable|date',
                'date_fin_service' => 'required_if:service_militaire,Oui|nullable|date|after:date_debut_service',
                'personne_contact' => 'nullable|string|max:255',
                'numero_contact' => 'nullable|string|max:20',
                'lien_parente' => 'nullable|string|max:255',
                'epouses' => 'nullable|array',
                'epouses.*.nom_complet' => 'required_with:epouses|string|max:255',
                'epouses.*.telephone' => 'nullable|string|max:20',
                'enfants' => 'nullable|array',
                'enfants.*.nom_complet' => 'required_with:enfants|string|max:255',
                'enfants.*.telephone' => 'nullable|string|max:20',
                'enfants.*.date_naissance' => 'nullable|date|before_or_equal:today',
            ]);

            $employe = Employe::create([
                'matricule' => $request->matricule,
                'fonction' => $request->fonction,
                'prenom' => $request->prenom,
                'nom' => $request->nom,
                'sexe' => $request->sexe ?? 'Homme',
                'date_naissance' => $request->date_naissance,
                'lieu_naissance' => $request->lieu_naissance,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'situation_matrimoniale' => $request->situation_matrimoniale,
                'niveau_experience' => $request->niveau_experience,
                'nbr_femme' => $request->nbr_femme ?? 0,
                'nbr_enfants' => $request->nbr_enfants ?? 0,
                'cni' => $request->cni,
                'arts_martiaux' => $request->arts_martiaux ?? 'Non',
                'date_delivrance' => $request->date_delivrance,
                'id_departement' => $request->id_departement,
                'id_user' => Auth::id(),
                'etat' => 1,
                'permis' => $request->permis ?? 'Non',
                'banque' => $request->banque,
                'compte_bancaire' => $request->compte_bancaire,
                'langues_parlees' => $request->has('langues_parlees') ? implode(',', $request->langues_parlees) : null,
                'langues_lues' => $request->has('langues_lues') ? implode(',', $request->langues_lues) : null,
                'niveau_etude' => $request->niveau_etude,
                'diplome' => $request->diplome,
                'service_militaire' => $request->service_militaire ?? 'Non',
                'corps_militaire' => $request->service_militaire === 'Oui' ? $request->corps_militaire : null,
                'date_debut_service' => $request->service_militaire === 'Oui' ? $request->date_debut_service : null,
                'date_fin_service' => $request->service_militaire === 'Oui' ? $request->date_fin_service : null,
                'personne_contact' => $request->personne_contact,
                'numero_contact' => $request->numero_contact,
                'lien_parente' => $request->lien_parente,
            ]);

            $this->syncFamille($employe, $request);
            $this->handlePhotoUpload($employe, $request);
            $this->handleContrat($employe, $request);
            $this->handleNouveauxDocuments($employe, $request);

            DB::commit();

            return redirect()->route('employes.index')->with('success', 'Employé créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création employé: ', ['message' => $e->getMessage()]);

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return redirect()->route('employes.create')->withErrors($e->validator)->withInput();
            }

            return redirect()->route('employes.create')->withErrors(['error' => 'Erreur: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Employe $employe)
    {
        $this->authorize('employe-view');

        $employe->load([
            'contrats',
            'plannings.site',
            'dotations.details',
            'departement',
            'documents',
            'epouses',
            'enfants',
            'sanctions',
            'demandesAbsencesAdmin' => fn($q) => $q->orderBy('date_debut', 'desc'),
        ]);

        return view('rh.employes.show', compact('employe'));
    }

    public function edit(Employe $employe)
    {
        $this->authorize('employe-update');

        $employe->load(['departement', 'documents', 'epouses', 'enfants', 'contrats']);
        $departements = Departement::all();

        return view('rh.employes.edit', compact('employe', 'departements'));
    }

    public function update(Request $request, Employe $employe)
    {
        $this->authorize('employe-update');

        $validated = $request->validate([
            'matricule' => 'nullable|string|max:255',
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'fonction' => 'required|string|max:255',
            'sexe' => 'required|in:Homme,Femme',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string',
            'telephone' => 'nullable|string',
            'adresse' => 'nullable|string|max:255',
            'situation_matrimoniale' => 'required|string',
            'niveau_experience' => 'nullable|string',
            'diplome' => 'nullable|string',
            'nbr_femme' => 'nullable|integer|min:0',
            'nbr_enfants' => 'nullable|integer|min:0',
            'cni' => 'nullable|string',
            'arts_martiaux' => 'nullable|in:Oui,Non',
            'date_delivrance' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:204800',
            'id_departement' => 'required|exists:departements,id',
            'compte_bancaire' => 'nullable|string|max:255',
            'permis' => 'required|in:Oui,Non',
            'banque' => 'nullable|string',
            'solde_conges' => 'nullable|numeric|min:0',
            'langues_parlees' => 'nullable|array',
            'langues_lues' => 'nullable|array',
            'documents_to_delete' => 'nullable|array',
            'documents' => 'nullable|array',
            'documents.*.type_document' => 'required_with:documents.*.fichier|string',
            'documents.*.fichier' => 'required_with:documents.*.type_document|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:204800',
            'niveau_etude' => 'nullable|string|max:255',
            'service_militaire' => 'nullable|in:Oui,Non',
            'corps_militaire' => 'required_if:service_militaire,Oui|nullable|string',
            'date_debut_service' => 'nullable|date',
            'date_fin_service' => 'nullable|date|after:date_debut_service',
            'personne_contact' => 'nullable|string|max:255',
            'numero_contact' => 'nullable|string|max:20',
            'lien_parente' => 'nullable|string',
            'epouses' => 'nullable|array',
            'epouses.*.id' => 'nullable|exists:employe_epouses,id',
            'epouses.*.nom_complet' => 'required_with:epouses|string|max:255',
            'epouses.*.telephone' => 'nullable|string|max:20',
            'epouses_to_delete' => 'nullable|array',
            'epouses_to_delete.*' => 'exists:employe_epouses,id',
            'enfants' => 'nullable|array',
            'enfants.*.id' => 'nullable|exists:employe_enfants,id',
            'enfants.*.nom_complet' => 'required_with:enfants|string|max:255',
            'enfants.*.telephone' => 'nullable|string|max:20',
            'enfants.*.date_naissance' => 'nullable|date|before_or_equal:today',
            'enfants_to_delete' => 'nullable|array',
            'enfants_to_delete.*' => 'exists:employe_enfants,id',
            'contrat_id' => 'nullable|exists:contrat_employe,id',
            'type_contrat' => 'required|string',
            'montant' => 'required|numeric|min:0',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'document' => 'nullable|file|mimes:pdf|max:204800',
            'supprimer_photo' => 'nullable|in:0,1',
        ]);

        try {
            DB::beginTransaction();

            $this->deleteFamilleMembers($employe, $request, 'epouses_to_delete', EmployeEpouse::class);
            $this->deleteFamilleMembers($employe, $request, 'enfants_to_delete', EmployeEnfant::class);
            $this->syncFamille($employe, $request);
            $this->deleteDocuments($employe, $request->input('documents_to_delete', []));

            $updateData = $request->except([
                'photo', 'langues_parlees', 'langues_lues', 'documents', 'documents_to_delete',
                'epouses', 'epouses_to_delete', 'enfants', 'enfants_to_delete',
                'contrat_id', 'type_contrat', 'montant', 'date_debut', 'date_fin', 'document',
                '_token', '_method',
            ]);

            $updateData['langues_parlees'] = $request->has('langues_parlees') && is_array($request->langues_parlees)
                ? implode(',', $request->langues_parlees) : null;
            $updateData['langues_lues'] = $request->has('langues_lues') && is_array($request->langues_lues)
                ? implode(',', $request->langues_lues) : null;

            if ($request->service_militaire === 'Non') {
                $updateData['corps_militaire'] = null;
                $updateData['date_debut_service'] = null;
                $updateData['date_fin_service'] = null;
            }

            if ($request->input('supprimer_photo') == '1' && $employe->photo) {
                $this->deletePublicFile($employe->photo);
                $updateData['photo'] = null;
            }

            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                if ($employe->photo) {
                    $this->deletePublicFile($employe->photo);
                }
                $updateData['photo'] = $this->storePublicFile($request->file('photo'), 'employes/photos');
            }

            $employe->update($updateData);

            $this->handleContrat($employe, $request);
            $this->handleNouveauxDocuments($employe, $request);

            DB::commit();

            return redirect()->route('employes.show', $employe->id)->with('success', 'Employé modifié avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour employé: ', ['message' => $e->getMessage()]);

            return redirect()->back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, Employe $employe)
    {
        $this->authorize('employe-delete');

        $request->validate([
            'date_arret' => 'required|date_format:d/m/Y|before_or_equal:today',
            'motif_arret' => 'required|string|in:Démission,Licenciement,Fin de contrat,Retraite,Décès,Autre',
            'commentaire' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $dateArret = Carbon::createFromFormat('d/m/Y', $request->date_arret)->format('Y-m-d');

            $employe->update([
                'etat' => 0,
                'arret' => $dateArret,
                'motif_arret' => $request->motif_arret,
                'commentaire' => $request->commentaire,
            ]);

            $contratActif = $employe->contrats()->where('etat', 1)->whereNull('date_fin')->first();
            if ($contratActif) {
                $contratActif->update(['date_fin' => $dateArret, 'etat' => 0]);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Employé archivé avec succès']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'archivage : ' . $e->getMessage()], 500);
        }
    }

    public function getEmployes(Request $request)
    {
        $this->authorize('employe-view');

        $query = Employe::query()->with(['departement', 'contrats'])->where('etat', 1);

        if ($request->type_contrat) {
            if ($request->type_contrat === 'sans_contrat') {
                $query->whereDoesntHave('contrats', function ($q) {
                    $q->where('etat', 1)->where(fn($sub) => $sub->whereNull('date_fin')->orWhere('date_fin', '>', now()));
                });
            } else {
                $query->whereHas('contrats', function ($q) use ($request) {
                    $q->where('type_contrat', $request->type_contrat)->where('etat', 1)
                        ->where(fn($sub) => $sub->whereNull('date_fin')->orWhere('date_fin', '>', now()));
                });
            }
        }

        if ($request->departement) {
            $query->whereHas('departement', fn($q) => $q->where('nom', $request->departement));
        }

        if ($request->etat_dossier === 'incomplet') {
            $query->where(fn($q) => $q->whereNull('date_naissance')->orWhereNull('lieu_naissance')->orWhereNull('telephone'));
        } elseif ($request->etat_dossier === 'complet') {
            $query->whereNotNull('date_naissance')->whereNotNull('lieu_naissance');
        }

        if ($request->filled('search.value')) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('prenom', 'LIKE', "%{$searchValue}%")
                    ->orWhere('nom', 'LIKE', "%{$searchValue}%")
                    ->orWhereRaw("CONCAT(prenom, ' ', nom) LIKE ?", ["%{$searchValue}%"]);
            });
        }

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addColumn('prenom', function ($employe) {
                $photoHtml = $employe->photo && file_exists(public_path('storage/' . $employe->photo))
                    ? '<img src="' . asset('storage/' . $employe->photo) . '" class="rounded-circle me-2" width="40" height="40">'
                    : '<div class="avatar me-2" style="width:40px;height:40px;"><span class="avatar-initial rounded-circle bg-label-primary">' . substr($employe->prenom, 0, 1) . substr($employe->nom, 0, 1) . '</span></div>';

                return '<div class="d-flex justify-content-start align-items-center"><div class="avatar-wrapper">' . $photoHtml . '</div>
                    <div class="d-flex flex-column"><span class="fw-medium">' . e($employe->prenom) . '</span>
                    <small class="text-primary bg-light px-2 py-1 rounded-pill fw-semibold">' . e($employe->matricule) . '</small></div></div>';
            })
            ->addColumn('naissance_info', function ($employe) {
                $dateNaissance = $employe->date_naissance ? $employe->date_naissance->format('d/m/Y') : 'N/A';
                return '<div class="d-flex flex-column"><span class="fw-medium">' . $dateNaissance . '</span><small class="text-muted">' . e($employe->lieu_naissance ?: 'N/A') . '</small></div>';
            })
            ->addColumn('type_contrat', function ($employe) {
                $dernierContrat = $employe->contrats->where('etat', 1)->sortByDesc('date_debut')->first();
                return $dernierContrat
                    ? '<span class="text-primary fw-medium">' . e($dernierContrat->type_contrat) . '</span>'
                    : '<span class="badge bg-label-warning">Sans contrat</span>';
            })
            ->addColumn('actions', fn($employe) => '<div class="d-inline-block">
                <a href="' . route('employes.show', $employe->id) . '" class="btn btn-sm btn-icon"><i class="ti ti-eye text-primary"></i></a>
                <a href="' . route('employes.edit', $employe->id) . '" class="btn btn-sm btn-icon"><i class="ti ti-pencil text-warning"></i></a>
                <button type="button" class="btn btn-sm btn-icon btn-delete-employe" data-id="' . $employe->id . '"><i class="ti ti-trash text-danger"></i></button></div>')
            ->rawColumns(['actions', 'type_contrat', 'prenom', 'naissance_info'])
            ->make(true);
    }

    public function archived()
    {
        $this->authorize('employes-archive');

        $totalArchived = Employe::where('etat', 0)->count();
        $statsByMotif = Employe::where('etat', 0)->select('motif_arret', DB::raw('count(*) as total'))->groupBy('motif_arret')->get();

        return view('rh.employes.archived', compact('totalArchived', 'statsByMotif'));
    }

    public function getArchivedEmployes(Request $request)
    {
        $this->authorize('employes-archive');

        $query = Employe::query()->with('departement')->where('etat', 0);

        if ($request->motif_arret) {
            $query->where('motif_arret', $request->motif_arret);
        }
        if ($request->departement) {
            $query->whereHas('departement', fn($q) => $q->where('nom', $request->departement));
        }

        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->filterColumn('employe_info', function ($q, $keyword) {
                $q->where(fn($sub) => $sub->where('prenom', 'like', "%{$keyword}%")->orWhere('nom', 'like', "%{$keyword}%")->orWhere('matricule', 'like', "%{$keyword}%"));
            })
            ->addColumn('employe_info', fn($employe) => '<div class="d-flex flex-column"><span class="fw-medium">' . e($employe->prenom) . ' ' . e($employe->nom) . '</span></div>')
            ->editColumn('arret', fn($employe) => $employe->arret ? $employe->arret->format('d/m/Y') : '<span class="text-muted">—</span>')
            ->editColumn('motif_arret', function ($employe) {
                $colors = ['Licenciement' => 'danger', 'Démission' => 'warning', 'Fin de contrat' => 'info'];
                $color = $colors[$employe->motif_arret] ?? 'secondary';
                return '<span class="badge bg-label-' . $color . '">' . e($employe->motif_arret) . '</span>';
            })
            ->addColumn('actions', fn($employe) => '<div class="d-inline-block">
                <a href="' . route('employes.show', $employe->id) . '" class="btn btn-sm btn-icon"><i class="ti ti-eye text-primary"></i></a>
                <button type="button" class="btn btn-sm btn-icon btn-unarchive-employe" data-id="' . $employe->id . '"><i class="ti ti-restore text-success"></i></button></div>')
            ->rawColumns(['employe_info', 'arret', 'motif_arret', 'actions'])
            ->make(true);
    }

    public function unarchive(Employe $employe)
    {
        $this->authorize('employes-archive');

        $employe->update(['etat' => 1, 'arret' => null, 'motif_arret' => null]);
        $employe->contrats()->where('etat', 0)->update(['etat' => 1]);

        return redirect()->route('employes.show', $employe->id)
            ->with('success', 'L\'employé ' . $employe->prenom . ' ' . $employe->nom . ' a été désarchivé avec succès.');
    }

    public function uploadDocument(Request $request, Employe $employe)
    {
        $this->authorize('employe-update');

        try {
            $validated = $request->validate([
                'type_document' => 'required|string|max:255',
                'fichier' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:61440',
            ]);

            $chemin = $this->storePublicFile($request->file('fichier'), 'employes/documents');

            $document = EmployeDocument::create([
                'employe_id' => $employe->id,
                'type_document' => $validated['type_document'],
                'nom_fichier' => $request->file('fichier')->getClientOriginalName(),
                'chemin_fichier' => $chemin,
                'ajoute_par' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document ajouté avec succès',
                'document' => [
                    'id' => $document->id,
                    'type_document' => $document->type_document,
                    'nom_fichier' => $document->nom_fichier,
                    'date_ajout' => $document->created_at->format('d/m/Y H:i'),
                    'url' => asset('storage/' . $document->chemin_fichier),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Erreur de validation', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur upload document employé', ['employe_id' => $employe->id, 'message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'ajout du document : ' . $e->getMessage()], 500);
        }
    }

    public function deleteDocument(Request $request, $employeId, $documentId)
    {
        $this->authorize('employe-update');

        try {
            $employe = Employe::findOrFail($employeId);
            $document = EmployeDocument::where('id', $documentId)->where('employe_id', $employe->id)->first();

            if (!$document) {
                return response()->json(['success' => false, 'message' => 'Document non trouvé'], 404);
            }

            $this->deletePublicFile($document->chemin_fichier);
            $document->delete();

            return response()->json(['success' => true, 'message' => 'Document supprimé avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur suppression document employé', ['employe_id' => $employeId, 'document_id' => $documentId, 'message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression du document'], 500);
        }
    }

    public function downloadDocument(Employe $employe, EmployeDocument $document)
    {
        if ($document->employe_id !== $employe->id) {
            abort(403, 'Accès non autorisé');
        }

        $filePath = public_path('storage/' . $document->chemin_fichier);

        if (!file_exists($filePath)) {
            abort(404, 'Fichier introuvable');
        }

        return response()->download($filePath, $document->nom_fichier);
    }

    // ========================================
    // HELPERS PRIVÉS
    // ========================================

    private function syncFamille(Employe $employe, Request $request): void
    {
        if ($request->has('epouses') && is_array($request->epouses)) {
            foreach ($request->epouses as $data) {
                if (empty($data['nom_complet'])) {
                    continue;
                }
                $payload = ['nom_complet' => trim($data['nom_complet']), 'telephone' => $data['telephone'] ?? null];
                if (!empty($data['id'])) {
                    EmployeEpouse::where('id', $data['id'])->where('employe_id', $employe->id)->update($payload);
                } else {
                    $employe->epouses()->create($payload);
                }
            }
        }

        if ($request->has('enfants') && is_array($request->enfants)) {
            foreach ($request->enfants as $data) {
                if (empty($data['nom_complet'])) {
                    continue;
                }
                $payload = [
                    'nom_complet' => trim($data['nom_complet']),
                    'telephone' => $data['telephone'] ?? null,
                    'date_naissance' => $data['date_naissance'] ?? null,
                ];
                if (!empty($data['id'])) {
                    EmployeEnfant::where('id', $data['id'])->where('employe_id', $employe->id)->update($payload);
                } else {
                    $employe->enfants()->create($payload);
                }
            }
        }
    }

    private function deleteFamilleMembers(Employe $employe, Request $request, string $field, string $modelClass): void
    {
        $ids = $request->input($field, []);
        if (is_array($ids)) {
            $modelClass::whereIn('id', $ids)->where('employe_id', $employe->id)->delete();
        }
    }

    private function deleteDocuments(Employe $employe, array $documentIds): void
    {
        foreach ($documentIds as $id) {
            $document = EmployeDocument::where('id', trim($id))->where('employe_id', $employe->id)->first();
            if ($document) {
                $this->deletePublicFile($document->chemin_fichier);
                $document->delete();
            }
        }
    }

    private function handlePhotoUpload(Employe $employe, Request $request): void
    {
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $employe->update(['photo' => $this->storePublicFile($request->file('photo'), 'employes/photos')]);
        }
    }

    private function handleContrat(Employe $employe, Request $request): void
    {
        if (!$request->filled('type_contrat') || !$request->filled('montant') || !$request->filled('date_debut')) {
            return;
        }

        $contratData = [
            'type_contrat' => $request->type_contrat,
            'date_debut' => $request->date_debut,
            'date_prevu_fin' => $request->filled('date_fin') ? $request->date_fin : null,
            'montant' => $request->montant,
            'id_user' => Auth::id(),
        ];

        if ($request->filled('contrat_id')) {
            $contrat = ContratEmploye::where('id', $request->contrat_id)->where('id_employe', $employe->id)->first();
            if ($contrat) {
                $contrat->update($contratData);
            } else {
                $contrat = $employe->contrats()->create($contratData + ['etat' => 1]);
            }
        } else {
            $contrat = $employe->contrats()->create($contratData + ['etat' => 1]);
        }

        if ($request->hasFile('document') && $request->file('document')->isValid()) {
            if ($contrat->document) {
                $this->deletePublicFile($contrat->document);
            }
            $contrat->update(['document' => $this->storePublicFile($request->file('document'), 'contrats')]);
        }
    }

    private function handleNouveauxDocuments(Employe $employe, Request $request): void
    {
        if (!$request->has('documents') || !is_array($request->documents)) {
            return;
        }

        foreach ($request->documents as $data) {
            if (!isset($data['fichier'], $data['type_document']) || !$data['fichier'] instanceof \Illuminate\Http\UploadedFile || !$data['fichier']->isValid()) {
                continue;
            }

            EmployeDocument::create([
                'employe_id' => $employe->id,
                'type_document' => $data['type_document'],
                'nom_fichier' => $data['fichier']->getClientOriginalName(),
                'chemin_fichier' => $this->storePublicFile($data['fichier'], 'employes/documents'),
                'ajoute_par' => Auth::id(),
            ]);
        }
    }

    private function storePublicFile(\Illuminate\Http\UploadedFile $file, string $subDirectory): string
    {
        $uniqueName = uniqid() . '_' . $file->getClientOriginalName();
        $directory = public_path('storage/' . $subDirectory);

        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $file->move($directory, $uniqueName);

        return $subDirectory . '/' . $uniqueName;
    }

    private function deletePublicFile(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }
        $path = public_path('storage/' . $relativePath);
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
