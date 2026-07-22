@extends('layouts.app')

@section('title', 'Historique — ' . $bien->code_interne)

@section('content')
    <a href="{{ route('immobilisations.biens.show', $bien->id) }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Historique des affectations — {{ $bien->code_interne }}</h3>

    <div class="card">
        <div class="card-body p-0">
            @if ($affectations->isEmpty())
                <p class="text-muted p-3 mb-0">Aucune affectation enregistrée.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Type</th>
                            <th>Début</th>
                            <th>Fin prévue</th>
                            <th>Fin réelle</th>
                            <th>État retour</th>
                            <th>Créé par</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($affectations as $affectation)
                            <tr>
                                <td>{{ $affectation->employe->prenom ?? '' }} {{ $affectation->employe->nom ?? '-' }}</td>
                                <td>{{ ucfirst($affectation->type_affectation) }}</td>
                                <td>{{ \Carbon\Carbon::parse($affectation->date_affectation)->format('d/m/Y') }}</td>
                                <td>{{ $affectation->date_fin_prevue ? \Carbon\Carbon::parse($affectation->date_fin_prevue)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $affectation->date_fin_reelle ? \Carbon\Carbon::parse($affectation->date_fin_reelle)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $affectation->etat_retour ? ucfirst(str_replace('_', ' ', $affectation->etat_retour)) : '-' }}</td>
                                <td>{{ $affectation->createur->nom_complet ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
