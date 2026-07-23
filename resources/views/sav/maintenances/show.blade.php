@extends('layouts.contentNavbarLayout')

@section('title', 'Maintenance — ' . ($maintenance->site->nom_site ?? ''))

@section('content')
    <a href="{{ route('sav.maintenances.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Maintenance — {{ $maintenance->site->nom_site ?? '' }}</h3>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Détails</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Client</dt><dd class="col-7">{{ $maintenance->contrat->client->nomClient ?? '-' }}</dd>
                        <dt class="col-5">Contrat</dt><dd class="col-7">{{ $maintenance->contrat->numero_contrat ?? '-' }}</dd>
                        <dt class="col-5">Site</dt><dd class="col-7">{{ $maintenance->site->nom_site ?? '-' }}</dd>
                        <dt class="col-5">Date prévue</dt><dd class="col-7">{{ \Carbon\Carbon::parse($maintenance->date_prevue)->format('d/m/Y') }}</dd>
                        <dt class="col-5">Statut</dt><dd class="col-7"><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $maintenance->status)) }}</span></dd>
                        <dt class="col-5">Date de réalisation</dt><dd class="col-7">{{ $maintenance->date_realisation ? \Carbon\Carbon::parse($maintenance->date_realisation)->format('d/m/Y') : '-' }}</dd>
                        <dt class="col-5">Description</dt><dd class="col-7">{{ $maintenance->description ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Interventions liées</div>
                <div class="card-body">
                    @if ($maintenance->interventions->isEmpty())
                        <p class="text-muted mb-0">Aucune intervention enregistrée.</p>
                        <a href="{{ route('sav.interventions.create', ['maintenance_id' => $maintenance->id]) }}" class="btn btn-sm btn-success mt-2">
                            <i class="ti ti-clipboard-check"></i> Créer le rapport d'intervention
                        </a>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($maintenance->interventions as $intervention)
                                <li class="list-group-item">
                                    <a href="{{ route('sav.interventions.show', $intervention->id) }}">{{ $intervention->numero_intervention }} — {{ $intervention->date_intervention->format('d/m/Y') }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
