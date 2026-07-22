@extends('layouts.app')

@section('title', 'Amortissement — ' . $bien->code_interne)

@section('content')
    <a href="{{ route('immobilisations.biens.show', $bien->id) }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour
    </a>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <h3 class="mb-0">Amortissement — {{ $bien->code_interne }} ({{ $bien->designation }})</h3>
        <form method="POST" action="{{ route('immobilisations.biens.recalculer', $bien->id) }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-repeat"></i> Recalculer
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-3">Valeur d'acquisition</dt><dd class="col-3">{{ number_format($bien->valeur_acquisition, 0, ',', ' ') }} FCFA</dd>
                <dt class="col-3">Méthode</dt><dd class="col-3">{{ ucfirst($bien->methode_amortissement) }}</dd>
                <dt class="col-3">Durée</dt><dd class="col-3">{{ $bien->duree_amortissement_annees }} ans</dd>
                <dt class="col-3">Valeur nette actuelle</dt><dd class="col-3">{{ number_format($bien->valeur_actuelle, 0, ',', ' ') }} FCFA</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if ($lignes->isEmpty())
                <p class="text-muted p-3 mb-0">Aucune ligne d'amortissement générée.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Exercice</th>
                            <th>Montant amortissement</th>
                            <th>Cumul</th>
                            <th>Valeur nette</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lignes as $ligne)
                            <tr>
                                <td>{{ $ligne->annee_exercice }}</td>
                                <td>{{ number_format($ligne->montant_amortissement, 0, ',', ' ') }} FCFA</td>
                                <td>{{ number_format($ligne->cumul_amortissement ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td>{{ number_format($ligne->valeur_nette ?? 0, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
