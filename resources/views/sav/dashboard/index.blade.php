@extends('layouts.contentNavbarLayout')

@section('title', 'Tableau de bord SAV')

@section('content')
    <h3 class="mb-4">Tableau de bord SAV</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total_clients'] }}</h4><small class="text-muted">Clients actifs</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-warning">{{ $stats['fiches_en_cours'] }}</h4><small class="text-muted">Fiches en cours</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-success">{{ $stats['contrats_actifs'] }}</h4><small class="text-muted">Contrats actifs</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-danger">{{ $stats['contrats_expirant'] }}</h4><small class="text-muted">Contrats expirant (30j)</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['rappels_a_faire'] }}</h4><small class="text-muted">Rappels à faire</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['maintenances_mois'] }}</h4><small class="text-muted">Maintenances (mois)</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['interventions_realisees'] }}</h4><small class="text-muted">Interventions réalisées</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total_appareils'] }}</h4><small class="text-muted">Équipements parc</small>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Fiches de progrès récentes</div>
                <div class="card-body">
                    @if ($fichesRecentes->isEmpty())
                        <p class="text-muted mb-0">Aucune fiche récente.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($fichesRecentes as $fiche)
                                <li class="list-group-item d-flex justify-content-between">
                                    <a href="{{ route('sav.fiches-progres.show', $fiche->id) }}">{{ $fiche->client->nomClient ?? '-' }} — {{ $fiche->type }}</a>
                                    <span class="badge bg-secondary">{{ $fiche->statut }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Contrats expirant sous 30 jours</div>
                <div class="card-body">
                    @if ($contratsExpirant->isEmpty())
                        <p class="text-muted mb-0">Aucun contrat expirant.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($contratsExpirant as $contrat)
                                <li class="list-group-item d-flex justify-content-between">
                                    <a href="{{ route('sav.contrats.show', $contrat->id) }}">{{ $contrat->client->nomClient ?? '-' }} — {{ $contrat->numero_contrat }}</a>
                                    <span class="text-muted small">{{ $contrat->date_fin?->format('d/m/Y') }}</span>
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
                <div class="card-header">Actions en retard</div>
                <div class="card-body">
                    @if ($actionsEnRetard->isEmpty())
                        <p class="text-muted mb-0">Aucune action en retard.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($actionsEnRetard as $action)
                                <li class="list-group-item">
                                    {{ $action->description }}
                                    <span class="text-muted small d-block">{{ $action->responsable->nom_complet ?? '-' }} — échéance {{ $action->date_echeance?->format('d/m/Y') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Activité récente (interactions clients)</div>
                <div class="card-body">
                    @if ($timeline->isEmpty())
                        <p class="text-muted mb-0">Aucune interaction récente.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($timeline->take(10) as $interaction)
                                <li class="list-group-item">
                                    {{ $interaction->client->nomClient ?? '-' }} — {{ $interaction->type }}
                                    <span class="text-muted small d-block">{{ $interaction->created_at->format('d/m/Y H:i') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
