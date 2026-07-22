@extends('layouts.app')

@section('title', 'Interactions clients')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Interactions clients</h3>
        <a href="{{ route('sav.interactions.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvelle interaction
        </a>
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
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <select id="filterStatut" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="a_traiter">À traiter</option>
                        <option value="en_attente">En attente</option>
                        <option value="traite">Traité</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="interactionsTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Sujet</th>
                            <th>Sens</th>
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
    var table = $('#interactionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('sav.interactions.index') }}',
            data: function (d) {
                d.client_id = $('#filterClient').val();
                d.type = $('#filterType').val();
                d.statut = $('#filterStatut').val();
            }
        },
        columns: [
            { data: 'client_nom', name: 'client.nomClient', orderable: false },
            { data: 'type_label', name: 'type', orderable: false },
            { data: 'sujet', name: 'sujet' },
            { data: 'sens_badge', name: 'sens', orderable: false },
            { data: 'statut_badge', name: 'statut', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
    });

    $('#filterClient, #filterType, #filterStatut').on('change', function () { table.ajax.reload(); });
});
</script>
@endpush
