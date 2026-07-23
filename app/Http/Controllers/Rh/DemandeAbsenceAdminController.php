<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Employe;
use App\Models\JourFerier;
use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DemandeAbsenceAdmin;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DemandeAbsenceAdminNotification;
use Illuminate\Support\Facades\Log;

class DemandeAbsenceAdminController extends Controller
{
    /**
     * Mon Historique — demandes personnelles de l'utilisateur connecté
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->can('conge-admin-view')) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder à votre historique de congés.');
        }

        $employe = $user->employe;
        $q = $employe
            ? DemandeAbsenceAdmin::where('id_employe', $employe->id)
            : DemandeAbsenceAdmin::whereRaw('0=1');

        $stats = [
            'solde_conges' => $employe?->solde_conges ?? 0,
            'total' => (clone $q)->count(),
            'en_attente' => (clone $q)->whereIn('statut', [
                DemandeAbsenceAdmin::STATUT_EN_ATTENTE,
                DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR,
            ])->count(),
            'approuvees' => (clone $q)->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_RH)->count(),
            'refusees_annulees' => (clone $q)->whereIn('statut', [
                DemandeAbsenceAdmin::STATUT_REFUSE_SUPERIEUR,
                DemandeAbsenceAdmin::STATUT_REFUSE_RH,
                DemandeAbsenceAdmin::STATUT_ANNULE,
            ])->count(),
        ];

        return view('rh.absences-admin.index', compact('stats', 'employe'));
    }

    /**
     * Toutes les demandes — département de l'utilisateur connecté
     */
    public function departement()
    {
        $user = Auth::user();
        $deptNom = $user->departement?->nom;
        $isRH = $deptNom === 'RH';
        $isDir = $deptNom === 'Direction';
        $isResp = $user->employe && $user->employe->departement &&
                  $user->employe->id === $user->employe->departement->responsable_id;

        if (!$isRH && !$isDir && !$isResp && !$user->can('conge-admin-dept-view')) {
            abort(403, 'Vous n\'avez pas la permission de voir les demandes de votre département.');
        }

        $query = DemandeAbsenceAdmin::query();
        if (!$isRH && !$isDir) {
            $query->whereHas('employe', fn($q) => $q->where('id_departement', $user->departement_id ?? 0));
        }

        $stats = [
            'total' => (clone $query)->count(),
            'en_attente' => (clone $query)->where('statut', DemandeAbsenceAdmin::STATUT_EN_ATTENTE)->count(),
            'en_cours' => (clone $query)->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR)->count(),
            'approuvees' => (clone $query)->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_RH)->count(),
            'refusees' => (clone $query)->whereIn('statut', [
                DemandeAbsenceAdmin::STATUT_REFUSE_SUPERIEUR,
                DemandeAbsenceAdmin::STATUT_REFUSE_RH,
            ])->count(),
            'annulees' => (clone $query)->where('statut', DemandeAbsenceAdmin::STATUT_ANNULE)->count(),
        ];

        $canValidate = $isRH || $isDir || $isResp || $user->can('conge-admin-validate');
        $canRefuse = $isRH || $isDir || $isResp || $user->can('conge-admin-refuse');
        $canCancel = $isRH || $isDir || $user->can('conge-admin-cancel');
        $canDelete = $user->can('conge-admin-delete');
        $canEdit = $user->can('conge-admin-edit');

        return view('rh.absences-admin.departement', compact(
            'stats', 'deptNom', 'isRH', 'isDir',
            'canValidate', 'canRefuse', 'canCancel', 'canDelete', 'canEdit'
        ));
    }

    /**
     * DataTable — Toutes les demandes du département
     */
    public function getDataDepartement(Request $request)
    {
        $user = Auth::user();
        $deptNom = $user->departement?->nom;
        $isRH = $deptNom === 'RH';
        $isDir = $deptNom === 'Direction';
        $isResp = $user->employe && $user->employe->departement &&
                  $user->employe->id === $user->employe->departement->responsable_id;

        if (!$isRH && !$isDir && !$isResp && !$user->can('conge-admin-dept-view')) {
            return response()->json(['error' => true, 'message' => 'Accès non autorisé'], 403);
        }

        $canValidate = $isRH || $isDir || $isResp || $user->can('conge-admin-validate');
        $canRefuse = $isRH || $isDir || $isResp || $user->can('conge-admin-refuse');
        $canCancel = $isRH || $isDir || $user->can('conge-admin-cancel');
        $canDelete = $user->can('conge-admin-delete');

        $query = DemandeAbsenceAdmin::with(['employe.departement']);
        if (!$isRH && !$isDir) {
            $query->whereHas('employe', fn($q) => $q->where('id_departement', $user->departement_id ?? 0));
        }

        if ($request->filled('type_conge')) {
            $query->where('type_conges', $request->type_conge);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('mois') && $request->filled('annee')) {
            $query->whereMonth('date_debut', $request->mois)->whereYear('date_debut', $request->annee);
        } elseif ($request->filled('mois')) {
            $query->whereMonth('date_debut', $request->mois);
        } elseif ($request->filled('annee')) {
            $query->whereYear('date_debut', $request->annee);
        }

        $types = $this->typesConges();
        $badges = $this->statutBadges();

        return DataTables::of($query)
            ->addColumn('employe_nom', fn($d) => $d->employe ? $d->employe->prenom . ' ' . $d->employe->nom : '—')
            ->addColumn('type_label', fn($d) => $types[$d->type_conges] ?? $d->type_conges)
            ->addColumn('periode', fn($d) => ($d->date_debut?->format('d/m/Y') ?? '—') . ' → ' . ($d->date_fin?->format('d/m/Y') ?? '—'))
            ->addColumn('statut_badge', fn($d) => $badges[$d->statut] ?? '<span class="badge bg-secondary">?</span>')
            ->addColumn('actions', function ($demande) use ($user, $canValidate, $canRefuse, $canCancel, $canDelete) {
                $html = '<div class="d-flex gap-1 flex-wrap">';
                $html .= '<a href="' . route('absences-admin.show', $demande) . '" class="btn btn-sm btn-icon btn-outline-primary" title="Voir"><i class="ti ti-eye"></i></a>';

                if ($demande->statut === DemandeAbsenceAdmin::STATUT_EN_ATTENTE && ($canValidate || $canRefuse)) {
                    $html .= '<button class="btn btn-sm btn-icon btn-outline-info btn-dept-val-sup" data-id="' . $demande->id . '" title="Valider / Refuser"><i class="ti ti-check"></i></button>';
                }
                if ($demande->statut === DemandeAbsenceAdmin::STATUT_VALIDE_RH && $canCancel) {
                    $html .= '<button class="btn btn-sm btn-icon btn-outline-warning btn-dept-annuler" data-id="' . $demande->id . '" title="Annuler"><i class="ti ti-ban"></i></button>';
                }
                if ($demande->peutEtreAnnuleParCreateur($user->id)) {
                    $html .= '<button class="btn btn-sm btn-icon btn-outline-warning btn-annuler-createur" data-id="' . $demande->id . '" title="Annuler ma demande"><i class="ti ti-x"></i></button>';
                }
                if ($canDelete) {
                    $html .= '<button class="btn btn-sm btn-icon btn-outline-danger btn-dept-supprimer" data-id="' . $demande->id . '" title="Supprimer"><i class="ti ti-trash"></i></button>';
                }

                $html .= '</div>';
                return $html;
            })
            ->filterColumn('employe_nom', function ($query, $keyword) {
                $query->whereHas('employe', fn($q) => $q->where(DB::raw("CONCAT(prenom, ' ', nom)"), 'LIKE', "%{$keyword}%"));
            })
            ->rawColumns(['statut_badge', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('conge-admin-create');

        $employe = Auth::user()->employe;

        if (!$employe) {
            return redirect()->back()->with('error', 'Vous devez être associé à un employé pour faire une demande d\'absence.');
        }

        $routeLibre = false;
        return view('rh.absences-admin.create', compact('employe', 'routeLibre'));
    }

    public function store(Request $request)
    {
        $this->authorize('conge-admin-create');

        return $this->traiterCreation($request, Auth::id());
    }

    private function traiterCreation(Request $request, ?int $creePar)
    {
        $validated = $request->validate([
            'employe_id' => 'required|exists:employe,id',
            'type_demande' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'required|string',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
        ]);

        $demandeExistante = DemandeAbsenceAdmin::where('id_employe', $request->employe_id)
            ->where('date_debut', $request->date_debut)
            ->where('date_fin', $request->date_fin)
            ->whereNull('deleted_at')
            ->first();

        if ($demandeExistante) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Une demande d\'absence existe déjà pour cette période (du ' . Carbon::parse($request->date_debut)->format('d/m/Y') . ' au ' . Carbon::parse($request->date_fin)->format('d/m/Y') . ').');
        }

        try {
            DB::beginTransaction();

            $dateDebut = Carbon::parse($request->date_debut);
            $dateFin = Carbon::parse($request->date_fin);
            $nbrJoursOuvrables = $this->calculerJoursOuvrables($dateDebut, $dateFin);

            $demande = DemandeAbsenceAdmin::create([
                'id_employe' => $request->employe_id,
                'type_conges' => $request->type_demande,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'nbr_jour' => $nbrJoursOuvrables,
                'motif' => $request->motif,
                'statut' => DemandeAbsenceAdmin::STATUT_EN_ATTENTE,
                'date_enregistrement' => now(),
                'cree_par' => $creePar,
            ]);

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                if ($file->isValid()) {
                    $uniqueName = uniqid() . '_' . $file->getClientOriginalName();
                    $path = 'demandes-absences-administration/' . $uniqueName;
                    $file->move(public_path('storage/demandes-absences-administration'), $uniqueName);
                    $demande->document_path = $path;
                    $demande->save();
                }
            }

            DB::commit();

            try {
                $this->notifierActeurs($demande, 'creation');
            } catch (\Exception $e) {
                Log::error('Erreur notification nouvelle demande absence admin:', ['message' => $e->getMessage()]);
            }

            return redirect()->route('absences-admin.index')
                ->with('success', 'Demande d\'absence enregistrée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    public function show(DemandeAbsenceAdmin $demande)
    {
        $demande->load(['employe.departement', 'superieur', 'responsableRH', 'responsableAnnulation']);

        return view('rh.absences-admin.show', compact('demande'));
    }

    /**
     * Vue globale — RH, Direction ou responsable de département
     */
    public function suiviGlobal()
    {
        $user = Auth::user();
        $dept = $user->departement?->nom;
        $isRH = $dept === 'RH';
        $isDirection = $dept === 'Direction';

        if (!$isRH && !$isDirection && !$user->can('conge-admin-suivi-view')) {
            abort(403, 'Accès non autorisé. Contactez votre administrateur pour obtenir la permission "Suivi global absences".');
        }

        $query = DemandeAbsenceAdmin::query();
        if (!$isRH && !$isDirection) {
            $query->whereHas('employe', function ($q) use ($user) {
                $q->where('id_departement', $user->departement_id);
            });
        }

        $stats = [
            'total' => (clone $query)->count(),
            'en_attente' => (clone $query)->where('statut', DemandeAbsenceAdmin::STATUT_EN_ATTENTE)->count(),
            'valide_superieur' => (clone $query)->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR)->count(),
            'valide_rh' => (clone $query)->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_RH)->count(),
            'refuse' => (clone $query)->whereIn('statut', [
                DemandeAbsenceAdmin::STATUT_REFUSE_SUPERIEUR,
                DemandeAbsenceAdmin::STATUT_REFUSE_RH,
            ])->count(),
            'annule' => (clone $query)->where('statut', DemandeAbsenceAdmin::STATUT_ANNULE)->count(),
        ];

        $departements = ($isRH || $isDirection) ? Departement::orderBy('nom')->get() : collect();

        return view('rh.absences-admin.suivi-global', compact('stats', 'departements', 'isRH', 'isDirection'));
    }

    public function getDataSuiviGlobal(Request $request)
    {
        $user = Auth::user();
        $dept = $user->departement?->nom;
        $isRH = $dept === 'RH';
        $isDirection = $dept === 'Direction';

        if (!$isRH && !$isDirection && !$user->can('conge-admin-suivi-view')) {
            return response()->json(['error' => true, 'message' => 'Accès non autorisé'], 403);
        }

        $query = DemandeAbsenceAdmin::with(['employe.departement', 'superieur', 'responsableRH']);

        if (!$isRH && !$isDirection) {
            $query->whereHas('employe', function ($q) use ($user) {
                $q->where('id_departement', $user->departement_id);
            });
        }

        if ($request->filled('departement_id') && ($isRH || $isDirection)) {
            $query->whereHas('employe', function ($q) use ($request) {
                $q->where('id_departement', $request->departement_id);
            });
        }
        if ($request->filled('type_conge')) {
            $query->where('type_conges', $request->type_conge);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('mois') && $request->filled('annee')) {
            $query->whereMonth('date_debut', $request->mois)->whereYear('date_debut', $request->annee);
        } elseif ($request->filled('annee')) {
            $query->whereYear('date_debut', $request->annee);
        } elseif ($request->filled('mois')) {
            $query->whereMonth('date_debut', $request->mois);
        }

        $types = $this->typesConges();
        $badges = $this->statutBadges();

        return DataTables::of($query)
            ->addColumn('employe_nom', fn($d) => $d->employe ? $d->employe->prenom . ' ' . $d->employe->nom : '—')
            ->addColumn('departement', fn($d) => $d->employe?->departement?->nom ?? '—')
            ->addColumn('type_conges_label', fn($d) => $types[$d->type_conges] ?? $d->type_conges)
            ->addColumn('periode', fn($d) => ($d->date_debut ? $d->date_debut->format('d/m/Y') : '—') . ' → ' . ($d->date_fin ? $d->date_fin->format('d/m/Y') : '—'))
            ->addColumn('statut_badge', fn($d) => $badges[$d->statut] ?? '<span class="badge bg-secondary">?</span>')
            ->addColumn('actions', function ($demande) use ($user, $isRH, $isDirection) {
                $isResponsable = $user->employe && $user->employe->id === ($demande->employe?->departement?->responsable_id ?? null);

                $canValidate = $isRH || $isDirection || $isResponsable || $user->can('conge-admin-validate');
                $canRefuse = $isRH || $isDirection || $isResponsable || $user->can('conge-admin-refuse');
                $canCancel = $isRH || $isDirection || $user->can('conge-admin-cancel');
                $canDelete = $isRH || $user->can('conge-admin-delete');
                $canPhase2 = $isRH || $isDirection || $user->can('conge-admin-validate');

                $html = '<div class="d-flex gap-1 flex-wrap">';
                $html .= '<a href="' . route('absences-admin.show', $demande->id) . '" class="btn btn-sm btn-icon btn-outline-primary" title="Voir"><i class="ti ti-eye"></i></a>';

                if ($demande->statut === DemandeAbsenceAdmin::STATUT_EN_ATTENTE && ($canValidate || $canRefuse)) {
                    $html .= '<button class="btn btn-sm btn-icon btn-outline-info btn-sg-val-sup" data-id="' . $demande->id . '" title="Valider / Refuser Phase 1"><i class="ti ti-check"></i></button>';
                }
                if ($demande->statut === DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR && $canPhase2) {
                    $html .= '<button class="btn btn-sm btn-icon btn-outline-success btn-sg-val-rh" data-id="' . $demande->id . '" data-isrh="' . ($isRH ? '1' : '0') . '" title="Validation Phase 2 (RH)"><i class="ti ti-checks"></i></button>';
                }
                if ($demande->statut === DemandeAbsenceAdmin::STATUT_VALIDE_RH && $canCancel) {
                    $html .= '<button class="btn btn-sm btn-icon btn-outline-warning btn-sg-annuler" data-id="' . $demande->id . '" title="Annuler"><i class="ti ti-ban"></i></button>';
                }
                if ($canDelete) {
                    $html .= '<button class="btn btn-sm btn-icon btn-outline-danger btn-sg-supprimer" data-id="' . $demande->id . '" title="Supprimer"><i class="ti ti-trash"></i></button>';
                }

                $html .= '</div>';
                return $html;
            })
            ->filterColumn('employe_nom', function ($query, $keyword) {
                $query->whereHas('employe', function ($q) use ($keyword) {
                    $q->where(DB::raw("CONCAT(prenom, ' ', nom)"), 'LIKE', "%{$keyword}%");
                });
            })
            ->rawColumns(['statut_badge', 'actions'])
            ->make(true);
    }

    public function validationSuperieur(Request $request, $demandeId)
    {
        $user = Auth::user();
        $dept = $user->departement?->nom;
        $isRH = $dept === 'RH';
        $isDirection = $dept === 'Direction';
        $isResponsable = $user->employe && $user->employe->departement &&
                          $user->employe->id === $user->employe->departement->responsable_id;

        $canAct = $isRH || $isDirection || $isResponsable
               || $user->can('conge-admin-validate')
               || $user->can('conge-admin-refuse');
        if (!$canAct) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        try {
            DB::beginTransaction();

            $demandeAbsence = DemandeAbsenceAdmin::with(['employe.departement', 'superieur'])->findOrFail($demandeId);

            $statut = $request->decision === 'valider'
                ? DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR
                : DemandeAbsenceAdmin::STATUT_REFUSE_SUPERIEUR;

            $demandeAbsence->update([
                'statut' => $statut,
                'commentaire_sup' => $request->commentaire_sup,
                'date_validation_sup' => now(),
                'id_superieur' => Auth::id(),
            ]);

            $demandeAbsence->refresh();

            $this->notifierActeurs($demandeAbsence, 'validation_superieur');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Demande ' . ($request->decision === 'valider' ? 'validée' : 'refusée') . ' avec succès.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation supérieur:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function validationRH(Request $request, $demandeId)
    {
        $user = Auth::user();
        $dept = $user->departement?->nom;
        $isRH = $dept === 'RH';
        $isDirection = $dept === 'Direction';

        if (!$isRH && !$isDirection && !$user->can('conge-admin-validate') && !$user->can('conge-admin-refuse')) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        try {
            DB::beginTransaction();

            $demandeAbsence = DemandeAbsenceAdmin::with(['employe.departement', 'responsableRH'])->findOrFail($demandeId);

            $statut = $request->decision === 'valider'
                ? DemandeAbsenceAdmin::STATUT_VALIDE_RH
                : DemandeAbsenceAdmin::STATUT_REFUSE_RH;

            $demandeAbsence->update([
                'statut' => $statut,
                'commentaire_rh' => $request->commentaire_rh,
                'date_val_rh' => now(),
                'id_rh' => Auth::id(),
                'a_deduire' => $isRH && $request->has('a_deduire'),
            ]);

            if ($request->decision === 'valider' && $isRH && $request->has('a_deduire')) {
                $employe = $demandeAbsence->employe;
                $employe->solde_conges -= $demandeAbsence->nbr_jour;
                $employe->save();
            }

            $demandeAbsence->refresh();

            $this->notifierActeurs($demandeAbsence, 'validation_rh');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Demande ' . ($request->decision === 'valider' ? 'validée' : 'refusée') . ' avec succès.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation RH:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function annuler(Request $request, DemandeAbsenceAdmin $demande)
    {
        $user = Auth::user();
        $dept = $user->departement?->nom;
        $isRH = $dept === 'RH';
        $isDirection = $dept === 'Direction';

        if (!$isRH && !$isDirection && !$user->can('conge-admin-cancel')) {
            abort(403, 'Accès non autorisé.');
        }

        if (!$demande->peutEtreAnnule()) {
            return back()->with('error', 'Cette demande ne peut plus être annulée.');
        }

        $request->validate(['motif_annulation_rh' => 'required|string|min:10']);

        try {
            DB::beginTransaction();

            $demande->update([
                'statut' => DemandeAbsenceAdmin::STATUT_ANNULE,
                'id_rh_annulation' => Auth::id(),
                'motif_annulation_rh' => $request->motif_annulation_rh,
                'date_annulation' => now(),
            ]);

            $this->notifierActeurs($demande, 'annulation');

            DB::commit();

            return redirect()->route('absences-admin.index')
                ->with('success', 'La demande a été annulée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de l\'annulation.');
        }
    }

    public function annulerParCreateur(Request $request, DemandeAbsenceAdmin $demande)
    {
        if (!$demande->peutEtreAnnuleParCreateur(Auth::id())) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas annuler cette demande.'], 403);
        }

        $request->validate(['motif_annulation' => 'required|string|min:10']);

        try {
            DB::beginTransaction();

            $demande->update([
                'statut' => DemandeAbsenceAdmin::STATUT_ANNULE,
                'id_rh_annulation' => Auth::id(),
                'motif_annulation_rh' => $request->motif_annulation,
                'date_annulation' => now(),
            ]);

            $this->notifierActeurs($demande, 'annulation');

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Votre demande a été annulée avec succès.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur annulation par créateur:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de l\'annulation.'], 500);
        }
    }

    public function getData(Request $request)
    {
        $user = Auth::user();

        if (!$user->can('conge-admin-view')) {
            return response()->json(['error' => true, 'message' => 'Accès non autorisé'], 403);
        }

        $employe = $user->employe;
        $query = DemandeAbsenceAdmin::query();

        if ($employe) {
            $query->where('id_employe', $employe->id);
        } else {
            $query->whereRaw('0 = 1');
        }

        if ($request->filled('type_conge')) {
            $query->where('type_conges', $request->type_conge);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('mois') && $request->filled('annee')) {
            $query->whereMonth('date_debut', $request->mois)->whereYear('date_debut', $request->annee);
        } elseif ($request->filled('mois')) {
            $query->whereMonth('date_debut', $request->mois);
        } elseif ($request->filled('annee')) {
            $query->whereYear('date_debut', $request->annee);
        }

        $types = $this->typesConges();
        $badges = $this->statutBadges();

        return DataTables::of($query)
            ->addColumn('type_label', fn($d) => $types[$d->type_conges] ?? $d->type_conges)
            ->addColumn('periode', fn($d) => ($d->date_debut?->format('d/m/Y') ?? '—') . ' → ' . ($d->date_fin?->format('d/m/Y') ?? '—'))
            ->addColumn('statut_badge', fn($d) => $badges[$d->statut] ?? '<span class="badge bg-secondary">?</span>')
            ->addColumn('soumise_le', fn($d) => $d->created_at?->format('d/m/Y') ?? '—')
            ->addColumn('actions', function ($demande) use ($user) {
                $html = '<div class="d-flex gap-1">';
                $html .= '<a href="' . route('absences-admin.show', $demande) . '" class="btn btn-sm btn-icon btn-outline-primary" title="Détails"><i class="ti ti-eye"></i></a>';
                if ($demande->peutEtreAnnuleParCreateur($user->id)) {
                    $html .= '<button class="btn btn-sm btn-icon btn-outline-warning btn-annuler-createur" data-id="' . $demande->id . '" title="Annuler ma demande"><i class="ti ti-x"></i></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['statut_badge', 'actions'])
            ->make(true);
    }

    /**
     * Envoie les notifications aux acteurs concernés (email + in-app)
     * — uniquement vers de vrais comptes User (pas d'adresse fixe codée en dur)
     */
    protected function notifierActeurs(DemandeAbsenceAdmin $demande, string $type)
    {
        $notification = new DemandeAbsenceAdminNotification($demande, $type);

        $destinataires = collect();

        $userEmploye = $demande->employe?->user;
        if ($userEmploye) {
            $destinataires->push($userEmploye);
        }

        $responsable = $demande->employe?->departement?->responsable?->user;
        if ($responsable) {
            $destinataires->push($responsable);
        }

        User::whereHas('departement', fn($q) => $q->where('nom', 'RH'))->each(function ($u) use ($destinataires) {
            $destinataires->push($u);
        });

        $destinataires->unique('id')->each(fn($u) => $u->notify($notification));
    }

    public function calculateWorkingDays(Request $request)
    {
        try {
            $request->validate([
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
            ]);

            $dateDebut = Carbon::parse($request->date_debut);
            $dateFin = Carbon::parse($request->date_fin);

            $joursFeries = JourFerier::whereBetween('date_ferier', [$dateDebut->startOfDay(), $dateFin->endOfDay()])->get();

            $nombreDimanches = 0;
            $dateCourante = $dateDebut->copy();
            while ($dateCourante->lte($dateFin)) {
                if ($dateCourante->isSunday()) {
                    $nombreDimanches++;
                }
                $dateCourante->addDay();
            }

            $totalJours = (int) $dateDebut->diffInDays($dateFin) + 1;
            $joursOuvrables = (int) ($totalJours - $nombreDimanches - $joursFeries->count());

            return response()->json([
                'success' => true,
                'details' => [
                    'total_jours' => $totalJours,
                    'nombre_dimanches' => $nombreDimanches,
                    'jours_feries' => [
                        'nombre' => $joursFeries->count(),
                        'dates' => $joursFeries->map(fn($j) => [
                            'date' => $j->date_ferier->format('d/m/Y'),
                            'description' => $j->description,
                        ]),
                    ],
                ],
                'nbr_jours' => $joursOuvrables,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors du calcul : ' . $e->getMessage()], 500);
        }
    }

    private function calculerJoursOuvrables($dateDebut, $dateFin)
    {
        $nbJours = 0;
        $dateCourante = $dateDebut->copy();

        $joursFeries = JourFerier::whereBetween('date_ferier', [$dateDebut, $dateFin])
            ->pluck('date_ferier')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        while ($dateCourante->lte($dateFin)) {
            if ($dateCourante->dayOfWeek !== Carbon::SUNDAY && !in_array($dateCourante->format('Y-m-d'), $joursFeries)) {
                $nbJours++;
            }
            $dateCourante->addDay();
        }

        return $nbJours;
    }

    public function createEnregistrementDirect()
    {
        $this->authorize('conge-admin-enregistrer');

        $employes = Employe::where('etat', 1)
            ->select('id', 'matricule', 'prenom', 'nom', 'solde_conges')
            ->orderBy('nom')
            ->get();

        return view('rh.absences-admin.enregistrement-direct', compact('employes'));
    }

    public function storeEnregistrementDirect(Request $request)
    {
        $this->authorize('conge-admin-enregistrer');

        $request->validate([
            'employe_id' => 'required|exists:employe,id',
            'type_demande' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'required|string',
            'a_deduire' => 'nullable',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:204800',
        ]);

        try {
            DB::beginTransaction();

            $demande = DemandeAbsenceAdmin::create([
                'id_employe' => $request->employe_id,
                'type_conges' => $request->type_demande,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'nbr_jour' => $this->calculerJoursOuvrables(
                    Carbon::parse($request->date_debut),
                    Carbon::parse($request->date_fin)
                ),
                'motif' => $request->motif,
                'statut' => DemandeAbsenceAdmin::STATUT_VALIDE_RH,
                'date_enregistrement' => now(),
                'a_deduire' => $request->has('a_deduire'),
            ]);

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                if ($file->isValid()) {
                    $uniqueName = uniqid() . '_' . $file->getClientOriginalName();
                    $path = 'demandes-absences-administration/' . $uniqueName;
                    $file->move(public_path('storage/demandes-absences-administration'), $uniqueName);
                    $demande->document_path = $path;
                    $demande->save();
                }
            }

            if ($request->has('a_deduire')) {
                $employe = $demande->employe;
                $employe->solde_conges -= $demande->nbr_jour;
                $employe->save();
            }

            DB::commit();
            return redirect()->route('absences-admin.index')
                ->with('success', 'Demande d\'absence enregistrée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
        }
    }

    public function demandesEnCours()
    {
        $this->authorize('conge-admin-view');

        $user = Auth::user();
        $query = DemandeAbsenceAdmin::whereIn('statut', [
            DemandeAbsenceAdmin::STATUT_EN_ATTENTE,
            DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR,
        ]);

        if (in_array($user->departement?->nom, ['RH', 'Direction'])) {
            // Pas de filtre pour RH et Direction
        } elseif ($user->employe && $user->employe->departement && $user->employe->id === $user->employe->departement->responsable_id) {
            $query->whereHas('employe', fn($q) => $q->where('id_departement', $user->departement_id));
        } elseif ($user->employe) {
            $query->where('id_employe', $user->employe->id);
        } else {
            $query->whereRaw('0=1');
        }

        $stats = [
            'total' => $query->count(),
            'en_attente' => (clone $query)->where('statut', DemandeAbsenceAdmin::STATUT_EN_ATTENTE)->count(),
            'validees_superieur' => (clone $query)->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR)->count(),
        ];

        return view('rh.absences-admin.demandes-en-cours', compact('stats'));
    }

    public function destroy(DemandeAbsenceAdmin $demande)
    {
        $this->authorize('conge-admin-delete');

        try {
            if (Auth::user()->departement?->nom !== 'RH') {
                return response()->json(['success' => false, 'message' => 'Vous n\'avez pas l\'autorisation de supprimer cette demande.'], 403);
            }

            DB::beginTransaction();

            if ($demande->statut === DemandeAbsenceAdmin::STATUT_VALIDE_RH && $demande->a_deduire) {
                $employe = $demande->employe;
                $employe->solde_conges += $demande->nbr_jour;
                $employe->save();
            }

            if ($demande->document_path) {
                $filePath = public_path('storage/' . $demande->document_path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $demande->forceDelete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'La demande a été supprimée définitivement avec succès.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la demande:', ['message' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de la suppression : ' . $e->getMessage()], 500);
        }
    }

    public function monSolde()
    {
        $user = Auth::user();
        $employe = $user->employe;

        if (!$employe) {
            return redirect()->back()->with('error', 'Votre compte n\'est pas lié à un profil employé. Contactez les RH.');
        }

        $demandes = DemandeAbsenceAdmin::where('id_employe', $employe->id)->orderByDesc('created_at')->get();

        $stats = [
            'solde_actuel' => $employe->solde_conges ?? 0,
            'en_attente' => $demandes->whereIn('statut', [
                DemandeAbsenceAdmin::STATUT_EN_ATTENTE,
                DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR,
            ])->sum('nbr_jour'),
            'jours_pris' => $demandes->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_RH)->sum('nbr_jour'),
            'total_demandes' => $demandes->count(),
        ];

        return view('rh.absences-admin.mon-solde', compact('employe', 'demandes', 'stats'));
    }

    /**
     * Formulaire de création accessible à tout utilisateur lié à un employé (sans permission dédiée)
     */
    public function createLibre()
    {
        $employe = Auth::user()->employe;

        if (!$employe) {
            return redirect()->back()->with('error', 'Vous devez être associé à un employé pour faire une demande d\'absence.');
        }

        $routeLibre = true;
        return view('rh.absences-admin.create', compact('employe', 'routeLibre'));
    }

    public function storeLibre(Request $request)
    {
        return $this->traiterCreation($request, Auth::id());
    }

    public function edit(DemandeAbsenceAdmin $demande)
    {
        $this->authorize('conge-admin-edit');

        $demande->load('employe');
        return view('rh.absences-admin.edit', compact('demande'));
    }

    private function typesConges(): array
    {
        return [
            'conge_annuel' => 'Congé annuel',
            'autorisation_absence' => "Autorisation d'absence",
            'conge_maladie' => 'Congé maladie',
            'conge_maternite' => 'Congé maternité',
            'conge_mariage' => 'Congé mariage',
            'deces' => 'Décès',
        ];
    }

    private function statutBadges(): array
    {
        return [
            'en_attente' => '<span class="badge bg-label-warning">En attente</span>',
            'valide_superieur' => '<span class="badge bg-label-info">Validé responsable</span>',
            'valide_rh' => '<span class="badge bg-label-success">Approuvé</span>',
            'refuse_superieur' => '<span class="badge bg-label-danger">Refusé (resp.)</span>',
            'refuse_rh' => '<span class="badge bg-label-danger">Refusé (RH)</span>',
            'annule' => '<span class="badge bg-label-secondary">Annulé</span>',
        ];
    }
}
