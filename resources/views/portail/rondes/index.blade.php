@extends('portail.layouts.app')

@section('title', 'Rondes')

@section('content')
    <h3 class="mb-4">Rondes de sécurité</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['totalRondes'] }}</h3>
                    <small class="text-muted">Rondes (ce mois)</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0 text-success">{{ $stats['rondesTerminees'] }}</h3>
                    <small class="text-muted">Terminées</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0 text-warning">{{ $stats['rondesEnCours'] }}</h3>
                    <small class="text-muted">En cours</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0 text-danger">{{ $stats['totalAnomalies'] }}</h3>
                    <small class="text-muted">Anomalies</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['sitesAvecRondes'] }}</h3>
                    <small class="text-muted">Sites couverts</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['tauxReussite'] }}%</h3>
                    <small class="text-muted">Taux de réussite</small>
                </div>
            </div>
        </div>
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
                    <select id="filterStatut" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="en_cours">En cours</option>
                        <option value="terminee">Terminée</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="rondesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Ronde</th>
                            <th>Site / Agent</th>
                            <th>Progression</th>
                            <th>Statut</th>
                            <th>Durée</th>
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
    var table = $('#rondesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('portail.rondes.getRondes') }}',
            data: function (d) {
                d.site_id = $('#filterSite').val();
                d.statut = $('#filterStatut').val();
            }
        },
        columns: [
            { data: 'ronde_info', name: 'plannings_ronde.nom', orderable: false },
            { data: 'site_agent', name: 'sites.nom_site', orderable: false },
            { data: 'progression', name: 'progression', orderable: false, searchable: false },
            { data: 'statut', name: 'rondes.statut' },
            { data: 'duree', name: 'duree', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterSite, #filterStatut').on('change', function () {
        table.ajax.reload();
    });
});
</script>
@endpush
