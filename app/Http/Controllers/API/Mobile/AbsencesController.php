<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Models\DemandeAbsenceAdmin;
use App\Models\JourFerier;
use App\Models\User;
use App\Notifications\DemandeAbsenceAdminNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * "Mes demandes d'absence" et validation hiérarchique — équivalent mobile de
 * App\Http\Controllers\DemandeAbsenceAdminController (guard web). Réplique
 * volontairement la logique métier du contrôleur web (autorisations hybrides
 * permission/département/propriété, calcul des jours ouvrables,
 * notifications) plutôt que de la réinventer.
 */
class AbsencesController extends Controller
{
    /**
     * GET /api/mobile/absences/moi
     * Permission : conge-admin-view.
     */
    public function mesDemandes(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('conge-admin-view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission d\'accéder à votre historique de congés.',
                ], 403);
            }

            $employe = $user->employe;

            if (!$employe) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $demandes = DemandeAbsenceAdmin::where('id_employe', $employe->id)
                ->orderByDesc('date_debut')
                ->get();

            $demandes->each(function (DemandeAbsenceAdmin $d) use ($user) {
                $d->append(['statut_libelle', 'type_conges_libelle', 'document_url']);
                $d->peut_annuler = $d->peutEtreAnnuleParCreateur($user->id);
            });

            return response()->json(['success' => true, 'data' => $demandes]);
        } catch (\Exception $e) {
            Log::error('AbsencesController::mesDemandes', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de vos demandes.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/mobile/absences
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->can('conge-admin-create')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de créer une demande d\'absence.',
                ], 403);
            }

            $employe = $user->employe;
            if (!$employe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez être associé à un employé pour faire une demande d\'absence.',
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'type_conges' => 'required|string',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
                'motif' => 'required|string',
                'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            $demandeExistante = DemandeAbsenceAdmin::where('id_employe', $employe->id)
                ->where('date_debut', $validated['date_debut'])
                ->where('date_fin', $validated['date_fin'])
                ->whereNull('deleted_at')
                ->first();

            if ($demandeExistante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une demande d\'absence existe déjà pour cette période (du '
                        . Carbon::parse($validated['date_debut'])->format('d/m/Y') . ' au '
                        . Carbon::parse($validated['date_fin'])->format('d/m/Y') . ').',
                ], 422);
            }

            DB::beginTransaction();

            $dateDebut = Carbon::parse($validated['date_debut']);
            $dateFin = Carbon::parse($validated['date_fin']);
            $nbrJoursOuvrables = $this->calculerJoursOuvrables($dateDebut, $dateFin);

            $demande = DemandeAbsenceAdmin::create([
                'id_employe' => $employe->id,
                'type_conges' => $validated['type_conges'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'nbr_jour' => $nbrJoursOuvrables,
                'motif' => $validated['motif'],
                'statut' => DemandeAbsenceAdmin::STATUT_EN_ATTENTE,
                'date_enregistrement' => now(),
                'cree_par' => $user->id,
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
                Log::error('Erreur notification nouvelle demande absence admin (mobile):', ['message' => $e->getMessage()]);
            }

            $demande->append(['statut_libelle', 'type_conges_libelle', 'document_url']);

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'absence enregistrée avec succès.',
                'data' => $demande,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AbsencesController::store', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de la demande.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/mobile/absences/calculate-working-days
     */
    public function calculateWorkingDays(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $dateDebut = Carbon::parse($request->date_debut);
            $dateFin = Carbon::parse($request->date_fin);

            $joursFeries = JourFerier::whereBetween('date_ferier', [
                $dateDebut->copy()->startOfDay(),
                $dateFin->copy()->endOfDay(),
            ])->get();

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
                'nbr_jours' => $joursOuvrables,
                'details' => [
                    'total_jours' => $totalJours,
                    'nombre_dimanches' => $nombreDimanches,
                    'jours_feries' => [
                        'nombre' => $joursFeries->count(),
                        'dates' => $joursFeries->map(fn ($jour) => [
                            'date' => $jour->date_ferier->format('d/m/Y'),
                            'description' => $jour->description,
                        ]),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('AbsencesController::calculateWorkingDays', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du calcul.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/mobile/absences/{demande}/annuler-par-createur
     * Réservé au créateur, statut ∈ {en_attente, valide_superieur}.
     */
    public function annulerParCreateur(Request $request, DemandeAbsenceAdmin $demande)
    {
        try {
            $user = $request->user();

            if (!$demande->peutEtreAnnuleParCreateur($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas annuler cette demande.',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'motif_annulation' => 'required|string|min:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $demande->update([
                'statut' => DemandeAbsenceAdmin::STATUT_ANNULE,
                'id_rh_annulation' => $user->id,
                'motif_annulation_rh' => $request->input('motif_annulation'),
                'date_annulation' => now(),
            ]);

            $this->notifierActeurs($demande, 'annulation');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Votre demande a été annulée avec succès.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AbsencesController::annulerParCreateur', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'annulation.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/mobile/absences/departement
     * RH/Direction/responsable de département voient tout leur département ;
     * sinon nécessite conge-admin-dept-view.
     */
    public function departement(Request $request)
    {
        try {
            $user = $request->user();

            $deptNom = $user->departement?->nom;
            $isRH = $deptNom === 'RH';
            $isDir = $deptNom === 'Direction';
            $isResp = $user->employe && $user->employe->departement
                && $user->employe->id === $user->employe->departement->responsable_id;

            if (!$isRH && !$isDir && !$isResp && !$user->can('conge-admin-dept-view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'avez pas la permission de voir les demandes de votre département.',
                ], 403);
            }

            $query = DemandeAbsenceAdmin::with(['employe.departement']);
            if (!$isRH && !$isDir) {
                $query->whereHas('employe', fn ($q) => $q->where('id_departement', $user->departement_id ?? 0));
            }

            $canValidate = $isRH || $isDir || $isResp || $user->can('conge-admin-validate');
            $canRefuse = $isRH || $isDir || $isResp || $user->can('conge-admin-refuse');
            $canCancel = $isRH || $isDir || $user->can('conge-admin-cancel');

            $demandes = $query->orderByDesc('date_debut')->get();

            $demandes->each(function (DemandeAbsenceAdmin $d) use ($user, $canValidate, $canRefuse, $canCancel) {
                $d->append(['statut_libelle', 'type_conges_libelle', 'document_url']);
                $d->peut_valider_phase1 = $d->statut === DemandeAbsenceAdmin::STATUT_EN_ATTENTE && ($canValidate || $canRefuse);
                $d->peut_annuler = ($d->statut === DemandeAbsenceAdmin::STATUT_VALIDE_RH && $canCancel)
                    || $d->peutEtreAnnuleParCreateur($user->id);
            });

            $stats = [
                'total' => $demandes->count(),
                'en_attente' => $demandes->where('statut', DemandeAbsenceAdmin::STATUT_EN_ATTENTE)->count(),
                'en_cours' => $demandes->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR)->count(),
                'approuvees' => $demandes->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_RH)->count(),
                'refusees' => $demandes->whereIn('statut', [
                    DemandeAbsenceAdmin::STATUT_REFUSE_SUPERIEUR,
                    DemandeAbsenceAdmin::STATUT_REFUSE_RH,
                ])->count(),
                'annulees' => $demandes->where('statut', DemandeAbsenceAdmin::STATUT_ANNULE)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'demandes' => $demandes->values(),
                    'stats' => $stats,
                    'permissions' => compact('canValidate', 'canRefuse', 'canCancel'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('AbsencesController::departement', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des demandes du département.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/mobile/absences/suivi-global
     * RH et Direction voient tout ; les autres ne voient que leur propre
     * département (permission conge-admin-suivi-view).
     */
    public function suiviGlobal(Request $request)
    {
        try {
            $user = $request->user();
            $deptNom = $user->departement?->nom;
            $isRH = $deptNom === 'RH';
            $isDirection = $deptNom === 'Direction';

            if (!$isRH && !$isDirection && !$user->can('conge-admin-suivi-view')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Contactez votre administrateur pour obtenir la permission "Suivi global absences".',
                ], 403);
            }

            $query = DemandeAbsenceAdmin::with(['employe.departement', 'superieur', 'responsableRH']);
            if (!$isRH && !$isDirection) {
                $query->whereHas('employe', fn ($q) => $q->where('id_departement', $user->departement_id));
            }

            $canPhase2 = $isRH || $isDirection || $user->can('conge-admin-validate');
            $canCancel = $isRH || $isDirection || $user->can('conge-admin-cancel');

            $demandes = $query->orderByDesc('date_debut')->get();

            $demandes->each(function (DemandeAbsenceAdmin $d) use ($user, $isRH, $isDirection, $canPhase2, $canCancel) {
                $isResponsable = $user->employe
                    && $user->employe->id === ($d->employe?->departement?->responsable_id ?? null);
                $canValidate = $isRH || $isDirection || $isResponsable || $user->can('conge-admin-validate');
                $canRefuse = $isRH || $isDirection || $isResponsable || $user->can('conge-admin-refuse');

                $d->append(['statut_libelle', 'type_conges_libelle', 'document_url']);
                $d->peut_valider_phase1 = $d->statut === DemandeAbsenceAdmin::STATUT_EN_ATTENTE && ($canValidate || $canRefuse);
                $d->peut_valider_phase2 = $d->statut === DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR && $canPhase2;
                $d->peut_annuler = $d->statut === DemandeAbsenceAdmin::STATUT_VALIDE_RH && $canCancel;
            });

            $stats = [
                'total' => $demandes->count(),
                'en_attente' => $demandes->where('statut', DemandeAbsenceAdmin::STATUT_EN_ATTENTE)->count(),
                'valide_superieur' => $demandes->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR)->count(),
                'valide_rh' => $demandes->where('statut', DemandeAbsenceAdmin::STATUT_VALIDE_RH)->count(),
                'refuse' => $demandes->whereIn('statut', [
                    DemandeAbsenceAdmin::STATUT_REFUSE_SUPERIEUR,
                    DemandeAbsenceAdmin::STATUT_REFUSE_RH,
                ])->count(),
                'annule' => $demandes->where('statut', DemandeAbsenceAdmin::STATUT_ANNULE)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'demandes' => $demandes->values(),
                    'stats' => $stats,
                    'is_rh' => $isRH,
                    'is_direction' => $isDirection,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('AbsencesController::suiviGlobal', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du suivi global.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/mobile/absences/{demande}/validation-superieur (Phase 1)
     */
    public function validationSuperieur(Request $request, DemandeAbsenceAdmin $demande)
    {
        try {
            $user = $request->user();
            $deptNom = $user->departement?->nom;
            $isRH = $deptNom === 'RH';
            $isDirection = $deptNom === 'Direction';
            $isResponsable = $user->employe && $user->employe->departement
                && $user->employe->id === $user->employe->departement->responsable_id;

            $canAct = $isRH || $isDirection || $isResponsable
                || $user->can('conge-admin-validate')
                || $user->can('conge-admin-refuse');

            if (!$canAct) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'decision' => 'required|in:valider,refuser',
                'commentaire_sup' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $demande->load(['employe.departement', 'superieur']);

            $statut = $request->input('decision') === 'valider'
                ? DemandeAbsenceAdmin::STATUT_VALIDE_SUPERIEUR
                : DemandeAbsenceAdmin::STATUT_REFUSE_SUPERIEUR;

            $demande->update([
                'statut' => $statut,
                'commentaire_sup' => $request->input('commentaire_sup'),
                'date_validation_sup' => now(),
                'id_superieur' => $user->id,
            ]);

            $demande->refresh();

            $this->notifierActeurs($demande, 'validation_superieur');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Demande ' . ($request->input('decision') === 'valider' ? 'validée' : 'refusée') . ' avec succès.',
                'data' => $demande,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AbsencesController::validationSuperieur', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la validation.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/mobile/absences/{demande}/validation-rh (Phase 2)
     * Réservée RH/Direction — même avec la permission conge-admin-validate,
     * un profil non-RH/Direction ne peut pas valider la Phase 2.
     */
    public function validationRH(Request $request, DemandeAbsenceAdmin $demande)
    {
        try {
            $user = $request->user();
            $deptNom = $user->departement?->nom;
            $isRH = $deptNom === 'RH';
            $isDirection = $deptNom === 'Direction';

            if (!$isRH && !$isDirection && !$user->can('conge-admin-validate') && !$user->can('conge-admin-refuse')) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }

            $validator = Validator::make($request->all(), [
                'decision' => 'required|in:valider,refuser',
                'commentaire_rh' => 'nullable|string',
                'a_deduire' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $demande->load(['employe.departement', 'responsableRH']);

            $decision = $request->input('decision');
            $aDeduire = $isRH && $request->boolean('a_deduire');

            $statut = $decision === 'valider'
                ? DemandeAbsenceAdmin::STATUT_VALIDE_RH
                : DemandeAbsenceAdmin::STATUT_REFUSE_RH;

            $demande->update([
                'statut' => $statut,
                'commentaire_rh' => $request->input('commentaire_rh'),
                'date_val_rh' => now(),
                'id_rh' => $user->id,
                'a_deduire' => $aDeduire,
            ]);

            if ($decision === 'valider' && $aDeduire) {
                $employe = $demande->employe;
                $employe->solde_conges -= $demande->nbr_jour;
                $employe->save();
            }

            $demande->refresh();

            $this->notifierActeurs($demande, 'validation_rh');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Demande ' . ($decision === 'valider' ? 'validée' : 'refusée') . ' avec succès.',
                'data' => $demande,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AbsencesController::validationRH', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la validation RH.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/mobile/absences/{demande}/annuler
     * RH/Direction (ou permission conge-admin-cancel), motif obligatoire
     * ≥10 caractères, uniquement sur une demande valide_rh.
     */
    public function annuler(Request $request, DemandeAbsenceAdmin $demande)
    {
        try {
            $user = $request->user();
            $deptNom = $user->departement?->nom;
            $isRH = $deptNom === 'RH';
            $isDirection = $deptNom === 'Direction';

            if (!$isRH && !$isDirection && !$user->can('conge-admin-cancel')) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
            }

            if (!$demande->peutEtreAnnule()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette demande ne peut plus être annulée.',
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'motif_annulation_rh' => 'required|string|min:10',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            $demande->update([
                'statut' => DemandeAbsenceAdmin::STATUT_ANNULE,
                'id_rh_annulation' => $user->id,
                'motif_annulation_rh' => $request->input('motif_annulation_rh'),
                'date_annulation' => now(),
            ]);

            $this->notifierActeurs($demande, 'annulation');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'La demande a été annulée avec succès.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AbsencesController::annuler', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'annulation.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Exclut dimanches et jours fériés.
     */
    private function calculerJoursOuvrables(Carbon $dateDebut, Carbon $dateFin): int
    {
        $nbJours = 0;
        $dateCourante = $dateDebut->copy();

        $joursFeries = JourFerier::whereBetween('date_ferier', [$dateDebut, $dateFin])
            ->pluck('date_ferier')
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        while ($dateCourante->lte($dateFin)) {
            if ($dateCourante->dayOfWeek !== Carbon::SUNDAY && !in_array($dateCourante->format('Y-m-d'), $joursFeries, true)) {
                $nbJours++;
            }
            $dateCourante->addDay();
        }

        return $nbJours;
    }

    /**
     * Notifications in-app (pas d'email en dur) : l'employé concerné, son
     * responsable de département, et les utilisateurs du département RH.
     */
    private function notifierActeurs(DemandeAbsenceAdmin $demande, string $type): void
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

        User::whereHas('departement', fn ($q) => $q->where('nom', 'RH'))->each(function ($u) use ($destinataires) {
            $destinataires->push($u);
        });

        $destinataires->unique('id')->each(fn ($u) => $u->notify($notification));
    }
}
