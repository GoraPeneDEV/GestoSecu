@extends('layouts.app')

@section('title', 'Plannings de ronde')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Plannings de ronde</h3>
        <a href="{{ route('sie.plannings-ronde.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouveau planning
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total'] }}</h4><small class="text-muted">Plannings</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['quotidiens'] }}</h4><small class="text-muted">Quotidiens</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['hebdomadaires'] }}</h4><small class="text-muted">Hebdomadaires</small>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['mensuels'] }}</h4><small class="text-muted">Mensuels</small>
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
                    <select id="filterFrequence" class="form-select form-select-sm">
                        <option value="">Toutes fréquences</option>
                        <option value="quotidienne">Quotidienne</option>
                        <option value="hebdomadaire">Hebdomadaire</option>
                        <option value="mensuelle">Mensuelle</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="planningsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Site</th>
                            <th>Fréquence</th>
                            <th>Heure de début</th>
                            <th>Durée estimée</th>
                            <th>Points</th>
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
            url: '{{ route('sie.plannings-ronde.data') }}',
            data: function (d) {
                d.site_id = $('#filterSite').val();
                d.frequence = $('#filterFrequence').val();
            }
        },
        columns: [
            { data: 'nom', name: 'nom' },
            { data: 'site.nom_site', name: 'site.nom_site' },
            { data: 'frequence', name: 'frequence' },
            { data: 'heure_debut', name: 'heure_debut' },
            { data: 'duree_estimee', name: 'duree_estimee', render: (d) => d + ' min' },
            { data: 'points_count', name: 'points_count', orderable: false, searchable: false },
            {
                data: null, orderable: false, searchable: false,
                render: (d) => `<a href="/sie/plannings-ronde/${d.id}" class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></a>
                    <a href="/sie/plannings-ronde/${d.id}/edit" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>`
            },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterSite, #filterFrequence').on('change', function () { table.ajax.reload(); });
});
</script>
@endpush
