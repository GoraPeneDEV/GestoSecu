@extends('layouts.contentNavbarLayout')

@section('title', 'Tableau de bord Achats & Logistique')

@section('content')
    <h3 class="mb-4">Tableau de bord Achats & Logistique</h3>

    <div class="row g-3 mb-3">
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total_articles'] }}</h4><small class="text-muted">Articles</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-danger">{{ $stats['articles_sous_stock'] }}</h4><small class="text-muted">Sous stock minimum</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h5 class="mb-0">{{ number_format($stats['valeur_stock'], 0, ',', ' ') }}</h5><small class="text-muted">Valeur du stock (FCFA)</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['dotations_mois'] }}</h4><small class="text-muted">Dotations (mois)</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total_biens'] }}</h4><small class="text-muted">Biens immobilisés</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h5 class="mb-0">{{ number_format($stats['valeur_biens'], 0, ',', ' ') }}</h5><small class="text-muted">Valeur des biens (FCFA)</small>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5><i class="ti ti-box"></i> Articles</h5>
                    <p class="text-muted">Gestion du stock, inventaire, dotations.</p>
                    <div class="mt-auto d-flex gap-2">
                        <a href="{{ route('articles.index') }}" class="btn btn-sm btn-primary">Articles</a>
                        <a href="{{ route('dotations.index') }}" class="btn btn-sm btn-outline-primary">Dotations</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5><i class="ti ti-building-warehouse"></i> Immobilisations</h5>
                    <p class="text-muted">Biens, catégories, sites, amortissement.</p>
                    <a href="{{ route('immobilisations.dashboard') }}" class="btn btn-sm btn-primary mt-auto align-self-start">Immobilisations</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">Biens par statut</div>
                <div class="card-body p-0">
                    @if ($biensParStatut->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucune donnée.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach ($biensParStatut as $ligne)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $ligne->statut)) }}</td>
                                        <td class="text-end">{{ $ligne->total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Articles sous le stock minimum</div>
                <div class="card-body p-0">
                    @if ($articlesSousStock->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucun article en alerte.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Article</th><th>Département</th><th>Stock</th></tr></thead>
                            <tbody>
                                @foreach ($articlesSousStock as $article)
                                    <tr>
                                        <td>{{ $article->designation }}</td>
                                        <td>{{ $article->departement->nom ?? '-' }}</td>
                                        <td class="text-danger">{{ $article->stock_actuel }} / {{ $article->stock_minimum }}</td>
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
                <div class="card-header">Dotations récentes</div>
                <div class="card-body p-0">
                    @if ($dotationsRecentes->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucune dotation récente.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Référence</th><th>Bénéficiaire</th><th>Date</th></tr></thead>
                            <tbody>
                                @foreach ($dotationsRecentes as $dotation)
                                    <tr>
                                        <td><a href="{{ route('dotations.show', $dotation->id) }}">{{ $dotation->reference }}</a></td>
                                        <td>
                                            @if ($dotation->site)
                                                {{ $dotation->site->nom_site }}
                                            @elseif ($dotation->employe)
                                                {{ $dotation->employe->prenom }} {{ $dotation->employe->nom }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $dotation->date_dotation->format('d/m/Y') }}</td>
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
