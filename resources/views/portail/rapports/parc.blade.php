@extends('layouts.contentNavbarLayout')

@section('title', 'Rapport Parc')

@section('content')
    <a href="{{ route('portail.rapports.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Rapport Parc / Équipements</h3>
        <a href="{{ route('portail.rapports.parc.export') }}" class="btn btn-outline-secondary">
            <i class="ti ti-file-pdf"></i> Exporter PDF
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $assets->count() }}</h4><small class="text-muted">Équipements</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-success">{{ $parStatut['fonctionnel'] ?? 0 }}</h4><small class="text-muted">Fonctionnels</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-warning">{{ ($parStatut['panne'] ?? 0) + ($parStatut['maintenance_requise'] ?? 0) }}</h4><small class="text-muted">En maintenance / panne</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-secondary">{{ $parStatut['hors_service'] ?? 0 }}</h4><small class="text-muted">Hors service</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Détail du parc</div>
        <div class="card-body p-0">
            @if ($assets->isEmpty())
                <p class="text-muted p-3 mb-0">Aucun équipement.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Site</th><th>Type</th><th>Libellé</th><th>Statut</th></tr></thead>
                    <tbody>
                        @foreach ($assets as $asset)
                            <tr>
                                <td>{{ $asset->site->nom_site ?? '-' }}</td>
                                <td>{{ $asset->type }}</td>
                                <td>{{ $asset->label ?? '-' }}</td>
                                <td>
                                    @php
                                        $badges = ['fonctionnel' => 'bg-success', 'maintenance_requise' => 'bg-warning', 'panne' => 'bg-danger', 'hors_service' => 'bg-secondary'];
                                    @endphp
                                    <span class="badge {{ $badges[$asset->status] ?? 'bg-primary' }}">{{ str_replace('_', ' ', ucfirst($asset->status)) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
