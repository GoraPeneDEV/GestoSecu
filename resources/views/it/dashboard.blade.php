@extends('layouts.contentNavbarLayout')

@section('title', 'Tableau de bord IT')

@section('content')
    <h3 class="mb-4">Tableau de bord IT</h3>

    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total_comptes'] }}</h4><small class="text-muted">Comptes portail</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-success">{{ $stats['comptes_actifs'] }}</h4><small class="text-muted">Actifs</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0 text-secondary">{{ $stats['comptes_inactifs'] }}</h4><small class="text-muted">Inactifs</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h4 class="mb-0">{{ $stats['clients_avec_acces'] }}</h4><small class="text-muted">Clients avec accès portail</small>
            </div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5>Comptes du portail client</h5>
                    <p class="text-muted">Créer, modifier ou réinitialiser le mot de passe des comptes d'accès au portail client.</p>
                    <a href="{{ route('it.client-users.index') }}" class="btn btn-primary mt-auto align-self-start">
                        <i class="ti ti-users"></i> Gérer les comptes
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Dernières connexions au portail</div>
                <div class="card-body p-0">
                    @if ($dernieresConnexions->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucune connexion enregistrée.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Compte</th><th>Client</th><th>Dernière connexion</th></tr></thead>
                            <tbody>
                                @foreach ($dernieresConnexions as $u)
                                    <tr>
                                        <td>{{ $u->prenom }} {{ $u->nom }}</td>
                                        <td>{{ $u->client->nomClient ?? '-' }}</td>
                                        <td>{{ $u->last_login_at?->format('d/m/Y H:i') }}</td>
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
