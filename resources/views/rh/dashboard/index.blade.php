@extends('layouts.contentNavbarLayout')

@section('title', 'Tableau de bord RH')

@section('content')
    <h3 class="mb-4">Tableau de bord RH</h3>

    <div class="row g-3 mb-3">
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total_employes'] }}</h4><small class="text-muted">Employés actifs</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['cdi'] }}</h4><small class="text-muted">CDI</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['cdd'] }}</h4><small class="text-muted">CDD</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['stage'] }}</h4><small class="text-muted">Stage</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-warning">{{ $stats['absences_en_cours'] }}</h4><small class="text-muted">Absences en cours</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-danger">{{ $stats['explications_en_attente'] }}</h4><small class="text-muted">Explications en attente</small>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Employés par département</div>
                <div class="card-body p-0">
                    @if ($employesParDepartement->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucune donnée.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Département</th><th>Employés</th></tr></thead>
                            <tbody>
                                @foreach ($employesParDepartement as $row)
                                    <tr>
                                        <td>{{ $row->departement->nom ?? 'Non défini' }}</td>
                                        <td>{{ $row->total }}</td>
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
                <div class="card-header">Contrats arrivant à échéance (30 jours)</div>
                <div class="card-body p-0">
                    @if ($contratsExpirant->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucun contrat concerné.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Employé</th><th>Type</th><th>Fin prévue</th></tr></thead>
                            <tbody>
                                @foreach ($contratsExpirant as $contrat)
                                    <tr>
                                        <td>
                                            @if ($contrat->employe)
                                                <a href="{{ route('employes.show', $contrat->employe->id) }}">{{ $contrat->employe->prenom }} {{ $contrat->employe->nom }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $contrat->type_contrat }}</td>
                                        <td>{{ \Carbon\Carbon::parse($contrat->date_prevu_fin)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Dernières demandes d'absence</div>
        <div class="card-body p-0">
            @if ($absencesRecentes->isEmpty())
                <p class="text-muted p-3 mb-0">Aucune demande.</p>
            @else
                <table class="table table-sm mb-0">
                    <thead><tr><th>Employé</th><th>Type</th><th>Période</th><th>Statut</th></tr></thead>
                    <tbody>
                        @foreach ($absencesRecentes as $demande)
                            <tr>
                                <td>{{ $demande->employe->prenom ?? '' }} {{ $demande->employe->nom ?? '-' }}</td>
                                <td>{{ $demande->type_conges ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}</td>
                                <td><span class="badge bg-secondary">{{ $demande->statut }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
