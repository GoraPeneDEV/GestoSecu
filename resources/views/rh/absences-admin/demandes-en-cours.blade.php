@extends('layouts.contentNavbarLayout')

@section('title', 'Demandes en cours')

@section('content')
    <a href="{{ route('absences-admin.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="ti ti-arrow-left"></i> Retour
    </a>

    <h3 class="mb-4">Demandes en cours de traitement</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0">{{ $stats['total'] }}</h3><small class="text-muted">Total en cours</small>
            </div></div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0 text-warning">{{ $stats['en_attente'] }}</h3><small class="text-muted">En attente (supérieur)</small>
            </div></div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0 text-info">{{ $stats['validees_superieur'] }}</h3><small class="text-muted">Validées par le supérieur (attente RH)</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="mb-3">Retrouvez le détail des demandes en attente de validation dans les listes suivantes :</p>
            <div class="d-flex gap-2 flex-wrap">
                @can('conge-admin-dept-view')
                    <a href="{{ route('absences-admin.departement') }}" class="btn btn-outline-primary">
                        <i class="ti ti-users"></i> Mon département
                    </a>
                @endcan
                @can('conge-admin-suivi-view')
                    <a href="{{ route('absences-admin.suivi-global') }}" class="btn btn-outline-primary">
                        <i class="ti ti-list-details"></i> Suivi global
                    </a>
                @endcan
                <a href="{{ route('absences-admin.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-history"></i> Mon historique
                </a>
            </div>
        </div>
    </div>
@endsection
