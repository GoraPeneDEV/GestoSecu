@extends('layouts.contentNavbarLayout')

@section('title', 'Rapport Agents')

@section('content')
    <a href="{{ route('portail.rapports.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Rapport Agents</h3>
        <a href="{{ route('portail.rapports.agents.export') }}" class="btn btn-outline-secondary">
            <i class="ti ti-file-pdf"></i> Exporter PDF
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $agents->count() }}</h4><small class="text-muted">Agents affectés</small>
            </div></div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $agents->pluck('plannings')->flatten()->pluck('site.id')->unique()->count() }}</h4><small class="text-muted">Sites couverts</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Détail des agents</div>
        <div class="card-body p-0">
            @if ($agents->isEmpty())
                <p class="text-muted p-3 mb-0">Aucun agent affecté.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Matricule</th><th>Agent</th><th>Fonction</th><th>Sites</th></tr></thead>
                    <tbody>
                        @foreach ($agents as $agent)
                            <tr>
                                <td>{{ $agent->matricule }}</td>
                                <td>{{ $agent->prenom }} {{ $agent->nom }}</td>
                                <td>{{ $agent->fonction ?? '-' }}</td>
                                <td>
                                    @foreach ($agent->plannings->pluck('site.nom_site')->filter()->unique() as $nomSite)
                                        <span class="badge bg-info me-1">{{ $nomSite }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
