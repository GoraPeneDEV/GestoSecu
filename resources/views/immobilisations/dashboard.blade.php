@extends('layouts.app')

@section('title', 'Immobilisations — Tableau de bord')

@section('content')
    <h3 class="mb-4">Immobilisations — Tableau de bord</h3>

    <div class="row g-3 mb-3">
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total_biens'] }}</h4><small class="text-muted">Biens</small>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body">
                <h5 class="mb-0">{{ number_format($stats['valeur_totale'], 0, ',', ' ') }}</h5><small class="text-muted">Valeur acquisition (FCFA)</small>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body">
                <h5 class="mb-0">{{ number_format($stats['valeur_nette_totale'], 0, ',', ' ') }}</h5><small class="text-muted">Valeur nette (FCFA)</small>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['biens_affectes'] }}</h4><small class="text-muted">Affectés</small>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['biens_en_stock'] }}</h4><small class="text-muted">En stock</small>
            </div></div>
        </div>
        <div class="col-md-2">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['biens_en_reparation'] }}</h4><small class="text-muted">En réparation</small>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Répartition par catégorie</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Catégorie</th><th>Biens</th><th>Valeur</th></tr></thead>
                        <tbody>
                            @foreach ($repartitionCategories as $cat)
                                <tr>
                                    <td>{{ $cat->libelle }}</td>
                                    <td>{{ $cat->immobilisations_count }}</td>
                                    <td>{{ number_format($cat->immobilisations_sum_valeur_acquisition ?? 0, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Répartition par site</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Site</th><th>Biens</th><th>Valeur</th></tr></thead>
                        <tbody>
                            @foreach ($repartitionSites as $site)
                                <tr>
                                    <td>{{ $site->libelle }}</td>
                                    <td>{{ $site->immobilisations_count }}</td>
                                    <td>{{ number_format($site->immobilisations_sum_valeur_acquisition ?? 0, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Biens récemment acquis (30 derniers jours)</div>
        <div class="card-body p-0">
            @if ($biensRecents->isEmpty())
                <p class="text-muted p-3 mb-0">Aucun bien récent.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Code</th><th>Désignation</th><th>Catégorie</th><th>Site</th><th>Date d'acquisition</th></tr></thead>
                    <tbody>
                        @foreach ($biensRecents as $bien)
                            <tr>
                                <td><a href="{{ route('immobilisations.biens.show', $bien->id) }}">{{ $bien->code_interne }}</a></td>
                                <td>{{ $bien->designation }}</td>
                                <td>{{ $bien->categorie->libelle ?? '-' }}</td>
                                <td>{{ $bien->site->libelle ?? '-' }}</td>
                                <td>{{ $bien->date_acquisition->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
