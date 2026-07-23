@extends('layouts.contentNavbarLayout')

@section('title', 'Tableau de bord Paie')

@section('content')
    <h3 class="mb-4">Tableau de bord Paie — {{ \Carbon\Carbon::create($anneeActuelle, $moisActuel, 1)->translatedFormat('F Y') }}</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ number_format($stats['masse_salariale_brute'], 0, ',', ' ') }}</h4>
                <small class="text-muted">Masse salariale brute (FCFA)</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ number_format($stats['masse_salariale_nette'], 0, ',', ' ') }}</h4>
                <small class="text-muted">Masse salariale nette (FCFA)</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['nb_employes_payes'] }}</h4>
                <small class="text-muted">Employés payés</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 {{ $stats['evolution_pourcentage'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $stats['evolution_pourcentage'] >= 0 ? '+' : '' }}{{ $stats['evolution_pourcentage'] }}%
                </h4>
                <small class="text-muted">Évolution vs mois précédent</small>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Cotisations &amp; charges (ce mois)</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-7">Cotisations salariales</dt>
                        <dd class="col-5 text-end">{{ number_format($stats['total_cotisations_salariales'], 0, ',', ' ') }}</dd>
                        <dt class="col-7">Cotisations patronales</dt>
                        <dd class="col-5 text-end">{{ number_format($stats['total_cotisations_patronales'], 0, ',', ' ') }}</dd>
                        <dt class="col-7">Impôts sur le revenu</dt>
                        <dd class="col-5 text-end">{{ number_format($stats['total_impots'], 0, ',', ' ') }}</dd>
                        <dt class="col-7 fw-bold">Coût total employeur</dt>
                        <dd class="col-5 text-end fw-bold">{{ number_format($stats['cout_total_employeur'], 0, ',', ' ') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Répartition des cotisations</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">IPRES <span>{{ number_format($repartitionCotisations['ipres'], 0, ',', ' ') }}</span></li>
                        <li class="list-group-item d-flex justify-content-between">CSS <span>{{ number_format($repartitionCotisations['css'], 0, ',', ' ') }}</span></li>
                        <li class="list-group-item d-flex justify-content-between">IPM <span>{{ number_format($repartitionCotisations['ipm'], 0, ',', ' ') }}</span></li>
                        <li class="list-group-item d-flex justify-content-between">TRIMF <span>{{ number_format($repartitionCotisations['trimf'], 0, ',', ' ') }}</span></li>
                        <li class="list-group-item d-flex justify-content-between">CFCE <span>{{ number_format($repartitionCotisations['cfce'], 0, ',', ' ') }}</span></li>
                        <li class="list-group-item d-flex justify-content-between">IR <span>{{ number_format($repartitionCotisations['ir'], 0, ',', ' ') }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Évolution de la masse salariale ({{ $anneeActuelle }})</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr>
                        @foreach ($evolutionMasseSalariale as $mois)
                            <th class="text-center">{{ $mois['mois'] }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody><tr>
                        @foreach ($evolutionMasseSalariale as $mois)
                            <td class="text-center small">{{ number_format($mois['brut'], 0, ',', ' ') }}</td>
                        @endforeach
                    </tr></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
