@extends('layouts.contentNavbarLayout')

@section('title', 'Rapport Sites')

@section('content')
    <a href="{{ route('portail.rapports.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Rapport Sites</h3>
        <a href="{{ route('portail.rapports.sites.export', request()->query()) }}" class="btn btn-outline-secondary">
            <i class="ti ti-file-pdf"></i> Exporter PDF
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('portail.rapports.sites') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0">Du</label>
                    <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ $dateDebut }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Au</label>
                    <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ $dateFin }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $sites->count() }}</h4><small class="text-muted">Sites (période)</small>
            </div></div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-success">{{ $sites->whereNull('date_arret')->count() }}</h4><small class="text-muted">Actifs</small>
            </div></div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-secondary">{{ $sites->whereNotNull('date_arret')->count() }}</h4><small class="text-muted">Archivés</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Détail des sites</div>
        <div class="card-body p-0">
            @if ($sites->isEmpty())
                <p class="text-muted p-3 mb-0">Aucun site sur la période.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Site</th><th>Type</th><th>Région</th><th>Début</th><th>Statut</th></tr></thead>
                    <tbody>
                        @foreach ($sites as $site)
                            <tr>
                                <td>{{ $site->nom_site }}</td>
                                <td>{{ $site->type_site ?? '-' }}</td>
                                <td>{{ $site->region ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($site->date_debut)->format('d/m/Y') }}</td>
                                <td>
                                    @if ($site->date_arret)
                                        <span class="badge bg-secondary">Archivé</span>
                                    @else
                                        <span class="badge bg-success">Actif</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
