@extends('layouts.contentNavbarLayout')

@section('title', 'Plannings')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Plannings ({{ $planningsActifs }} actifs)</h3>
        <a href="{{ route('plannings.create') }}" class="btn btn-primary">
            <i class="ti ti-plus-lg"></i> Nouveau planning
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-auto">
                    <select id="filterSite" class="form-select form-select-sm">
                        <option value="">Tous les sites</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->nom_site }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <input type="text" id="searchAgent" class="form-control form-control-sm" placeholder="Rechercher un agent...">
                </div>
            </div>
            <div class="table-responsive">
                <table id="planningsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Département</th>
                            <th>Site</th>
                            <th>Début</th>
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
    var table = $('#planningsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('plannings.data') }}',
            data: function (d) {
                d.site = $('#filterSite').val();
                d.search_agent = $('#searchAgent').val();
            }
        },
        columns: [
            { data: 'employe_info', name: 'employe.prenom', orderable: false },
            { data: 'departement_name', name: 'departement_name', orderable: false },
            { data: 'site_info', name: 'site.nom_site', orderable: false },
            { data: 'date_debut', name: 'date_debut' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterSite').on('change', function () { table.ajax.reload(); });
    let searchTimer;
    $('#searchAgent').on('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { table.ajax.reload(); }, 400);
    });

    $('#planningsTable').on('click', '.delete-planning', function () {
        if (!confirm('Supprimer ce planning ?')) return;
        var id = $(this).data('id');
        $.ajax({
            url: '/plannings/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function () { table.ajax.reload(); },
            error: function () { alert('Erreur lors de la suppression'); }
        });
    });
});
</script>
@endpush
