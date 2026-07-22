@extends('portail.layouts.app')

@section('title', 'Ronde #' . $ronde->id)

@section('content')
    <a href="{{ route('portail.rondes.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour aux rondes
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">Ronde — {{ $ronde->planningRonde->nom ?? '-' }}</h3>
        @if ($ronde->scans->where('anomalie', true)->count() > 0)
            <a href="{{ route('portail.rondes.export-anomalies', $ronde->id) }}" class="btn btn-danger btn-sm">
                <i class="bi bi-file-earmark-arrow-down"></i> Exporter les anomalies (PDF)
            </a>
        @endif
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Informations</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-4">Site</dt>
                        <dd class="col-8">{{ $ronde->planningRonde->site->nom_site ?? '-' }}</dd>
                        <dt class="col-4">Agent</dt>
                        <dd class="col-8">{{ $ronde->agent->prenom ?? '' }} {{ $ronde->agent->nom ?? '' }}</dd>
                        <dt class="col-4">Début</dt>
                        <dd class="col-8">{{ $ronde->date_debut?->format('d/m/Y H:i') }}</dd>
                        <dt class="col-4">Fin</dt>
                        <dd class="col-8">{{ $ronde->date_fin?->format('d/m/Y H:i') ?? 'En cours' }}</dd>
                        <dt class="col-4">Statut</dt>
                        <dd class="col-8">
                            <span class="badge {{ $ronde->statut === 'terminee' ? 'bg-success' : 'bg-warning' }}">
                                {{ $ronde->statut === 'terminee' ? 'Terminée' : 'En cours' }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Points de contrôle</div>
                <div class="card-body">
                    @php($totalPoints = $ronde->planningRonde->pointsControle->count() ?? 0)
                    @php($scanned = $ronde->scans->count())
                    <p class="mb-1">{{ $scanned }} / {{ $totalPoints }} points scannés</p>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="width: {{ $totalPoints > 0 ? round($scanned / $totalPoints * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Détail des scans</div>
        <div class="card-body">
            @if ($ronde->scans->isEmpty())
                <p class="text-muted mb-0">Aucun point encore scanné.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Point de contrôle</th>
                                <th>Heure</th>
                                <th>Anomalie</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ronde->scans as $scan)
                                <tr class="{{ $scan->anomalie ? 'table-danger' : '' }}">
                                    <td>{{ $scan->pointControle->nom ?? '-' }}</td>
                                    <td>{{ $scan->date_scan?->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if ($scan->anomalie)
                                            <span class="badge bg-danger">Anomalie</span>
                                        @else
                                            <span class="badge bg-success">OK</span>
                                        @endif
                                    </td>
                                    <td>{{ $scan->commentaire ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
