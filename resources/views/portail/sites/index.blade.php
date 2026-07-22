@extends('portail.layouts.app')

@section('title', 'Mes sites')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Mes sites</h3>
        <a href="{{ route('portail.sites.export-csv') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-download"></i> Exporter en CSV
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['totalSites'] }}</h3>
                    <small class="text-muted">Total sites</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['sitesActifs'] }}</h3>
                    <small class="text-muted">Actifs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['totalAgents'] }}</h3>
                    <small class="text-muted">Agents actifs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['sitesArchives'] }}</h3>
                    <small class="text-muted">Archivés</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="sitesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Zone</th>
                            <th>Date de début</th>
                            <th>Agents</th>
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
    $('#sitesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('portail.sites.getSites') }}',
        columns: [
            { data: 'nom_site', name: 'nom_site' },
            { data: 'type_site', name: 'type_site' },
            { data: 'zone', name: 'zone', orderable: false },
            { data: 'date_debut', name: 'date_debut' },
            { data: 'agents_count', name: 'agents_count', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });
});
</script>
@endpush
