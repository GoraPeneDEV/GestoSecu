@extends('layouts.contentNavbarLayout')

@section('title', 'Fiche ' . $ficheProgres->numero_fiche)

@section('content')
    <a href="{{ route('sav.fiches-progres.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3 class="mb-0">{{ $ficheProgres->numero_fiche }} — {{ $ficheProgres->objet }}</h3>
            <p class="text-muted mb-0">{{ $ficheProgres->client->nomClient ?? '-' }}</p>
        </div>
        <div>{!! $ficheProgres->statut_badge ?? '' !!}</div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Détails</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Type</dt><dd class="col-7">{{ $ficheProgres->type_label }}</dd>
                        <dt class="col-5">Processus</dt><dd class="col-7">{{ $ficheProgres->processus_label }}</dd>
                        <dt class="col-5">Créée par</dt><dd class="col-7">{{ $ficheProgres->createur->nom_complet ?? '-' }}</dd>
                        <dt class="col-5">Contrat</dt><dd class="col-7">{{ $ficheProgres->contrat->numero_contrat ?? '-' }}</dd>
                        <dt class="col-5">Constat client</dt><dd class="col-7">{{ $ficheProgres->constat_client }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Analyse 5M</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sav.fiches-progres.analyse', $ficheProgres->id) }}">
                        @csrf
                        @method('PATCH')
                        @php $a5m = $ficheProgres->analyse_5m ?? []; @endphp
                        @foreach (['matiere' => 'Matière', 'milieu' => 'Milieu', 'methodes' => 'Méthodes', 'materiel' => 'Matériel', 'main_oeuvre' => "Main d'œuvre"] as $key => $label)
                            <div class="mb-2">
                                <label class="form-label mb-0">{{ $label }}</label>
                                <textarea name="{{ $key }}" class="form-control form-control-sm" rows="2">{{ $a5m[$key] ?? '' }}</textarea>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-sm btn-primary mt-1">Enregistrer l'analyse</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Plan d'actions
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAction">
                        <i class="ti ti-plus-lg"></i> Ajouter une action
                    </button>
                </div>
                <div class="card-body">
                    @if ($ficheProgres->actions->isEmpty())
                        <p class="text-muted mb-0">Aucune action.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($ficheProgres->actions as $action)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $action->description }}</strong><br>
                                            <small class="text-muted">Responsable : {{ $action->responsable->nom_complet ?? '-' }} — Échéance : {{ \Carbon\Carbon::parse($action->date_echeance)->format('d/m/Y') }}</small>
                                        </div>
                                        @if (!$action->realisee_le)
                                            <form method="POST" action="{{ route('sav.fiches-progres.actions.realiser', [$ficheProgres->id, $action->id]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success">Réaliser</button>
                                            </form>
                                        @else
                                            <span class="badge bg-success align-self-center">Réalisée</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header">Évaluation d'efficacité</div>
                <div class="card-body">
                    @if ($ficheProgres->statut === 'cloture')
                        <p class="mb-1"><strong>Efficace :</strong> {{ $ficheProgres->efficacite_actions ? 'Oui' : 'Non' }}</p>
                        <p class="mb-0">{{ $ficheProgres->commentaire_efficacite }}</p>
                    @else
                        <form method="POST" action="{{ route('sav.fiches-progres.evaluer', $ficheProgres->id) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Actions efficaces ?</label>
                                <select name="efficacite_actions" class="form-select form-select-sm">
                                    <option value="1">Oui</option>
                                    <option value="0">Non</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Commentaire *</label>
                                <textarea name="commentaire_efficacite" class="form-control form-control-sm" rows="2" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Évaluer</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            Pièces jointes
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalPiece">
                <i class="ti ti-paperclip"></i> Ajouter
            </button>
        </div>
        <div class="card-body">
            @if ($ficheProgres->piecesJointes->isEmpty())
                <p class="text-muted mb-0">Aucune pièce jointe.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach ($ficheProgres->piecesJointes as $pj)
                        <li class="list-group-item">
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($pj->chemin_fichier) }}" target="_blank">{{ $pj->filename }}</a>
                            <small class="text-muted ms-2">{{ $pj->description }} — {{ $pj->uploadedBy->nom_complet ?? '-' }}</small>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="modal fade" id="modalAction" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('sav.fiches-progres.actions.add', $ficheProgres->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter une action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Description *</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Responsable *</label>
                            <select name="responsable_id" class="form-select" required>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->nom_complet }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Date d'échéance *</label>
                            <input type="date" name="date_echeance" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPiece" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('sav.fiches-progres.upload', $ficheProgres->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter une pièce jointe</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Fichier *</label>
                            <input type="file" name="piece_jointe" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Type *</label>
                            <select name="type_piece" class="form-select" required>
                                <option value="photo">Photo</option>
                                <option value="document">Document</option>
                                <option value="capture_ecran">Capture d'écran</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
