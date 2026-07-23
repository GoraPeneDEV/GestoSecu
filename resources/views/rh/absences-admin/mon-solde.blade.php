@extends('layouts.contentNavbarLayout')

@section('title', 'Mon solde de congés')

@section('content')
    <a href="{{ route('absences-admin.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Mon solde de congés</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0">{{ $stats['solde_actuel'] }}</h3><small class="text-muted">Solde actuel (j)</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0 text-warning">{{ $stats['en_attente'] }}</h3><small class="text-muted">Jours en attente</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0 text-success">{{ $stats['jours_pris'] }}</h3><small class="text-muted">Jours pris</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0">{{ $stats['total_demandes'] }}</h3><small class="text-muted">Total demandes</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Historique de mes demandes</div>
        <div class="card-body p-0">
            @if ($demandes->isEmpty())
                <p class="text-muted p-3 mb-0">Aucune demande enregistrée.</p>
            @else
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Période</th>
                            <th>Jours</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($demandes as $demande)
                            <tr>
                                <td>{{ $demande->type_conges_libelle }}</td>
                                <td>{{ $demande->date_debut?->format('d/m/Y') }} → {{ $demande->date_fin?->format('d/m/Y') }}</td>
                                <td>{{ $demande->nbr_jour }}</td>
                                <td><span class="badge {{ $demande->statut_badge_class }}">{{ $demande->statut_libelle }}</span></td>
                                <td>
                                    <a href="{{ route('absences-admin.show', $demande) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Détails">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
