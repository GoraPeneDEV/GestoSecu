@extends('layouts.app')

@section('title', $employe->prenom . ' ' . $employe->nom)

@section('content')
    <a href="{{ route('employes.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-center gap-3">
            @if ($employe->photo)
                <img src="{{ asset('storage/' . $employe->photo) }}" class="rounded-circle" width="64" height="64">
            @else
                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white" style="width:64px;height:64px;">
                    {{ substr($employe->prenom, 0, 1) }}{{ substr($employe->nom, 0, 1) }}
                </div>
            @endif
            <div>
                <h3 class="mb-0">{{ $employe->prenom }} {{ $employe->nom }}</h3>
                <small class="text-muted">{{ $employe->matricule }} — {{ $employe->fonction }}</small>
            </div>
        </div>
        <a href="{{ route('employes.edit', $employe->id) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Modifier
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Informations personnelles</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Département</dt>
                        <dd class="col-7">{{ $employe->departement->nom ?? '-' }}</dd>
                        <dt class="col-5">Sexe</dt>
                        <dd class="col-7">{{ $employe->sexe }}</dd>
                        <dt class="col-5">Date de naissance</dt>
                        <dd class="col-7">{{ $employe->date_naissance?->format('d/m/Y') ?? '-' }}</dd>
                        <dt class="col-5">Lieu de naissance</dt>
                        <dd class="col-7">{{ $employe->lieu_naissance ?? '-' }}</dd>
                        <dt class="col-5">Téléphone</dt>
                        <dd class="col-7">{{ $employe->telephone ?? '-' }}</dd>
                        <dt class="col-5">Adresse</dt>
                        <dd class="col-7">{{ $employe->adresse ?? '-' }}</dd>
                        <dt class="col-5">Situation matrimoniale</dt>
                        <dd class="col-7">{{ $employe->situation_matrimoniale ?? '-' }}</dd>
                        <dt class="col-5">CNI</dt>
                        <dd class="col-7">{{ $employe->cni ?? '-' }}</dd>
                        <dt class="col-5">Solde congés</dt>
                        <dd class="col-7">{{ $employe->solde_conges ?? 0 }} jours</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Contrats</div>
                <div class="card-body">
                    @if ($employe->contrats->isEmpty())
                        <p class="text-muted mb-0">Aucun contrat enregistré.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Type</th><th>Début</th><th>Fin</th><th>Montant</th></tr></thead>
                                <tbody>
                                    @foreach ($employe->contrats as $contrat)
                                        <tr>
                                            <td>{{ $contrat->type_contrat }}</td>
                                            <td>{{ $contrat->date_debut?->format('d/m/Y') }}</td>
                                            <td>{{ $contrat->date_prevu_fin?->format('d/m/Y') ?? '-' }}</td>
                                            <td>{{ number_format($contrat->montant, 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Plannings (sites)</div>
                <div class="card-body">
                    @if ($employe->plannings->isEmpty())
                        <p class="text-muted mb-0">Aucun planning enregistré.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($employe->plannings as $planning)
                                <li class="list-group-item d-flex justify-content-between">
                                    {{ $planning->site->nom_site ?? '-' }}
                                    <span class="text-muted small">{{ $planning->date_debut?->format('d/m/Y') }} - {{ $planning->date_fin?->format('d/m/Y') ?? 'en cours' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Documents</div>
                <div class="card-body">
                    @if ($employe->documents->isEmpty())
                        <p class="text-muted mb-0">Aucun document.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($employe->documents as $document)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $document->type_document }} — {{ $document->nom_fichier }}
                                    <a href="{{ route('employes.documents.download', [$employe->id, $document->id]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Famille</div>
                <div class="card-body">
                    <p class="fw-bold mb-1">Épouse(s)</p>
                    @if ($employe->epouses->isEmpty())
                        <p class="text-muted small">Aucune.</p>
                    @else
                        <ul class="mb-3">
                            @foreach ($employe->epouses as $epouse)
                                <li>{{ $epouse->nom_complet }} — {{ $epouse->telephone ?? '-' }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="fw-bold mb-1">Enfant(s)</p>
                    @if ($employe->enfants->isEmpty())
                        <p class="text-muted small">Aucun.</p>
                    @else
                        <ul class="mb-0">
                            @foreach ($employe->enfants as $enfant)
                                <li>{{ $enfant->nom_complet }} — {{ $enfant->date_naissance?->format('d/m/Y') ?? '-' }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Demandes d'absence</div>
                <div class="card-body">
                    @if ($employe->demandesAbsencesAdmin->isEmpty())
                        <p class="text-muted mb-0">Aucune demande.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($employe->demandesAbsencesAdmin->take(5) as $demande)
                                <li class="list-group-item d-flex justify-content-between">
                                    {{ $demande->type_conges }} ({{ $demande->date_debut?->format('d/m/Y') }} - {{ $demande->date_fin?->format('d/m/Y') }})
                                    <span class="badge bg-secondary">{{ $demande->statut }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
