@extends('layouts.contentNavbarLayout')

@section('title', 'Points de contrôle superviseur')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Points de contrôle superviseur</h3>
        <a href="{{ route('superviseur.pointcontroles.create') }}" class="btn btn-primary">
            <i class="ti ti-plus-lg"></i> Nouveau point de contrôle
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $totalPointControles }}</h4><small class="text-muted">Total</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $pointControlesActifs }}</h4><small class="text-muted">Actifs</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $pointControlesInactifs }}</h4><small class="text-muted">Inactifs</small>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <select id="filterSite" class="form-select form-select-sm">
                        <option value="">Tous les sites</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->nom_site }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterActif" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="pointsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Site</th>
                            <th>Emplacement</th>
                            <th>Ordre</th>
                            <th>Actif</th>
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
    var table = $('#pointsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('superviseur.pointcontroles.getPointControles') }}',
            data: function (d) {
                d.site_id = $('#filterSite').val();
                d.actif = $('#filterActif').val();
            }
        },
        columns: [
            { data: 'nom', name: 'nom' },
            { data: 'site.nom_site', name: 'site.nom_site' },
            { data: 'emplacement', name: 'emplacement' },
            { data: 'ordre', name: 'ordre' },
            { data: 'actif', name: 'actif', render: (d) => d ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>' },
            {
                data: null, orderable: false, searchable: false,
                render: (d) => `<a href="/superviseur/pointcontroles/${d.id}" class="btn btn-sm btn-info me-1"><i class="ti ti-eye"></i></a>
                    <a href="/superviseur/pointcontroles/${d.id}/edit" class="btn btn-sm btn-warning"><i class="ti ti-pencil"></i></a>`
            },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterSite, #filterActif').on('change', function () { table.ajax.reload(); });
});
</script>
@endpush
