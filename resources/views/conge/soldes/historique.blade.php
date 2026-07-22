@extends('layouts.app')

@section('title', 'Historique du solde')

@section('content')
    <a href="{{ route('conge.soldes.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Historique — {{ $employe->prenom }} {{ $employe->nom }} ({{ $employe->solde_conges ?? 0 }} jours)</h3>

    <div class="card">
        <div class="card-body">
            @if ($ajustements->isEmpty())
                <p class="text-muted mb-0">Aucun ajustement enregistré.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Date</th><th>Type</th><th>Montant</th><th>Commentaire</th><th>Par</th></tr></thead>
                        <tbody>
                            @foreach ($ajustements as $ajustement)
                                <tr>
                                    <td>{{ $ajustement->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $ajustement->type === 'ajout' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $ajustement->montant_signe }}
                                        </span>
                                    </td>
                                    <td>{{ $ajustement->montant }}</td>
                                    <td>{{ $ajustement->commentaire }}</td>
                                    <td>{{ $ajustement->utilisateur->nom_complet ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
