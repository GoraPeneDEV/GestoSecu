@extends('layouts.contentNavbarLayout')

@section('title', 'Tableau de bord')

@section('content')
    <h3 class="mb-4">Tableau de bord</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="ti ti-building fs-2 text-primary"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['totalSites'] }}</h3>
                    <small class="text-muted">Sites</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="ti ti-check-circle fs-2 text-success"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['sitesActifs'] }}</h3>
                    <small class="text-muted">Sites actifs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="ti ti-tools fs-2 text-warning"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['totalEquipements'] }}</h3>
                    <small class="text-muted">Équipements</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="ti ti-exclamation-triangle fs-2 text-danger"></i>
                    <h3 class="mt-2 mb-0">{{ $stats['equipementsMaintenance'] + $stats['equipementsHS'] }}</h3>
                    <small class="text-muted">Équipements à surveiller</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Répartition par type de site</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            Gardiennage <span class="badge bg-primary">{{ $stats['sitesGardiennage'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Nettoyage <span class="badge bg-success">{{ $stats['sitesNettoyage'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            Mixte <span class="badge bg-warning">{{ $stats['sitesMixtes'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Répartition par région (top 5)</div>
                <div class="card-body">
                    @if ($sitesParRegion->isEmpty())
                        <p class="text-muted mb-0">Aucune donnée disponible.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($sitesParRegion as $region)
                                <li class="list-group-item d-flex justify-content-between">
                                    {{ $region->region ?? 'Non renseignée' }}
                                    <span class="badge bg-secondary">{{ $region->total }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Sites récents</div>
        <div class="card-body">
            @if ($sitesRecents->isEmpty())
                <p class="text-muted mb-0">Aucun site pour le moment.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Localisation</th>
                                <th>Date de début</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sitesRecents as $site)
                                <tr>
                                    <td><a href="{{ route('portail.sites.show', $site->id) }}">{{ $site->nom_site }}</a></td>
                                    <td>{{ ucfirst($site->type_site) }}</td>
                                    <td>{{ $site->localisation }}</td>
                                    <td>{{ $site->date_debut?->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
