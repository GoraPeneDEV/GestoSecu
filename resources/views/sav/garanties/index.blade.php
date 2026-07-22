@extends('layouts.app')

@section('title', 'Garanties')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Garanties</h3>
        <a href="{{ route('sav.garanties.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvelle garantie
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['actives'] }}</h4><small class="text-muted">Actives</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['expirant_30'] }}</h4><small class="text-muted">Expirant sous 30 jours</small>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center"><div class="card-body">
                <h4 class="mb-0">{{ $stats['total'] }}</h4><small class="text-muted">Total</small>
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-auto">
                    <select id="filterClient" class="form-select form-select-sm">
                        <option value="">Tous les clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->nomClient }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterType" class="form-select form-select-sm">
                        <option value="">Tous les types</option>
                        <option value="main_oeuvre">Main d'œuvre</option>
                        <option value="pieces">Pièces</option>
                        <option value="totale">Totale</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterStatut" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="active">Active</option>
                        <option value="expiree">Expirée</option>
                        <option value="resiliee">Résiliée</option>
                        <option value="en_reclamation">En réclamation</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="garantiesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Fin</th>
                            <th>Jours restants</th>
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
    var table = $('#garantiesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('sav.garanties.index') }}',
            data: function (d) {
                d.client_id = $('#filterClient').val();
                d.type = $('#filterType').val();
                d.statut = $('#filterStatut').val();
            }
        },
        columns: [
            { data: 'client_nom', name: 'client.nomClient', orderable: false },
            { data: 'type_label', name: 'type', orderable: false },
            { data: 'date_fin_fmt', name: 'date_fin' },
            { data: 'jours_restants', name: 'jours_restants', orderable: false, searchable: false },
            { data: 'statut_badge', name: 'statut', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterClient, #filterType, #filterStatut').on('change', function () { table.ajax.reload(); });
});
</script>
@endpush
