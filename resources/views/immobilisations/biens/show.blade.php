@extends('layouts.app')

@section('title', $bien->code_interne . ' — ' . $bien->designation)

@section('content')
    <a href="{{ route('immobilisations.biens.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour
    </a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">{{ $bien->code_interne }} — {{ $bien->designation }}</h3>
        <div>
            <a href="{{ route('immobilisations.biens.edit', $bien->id) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            <a href="{{ route('immobilisations.biens.qrcode', $bien->id) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="bi bi-qr-code"></i> QR Code
            </a>
            <a href="{{ route('immobilisations.biens.amortissement', $bien->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-graph-down"></i> Amortissement
            </a>
            <a href="{{ route('immobilisations.biens.historique', $bien->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clock-history"></i> Historique
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Détails</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Catégorie</dt><dd class="col-7">{{ $bien->categorie->libelle ?? '-' }}</dd>
                        <dt class="col-5">Site</dt><dd class="col-7">{{ $bien->site->libelle ?? '-' }}</dd>
                        <dt class="col-5">Emplacement</dt><dd class="col-7">{{ $bien->emplacement->libelle ?? '-' }}</dd>
                        <dt class="col-5">Numéro de série</dt><dd class="col-7">{{ $bien->numero_serie ?? '-' }}</dd>
                        <dt class="col-5">Date d'acquisition</dt><dd class="col-7">{{ $bien->date_acquisition->format('d/m/Y') }}</dd>
                        <dt class="col-5">Valeur d'acquisition</dt><dd class="col-7">{{ number_format($bien->valeur_acquisition, 0, ',', ' ') }} FCFA</dd>
                        <dt class="col-5">Valeur nette actuelle</dt><dd class="col-7">{{ number_format($bien->valeur_actuelle, 0, ',', ' ') }} FCFA</dd>
                        <dt class="col-5">Statut</dt><dd class="col-7">{{ ucfirst(str_replace('_', ' ', $bien->statut)) }}</dd>
                        <dt class="col-5">Détenteur actuel</dt><dd class="col-7">{{ $bien->employe ? $bien->employe->prenom . ' ' . $bien->employe->nom : 'En stock' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    @if ($bien->statut !== 'affecte')
                        <button type="button" class="btn btn-sm btn-success mb-2" data-bs-toggle="modal" data-bs-target="#modalAffecter">
                            <i class="bi bi-person-plus"></i> Affecter à un employé
                        </button>
                    @else
                        <button type="button" class="btn btn-sm btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modalTransferer">
                            <i class="bi bi-arrow-left-right"></i> Transférer
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-2" data-bs-toggle="modal" data-bs-target="#modalRetourner">
                            <i class="bi bi-box-arrow-in-down"></i> Retourner en stock
                        </button>
                    @endif

                    <hr>
                    <h6>Description</h6>
                    <p class="mb-0">{{ $bien->description ?? 'Aucune.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Derniers mouvements</div>
        <div class="card-body p-0">
            @if ($bien->mouvements->isEmpty())
                <p class="text-muted p-3 mb-0">Aucun mouvement.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Type</th><th>Motif</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach ($bien->mouvements->sortByDesc('created_at')->take(10) as $mvt)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $mvt->type_mouvement)) }}</td>
                                <td>{{ $mvt->motif ?? '-' }}</td>
                                <td>{{ $mvt->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="modal fade" id="modalAffecter" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('immobilisations.biens.affecter', $bien->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Affecter à un employé</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Employé *</label>
                            <select name="employe_id" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach ($employes as $employe)
                                    <option value="{{ $employe->id }}">{{ $employe->matricule }} — {{ $employe->prenom }} {{ $employe->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Type d'affectation *</label>
                            <select name="type_affectation" class="form-select" required>
                                <option value="dotation">Dotation</option>
                                <option value="pret">Prêt</option>
                                <option value="service">Service</option>
                                <option value="gardien">Gardien</option>
                                <option value="mission">Mission</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Date d'affectation *</label>
                            <input type="date" name="date_affectation" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Date de fin prévue</label>
                            <input type="date" name="date_fin_prevue" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Motif</label>
                            <textarea name="motif" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Affecter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTransferer" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('immobilisations.biens.transferer', $bien->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Transférer le bien</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Nouvel employé *</label>
                            <select name="nouvel_employe_id" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach ($employes as $employe)
                                    <option value="{{ $employe->id }}">{{ $employe->matricule }} — {{ $employe->prenom }} {{ $employe->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Date de transfert *</label>
                            <input type="date" name="date_transfert" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Motif</label>
                            <textarea name="motif" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Transférer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRetourner" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('immobilisations.biens.retourner', $bien->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Retourner en stock</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Date de retour *</label>
                            <input type="date" name="date_retour" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">État du bien *</label>
                            <select name="etat_retour" class="form-select" required>
                                <option value="bon">Bon état</option>
                                <option value="abime">Abîmé</option>
                                <option value="hors_service">Hors service</option>
                                <option value="perdu">Perdu</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Observation</label>
                            <textarea name="observation" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Confirmer le retour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
