@extends('layouts.app')

@section('title', 'Mes absences')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Mon historique d'absences</h3>
        <div>
            @can('conge-admin-dept-view')
                <a href="{{ route('absences-admin.departement') }}" class="btn btn-outline-secondary">Mon département</a>
            @endcan
            <a href="{{ route('absences-admin.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nouvelle demande
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0">{{ $stats['solde_conges'] }}</h3><small class="text-muted">Solde congés (j)</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0">{{ $stats['total'] }}</h3><small class="text-muted">Total demandes</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0 text-warning">{{ $stats['en_attente'] }}</h3><small class="text-muted">En attente</small>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100"><div class="card-body">
                <h3 class="mb-0 text-success">{{ $stats['approuvees'] }}</h3><small class="text-muted">Approuvées</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="demandesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Période</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#demandesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('absences-admin.data') }}',
        columns: [
            { data: 'type_label', name: 'type_conges' },
            { data: 'periode', name: 'periode', orderable: false },
            { data: 'statut_badge', name: 'statut' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });
});
</script>
@endpush
