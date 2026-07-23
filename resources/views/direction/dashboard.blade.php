@extends('layouts.contentNavbarLayout')

@section('title', 'Tableau de bord Direction')

@section('content')
    <h3 class="mb-1">Tableau de bord Direction</h3>
    <p class="text-muted mb-4">Vue à 360° sur l'ensemble des départements.</p>

    <div class="row g-3 mb-3">
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['effectif_actif'] }}</h4><small class="text-muted">Effectif actif</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['sites_actifs'] }}</h4><small class="text-muted">Sites actifs</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['clients_actifs'] }}</h4><small class="text-muted">Clients actifs</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h5 class="mb-0">{{ number_format($stats['masse_salariale_mois'], 0, ',', ' ') }}</h5><small class="text-muted">Masse salariale (mois)</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h5 class="mb-0">{{ number_format($stats['valeur_immobilisations'], 0, ',', ' ') }}</h5><small class="text-muted">Valeur immobilisations</small>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-3">
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
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['contrats_sav_actifs'] }}</h4><small class="text-muted">Contrats SAV actifs</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-warning">{{ $stats['contrats_sav_expirant'] }}</h4><small class="text-muted">Contrats SAV expirant (30j)</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['fiches_sav_en_cours'] }}</h4><small class="text-muted">Fiches SAV en cours</small>
            </div></div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['comptes_portail_actifs'] }}</h4><small class="text-muted">Comptes portail actifs</small>
            </div></div>
        </div>
    </div>

    @if ($stats['articles_sous_stock'] > 0)
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle"></i> {{ $stats['articles_sous_stock'] }} article(s) sous le seuil de stock minimum.
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Effectif par département</div>
                <div class="card-body p-0">
                    @if ($employesParDepartement->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucune donnée.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Département</th><th>Employés</th></tr></thead>
                            <tbody>
                                @foreach ($employesParDepartement as $dept)
                                    <tr>
                                        <td>{{ $dept->nom }}</td>
                                        <td>{{ $dept->employes_count }}</td>
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
                <div class="card-header">Rondes sur 7 jours</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Jour</th><th>Total</th><th>Complétées</th></tr></thead>
                        <tbody>
                            @foreach ($rondesParJour as $ligne)
                                <tr>
                                    <td>{{ $ligne['jour'] }}</td>
                                    <td>{{ $ligne['total'] }}</td>
                                    <td>{{ $ligne['completes'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">Dernières demandes d'absence</div>
                <div class="card-body p-0">
                    @if ($absencesRecentes->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucune demande.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Employé</th><th>Statut</th></tr></thead>
                            <tbody>
                                @foreach ($absencesRecentes as $demande)
                                    <tr>
                                        <td>{{ $demande->employe->prenom ?? '' }} {{ $demande->employe->nom ?? '-' }}</td>
                                        <td><span class="badge {{ $demande->statut_badge_class }}">{{ $demande->statut_libelle }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">Contrats SAV expirant</div>
                <div class="card-body p-0">
                    @if ($contratsSavExpirantListe->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucun contrat concerné.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Client</th><th>Fin</th></tr></thead>
                            <tbody>
                                @foreach ($contratsSavExpirantListe as $contrat)
                                    <tr>
                                        <td>{{ $contrat->client->nomClient ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($contrat->date_fin)->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">Fiches SAV récentes</div>
                <div class="card-body p-0">
                    @if ($fichesSavRecentes->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucune fiche.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>N°</th><th>Client</th><th>Statut</th></tr></thead>
                            <tbody>
                                @foreach ($fichesSavRecentes as $fiche)
                                    <tr>
                                        <td>{{ $fiche->numero_fiche }}</td>
                                        <td>{{ $fiche->client->nomClient ?? '-' }}</td>
                                        <td><span class="badge bg-secondary">{{ $fiche->statut }}</span></td>
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
