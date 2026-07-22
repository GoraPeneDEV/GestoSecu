<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord') - {{ config('app.name', 'GestoSecu') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">{{ config('app.name', 'GestoSecu') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">RH</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('employes.index') }}">Employés</a></li>
                            <li><a class="dropdown-item" href="{{ route('departements.index') }}">Départements</a></li>
                            <li><a class="dropdown-item" href="{{ route('contrats.index') }}">Contrats</a></li>
                            <li><a class="dropdown-item" href="{{ route('plannings.index') }}">Plannings</a></li>
                            <li><a class="dropdown-item" href="{{ route('horaires.index') }}">Horaires de planning</a></li>
                            <li><a class="dropdown-item" href="{{ route('jours_ferier.index') }}">Jours fériés</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('absences-admin.index') }}">Absences (RH)</a></li>
                            <li><a class="dropdown-item" href="{{ route('conge.soldes.index') }}">Soldes de congés</a></li>
                            <li><a class="dropdown-item" href="{{ route('demandes-explications.index') }}">Demandes d'explication</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Paie</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('paie.dashboard') }}">Tableau de bord</a></li>
                            <li><a class="dropdown-item" href="{{ route('paie.variables.index') }}">Variables de paie</a></li>
                            <li><a class="dropdown-item" href="{{ route('paie.bulletins.index') }}">Bulletins</a></li>
                            <li><a class="dropdown-item" href="{{ route('paie.simulations.index') }}">Simulations</a></li>
                            <li><a class="dropdown-item" href="{{ route('paie.elements-paie.index') }}">Éléments de paie</a></li>
                            <li><a class="dropdown-item" href="{{ route('paie.baremes-fiscaux.index') }}">Barèmes fiscaux</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">SAV</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('sav.dashboard') }}">Tableau de bord</a></li>
                            <li><a class="dropdown-item" href="{{ route('sav.fiches-progres.index') }}">Fiches de progrès</a></li>
                            <li><a class="dropdown-item" href="{{ route('sav.contrats.index') }}">Contrats SAV</a></li>
                            <li><a class="dropdown-item" href="{{ route('sav.garanties.index') }}">Garanties</a></li>
                            <li><a class="dropdown-item" href="{{ route('sav.interactions.index') }}">Interactions clients</a></li>
                            <li><a class="dropdown-item" href="{{ route('sav.parc.index') }}">Parc client</a></li>
                            <li><a class="dropdown-item" href="{{ route('sav.maintenances.index') }}">Maintenances</a></li>
                            <li><a class="dropdown-item" href="{{ route('sav.interventions.index') }}">Interventions</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Articles &amp; Dotations</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('articles.index') }}">Articles</a></li>
                            <li><a class="dropdown-item" href="{{ route('dotations.index') }}">Dotations</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Immobilisations</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('immobilisations.dashboard') }}">Tableau de bord</a></li>
                            <li><a class="dropdown-item" href="{{ route('immobilisations.categories.index') }}">Catégories</a></li>
                            <li><a class="dropdown-item" href="{{ route('immobilisations.sites.index') }}">Sites</a></li>
                            <li><a class="dropdown-item" href="{{ route('immobilisations.biens.index') }}">Biens</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Ronde &amp; Supervision</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('sie.plannings-ronde.index') }}">Plannings de ronde</a></li>
                            <li><a class="dropdown-item" href="{{ route('sie.pointcontroles.index') }}">Points de contrôle</a></li>
                            <li><a class="dropdown-item" href="{{ route('sie.rondes.index') }}">Rondes</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('superviseur.pointcontroles.index') }}">Points de contrôle (superviseur)</a></li>
                            <li><a class="dropdown-item" href="{{ route('supervision.visites.index') }}">Visites de supervision</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->nom_complet }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Déconnexion</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    @stack('scripts')
</body>
</html>
