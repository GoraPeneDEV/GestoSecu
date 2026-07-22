<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portail Client') - {{ config('app.name', 'GestoSecu') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-light">
    @php($portailUser = Auth::guard('portail')->user())
    @php($portailClient = $portailUser->client ?? null)

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ route('portail.dashboard') }}">
                <i class="bi bi-shield-check"></i> GestoSecu <span class="fw-normal">Portail Client</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portailNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="portailNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portail.dashboard') ? 'active fw-bold' : '' }}" href="{{ route('portail.dashboard') }}">Tableau de bord</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portail.sites.*') ? 'active fw-bold' : '' }}" href="{{ route('portail.sites.index') }}">Mes sites</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portail.parc.*') ? 'active fw-bold' : '' }}" href="{{ route('portail.parc.index') }}">Mon parc</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portail.rondes.*') ? 'active fw-bold' : '' }}" href="{{ route('portail.rondes.index') }}">Rondes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portail.agents.*') ? 'active fw-bold' : '' }}" href="{{ route('portail.agents.index') }}">Agents</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('portail.support') ? 'active fw-bold' : '' }}" href="{{ route('portail.support') }}">Support</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="portailUserMenu" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            {{ $portailClient->nomClient ?? 'Client' }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('portail.profile') }}">Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('portail.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Déconnexion</button>
                                </form>
                            </li>
                        </ul>
                    </li>
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

    <footer class="text-center text-muted py-4 small">
        © {{ date('Y') }} GestoSecu - Portail Client
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    @stack('scripts')
</body>
</html>
