@extends('layouts.contentNavbarLayout')

@section('title', 'Intervention ' . $intervention->numero_intervention)

@section('content')
    <a href="{{ route('sav.interventions.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">{{ $intervention->numero_intervention }} — {{ $intervention->site->nom_site ?? '' }}</h3>
        <div>
            <a href="{{ route('sav.interventions.edit', $intervention->id) }}" class="btn btn-warning btn-sm">
                <i class="ti ti-pencil"></i> Modifier
            </a>
            <a href="{{ route('sav.interventions.pdf', $intervention->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Détails</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Client</dt><dd class="col-7">{{ $intervention->site->client->nomClient ?? '-' }}</dd>
                        <dt class="col-5">Type</dt><dd class="col-7">{{ ucfirst(str_replace('_', ' ', $intervention->type)) }}</dd>
                        <dt class="col-5">Technicien</dt><dd class="col-7">{{ $intervention->technicien->nom_complet ?? '-' }}</dd>
                        <dt class="col-5">Date</dt><dd class="col-7">{{ $intervention->date_intervention?->format('d/m/Y') }}</dd>
                        <dt class="col-5">Maintenance liée</dt><dd class="col-7">{{ $intervention->maintenance?->id ? 'Oui — ' . \Carbon\Carbon::parse($intervention->maintenance->date_prevue)->format('d/m/Y') : 'Non' }}</dd>
                        <dt class="col-5">Statut</dt><dd class="col-7"><span class="badge bg-success">{{ ucfirst($intervention->statut) }}</span></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Recommandations générales</div>
                <div class="card-body">
                    <p class="mb-0">{{ $intervention->recommandations_generales ?? 'Aucune.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Appareils concernés</div>
        <div class="card-body">
            @if ($intervention->assets->isEmpty())
                <p class="text-muted mb-0">Aucun appareil enregistré pour cette intervention.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Appareil</th>
                                <th>Actions faites</th>
                                <th>Recommandation</th>
                                <th>Statut après</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($intervention->assets as $asset)
                                <tr>
                                    <td>{{ $asset->label ?? $asset->type }}</td>
                                    <td>{{ $asset->pivot->actions_faites ?? '-' }}</td>
                                    <td>{{ $asset->pivot->recommandation_specifique ?? '-' }}</td>
                                    <td>{{ $asset->pivot->statut_apres ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
