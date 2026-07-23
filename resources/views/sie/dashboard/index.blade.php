@extends('layouts.contentNavbarLayout')

@section('title', 'Tableau de bord SIE')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Tableau de bord SIE</h3>
        <a href="{{ route('sie.rapport') }}" class="btn btn-outline-secondary">
            <i class="ti ti-report"></i> Rapport de ronde par période
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['rondes_aujourdhui'] }}</h4><small class="text-muted">Rondes aujourd'hui</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-warning">{{ $stats['rondes_en_cours'] }}</h4><small class="text-muted">En cours</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['rondes_semaine'] }}</h4><small class="text-muted">Rondes (semaine)</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-danger">{{ $stats['anomalies_ouvertes'] }}</h4><small class="text-muted">Anomalies</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['points_controle_actifs'] }}</h4><small class="text-muted">Points de contrôle actifs</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['visites_supervision_semaine'] }}</h4><small class="text-muted">Visites sup. (semaine)</small>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Rondes récentes</div>
                <div class="card-body p-0">
                    @if ($rondesRecentes->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucune ronde enregistrée.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Agent</th><th>Site</th><th>Statut</th><th>Anomalies</th></tr></thead>
                            <tbody>
                                @foreach ($rondesRecentes as $ronde)
                                    <tr>
                                        <td><a href="{{ route('sie.rondes.show', $ronde->id) }}">{{ $ronde->agent->prenom ?? '' }} {{ $ronde->agent->nom ?? '-' }}</a></td>
                                        <td>{{ $ronde->planningRonde->site->nom_site ?? '-' }}</td>
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
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Visites de supervision récentes</div>
                <div class="card-body p-0">
                    @if ($visitesRecentes->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucune visite enregistrée.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Site</th><th>Superviseur</th><th>Statut</th></tr></thead>
                            <tbody>
                                @foreach ($visitesRecentes as $visite)
                                    <tr>
                                        <td>{{ $visite->site->nom_site ?? '-' }}</td>
                                        <td>{{ $visite->supervisor->prenom ?? '' }} {{ $visite->supervisor->nom ?? '-' }}</td>
                                        <td><span class="badge bg-secondary">{{ $visite->status }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
