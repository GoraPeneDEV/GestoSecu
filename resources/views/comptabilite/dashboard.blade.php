@extends('layouts.contentNavbarLayout')

@section('title', 'Tableau de bord Comptabilité')

@section('content')
    <h3 class="mb-4">Tableau de bord Comptabilité</h3>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-0">Mois</label>
                    <select name="mois" class="form-select form-select-sm">
                        @foreach (['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'] as $i => $nom)
                            <option value="{{ $i + 1 }}" @selected(($i + 1) == $mois)>{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">Année</label>
                    <input type="number" name="annee" class="form-control form-control-sm" value="{{ $annee }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['nb_bulletins'] }}</h4><small class="text-muted">Bulletins</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h5 class="mb-0">{{ number_format($stats['masse_salariale_brute'], 0, ',', ' ') }}</h5><small class="text-muted">Masse brute</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h5 class="mb-0">{{ number_format($stats['masse_salariale_nette'], 0, ',', ' ') }}</h5><small class="text-muted">Masse nette</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h5 class="mb-0">{{ number_format($stats['total_cotisations_patronales'], 0, ',', ' ') }}</h5><small class="text-muted">Charges patronales</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-secondary">{{ $stats['brouillons'] }}</h4><small class="text-muted">Brouillons</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-success">{{ $stats['valides'] }}</h4><small class="text-muted">Validés</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Exports comptables</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="{{ route('comptabilite.exports.livre-paie', ['mois' => $mois, 'annee' => $annee]) }}" class="btn btn-outline-primary w-100">
                        <i class="ti ti-book"></i> Livre de paie
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('comptabilite.exports.rapport-masse-salariale', ['mois' => $mois, 'annee' => $annee]) }}" class="btn btn-outline-primary w-100">
                        <i class="ti ti-report-money"></i> Rapport masse salariale
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('comptabilite.exports.virements-bancaires', ['mois' => $mois, 'annee' => $annee]) }}" class="btn btn-outline-primary w-100">
                        <i class="ti ti-building-bank"></i> Virements bancaires (CSV)
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('comptabilite.exports.declaration-ipres', ['mois' => $mois, 'annee' => $annee]) }}" class="btn btn-outline-primary w-100">
                        <i class="ti ti-file-invoice"></i> Déclaration IPRES
                    </a>
                </div>
            </div>
            <p class="text-muted small mt-3 mb-0">Exports générés pour la période sélectionnée ci-dessus.</p>
        </div>
    </div>
@endsection
