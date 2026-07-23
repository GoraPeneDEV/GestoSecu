@extends('layouts.contentNavbarLayout')

@section('title', 'Rapports')

@section('content')
    <h3 class="mb-1">Rapports</h3>
    <p class="text-muted mb-4">Générez un rapport détaillé, filtrable par période, pour chaque module de votre compte.</p>

    <div class="row g-3">
        <div class="col-md-3 col-6">
            <a href="{{ route('portail.rapports.sites') }}" class="text-decoration-none">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="ti ti-building fs-1 text-primary"></i>
                        <h5 class="mt-2 mb-0">Sites</h5>
                        <small class="text-muted">Liste et évolution de vos sites</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('portail.rapports.agents') }}" class="text-decoration-none">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="ti ti-users fs-1 text-primary"></i>
                        <h5 class="mt-2 mb-0">Agents</h5>
                        <small class="text-muted">Couverture agents par site</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('portail.rapports.rondes') }}" class="text-decoration-none">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="ti ti-route fs-1 text-primary"></i>
                        <h5 class="mt-2 mb-0">Rondes</h5>
                        <small class="text-muted">Rondes réalisées et anomalies</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('portail.rapports.parc') }}" class="text-decoration-none">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="ti ti-devices fs-1 text-primary"></i>
                        <h5 class="mt-2 mb-0">Parc / Équipements</h5>
                        <small class="text-muted">État de votre parc d'équipements</small>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
