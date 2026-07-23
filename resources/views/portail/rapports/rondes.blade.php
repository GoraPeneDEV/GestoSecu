@extends('layouts.contentNavbarLayout')

@section('title', 'Rapport Rondes')

@section('content')
    <a href="{{ route('portail.rapports.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Rapport Rondes</h3>
        <a href="{{ route('portail.rapports.rondes.export', request()->query()) }}" class="btn btn-outline-secondary">
            <i class="ti ti-file-pdf"></i> Exporter PDF
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('portail.rapports.rondes') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0">Du</label>
                    <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ $dateDebut }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Au</label>
                    <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ $dateFin }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Site</label>
                    <select name="site_id" class="form-select form-select-sm">
                        <option value="">Tous les sites</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}" @selected($siteId == $site->id)>{{ $site->nom_site }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $totalRondes }}</h4><small class="text-muted">Rondes</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-success">{{ $rondesTerminees }}</h4><small class="text-muted">Terminées</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $tauxCompletion }}%</h4><small class="text-muted">Taux de complétion</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-danger">{{ $totalAnomalies }}</h4><small class="text-muted">Anomalies</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Détail des rondes</div>
        <div class="card-body p-0">
            @if ($rondes->isEmpty())
                <p class="text-muted p-3 mb-0">Aucune ronde sur la période.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Agent</th><th>Site</th><th>Début</th><th>Fin</th><th>Statut</th><th>Anomalies</th></tr></thead>
                    <tbody>
                        @foreach ($rondes as $ronde)
                            <tr>
                                <td>{{ $ronde->agent->prenom ?? '' }} {{ $ronde->agent->nom ?? '-' }}</td>
                                <td>{{ $ronde->planningRonde->site->nom_site ?? '-' }}</td>
                                <td>{{ $ronde->date_debut?->format('d/m/Y H:i') }}</td>
                                <td>{{ $ronde->date_fin?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>
                                    @if ($ronde->statut === 'en_cours')
                                        <span class="badge bg-warning">En cours</span>
                                    @else
                                        <span class="badge bg-success">Terminée</span>
                                    @endif
                                </td>
                                <td>{{ $ronde->scans->where('anomalie', true)->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
