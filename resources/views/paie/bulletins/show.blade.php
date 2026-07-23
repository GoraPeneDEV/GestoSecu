@extends('layouts.contentNavbarLayout')

@section('title', 'Bulletin ' . $bulletin->numero_bulletin)

@section('content')
    <a href="{{ route('paie.bulletins.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Bulletin {{ $bulletin->numero_bulletin }} — {{ $bulletin->employe->prenom }} {{ $bulletin->employe->nom }}</h3>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ number_format($bulletin->salaire_brut, 0, ',', ' ') }}</h4>
                <small class="text-muted">Salaire brut (FCFA)</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ number_format($bulletin->total_cotisations_salariales + $bulletin->impot_revenu, 0, ',', ' ') }}</h4>
                <small class="text-muted">Total retenues</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-success">{{ number_format($bulletin->salaire_net_a_payer, 0, ',', ' ') }}</h4>
                <small class="text-muted">Net à payer (FCFA)</small>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Détail du bulletin</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Élément</th><th>Base</th><th>Taux</th><th>Nombre</th><th class="text-end">Montant</th></tr></thead>
                    <tbody>
                        @foreach ($bulletin->lignes as $ligne)
                            <tr>
                                <td>{{ $ligne->libelle }}</td>
                                <td>{{ $ligne->base_calcul ? number_format($ligne->base_calcul, 0, ',', ' ') : '-' }}</td>
                                <td>{{ $ligne->taux ? $ligne->taux . '%' : '-' }}</td>
                                <td>{{ $ligne->nombre ?? '-' }}</td>
                                <td class="text-end">{{ number_format($ligne->montant, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Récapitulatif</div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-6">Salaire de base</dt><dd class="col-6 text-end">{{ number_format($bulletin->salaire_base, 0, ',', ' ') }}</dd>
                <dt class="col-6">Total gains</dt><dd class="col-6 text-end">{{ number_format($bulletin->total_gains, 0, ',', ' ') }}</dd>
                <dt class="col-6">Cotisations IPRES (salariale)</dt><dd class="col-6 text-end">{{ number_format($bulletin->cotisation_ipres, 0, ',', ' ') }}</dd>
                <dt class="col-6">Cotisations CSS (salariale)</dt><dd class="col-6 text-end">{{ number_format($bulletin->cotisation_css, 0, ',', ' ') }}</dd>
                <dt class="col-6">Cotisation IPM</dt><dd class="col-6 text-end">{{ number_format($bulletin->cotisation_ipm, 0, ',', ' ') }}</dd>
                <dt class="col-6">TRIMF</dt><dd class="col-6 text-end">{{ number_format($bulletin->trimf, 0, ',', ' ') }}</dd>
                <dt class="col-6">Impôt sur le revenu</dt><dd class="col-6 text-end">{{ number_format($bulletin->impot_revenu, 0, ',', ' ') }}</dd>
                <dt class="col-6 fw-bold">Net à payer</dt><dd class="col-6 text-end fw-bold">{{ number_format($bulletin->salaire_net_a_payer, 0, ',', ' ') }}</dd>
            </dl>
        </div>
    </div>
@endsection
